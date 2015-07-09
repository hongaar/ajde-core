<?php


namespace Ajde\Core;

use Ajde\Object\Singleton;
use Ajde\Http\Curl;
use Ajde\Core\Exception;
use ZipArchive;
use Ajde\FileSystem\Directory;
use Config;
use Ajde\FileSystem\Find;
use Ajde\Db;



class Updater extends Singleton
{
	public $current_version;
	public $available_version;
	public $available_package;
	
	public $repo_tags_url = 'https://api.github.com/repos/nabble/ajde/tags';
	public $changelog_url = 'https://raw.github.com/nabble/ajde/master/CHANGELOG.md';
	
	/**
	 *
	 * @staticvar Ajde_Application $instance
	 * @return Ajde_Application
	 */
	public static function getInstance()
	{
		static $instance;
		return $instance === null ? $instance = new self : $instance;
	}
	
	protected function __construct()
	{
        $tags = json_decode(Curl::get($this->repo_tags_url));

        if (!is_array($tags) || !isset($tags[0])) {
            throw new Exception("Updater failed to initialize");
        }
        $availableversion = $tags[0]->name;
        $zipball = $tags[0]->zipball_url;
		
		$this->current_version = AJDE_VERSION;
		$this->available_version = $availableversion;
		$this->available_package = $zipball;
		
		// allow 5 minutes for each step
		@set_time_limit(5 * 60);
	}
	
	public function getCurrentVersion()
	{
		return $this->current_version;
	}
	
	public function getAvailableVersion()
	{
		return $this->available_version;
	}
	
	public function getAvailablePackageURL()
	{
		return $this->available_package;
	}

	public function isUpdateable()
	{
		return version_compare($this->available_version, $this->current_version) > 0;
	}
	
	public function getChangelog()
	{
		return Curl::get($this->changelog_url);
	}
	
	public function update($step)
	{
		switch($step) {
			case "download":
				return $this->downloadUpdate();
				break;
			case "extract":
				return $this->extractUpdate();
				break;
			case "clean":
				return $this->cleanCurrent();
				break;
			case "copy":
				return $this->copyFiles();
				break;
			case "post":
				return $this->postHook();
				break;
		}
		return 'Unknown step in update process';
	}
	
	private function getPackageFile()
	{
		return TMP_DIR . 'update.zip';
	}
	
	public function downloadUpdate()
	{
		// store in tmp dir
		$url = $this->getAvailablePackageURL();
		$file = $this->getPackageFile();
		if (is_file($file)) {
			unlink($file);
		}
		return Curl::download($url, $file);
	}
	
	public function extractUpdate()
	{
		$file = $this->getPackageFile();
		if (!is_file($file)) {
			return false;
		}
		
		$zip = new ZipArchive;
		$res = $zip->open($file);
		if ($res === true) {
			// delete old directory
			if (is_dir(TMP_DIR . 'update')) {
				Directory::delete(TMP_DIR . 'update');
			}
			
			// get root folder
			$root = $zip->statIndex(0);
			$root = $root['name'];
				
			// extract zip
			$zip->extractTo(TMP_DIR);
			$zip->close();
			
			// rename to update
			rename(TMP_DIR . $root, TMP_DIR . 'update');
			return true;
		}
		return false;
	}
	
	public function cleanCurrent()
	{
		$cleanDirs = array(
				CACHE_DIR
		);
		
		foreach($cleanDirs as $cleanDir) {
			Directory::truncate($cleanDir);
		}
		return true;
	}
	
	public function copyFiles()
	{	
		// make sure we are in the current dir
		chdir(Config::get('local_root'));
		
		$updateDir = TMP_DIR . 'update' . DIRECTORY_SEPARATOR;
		
		// directories to overwrite
		$installDirs = array(
			CORE_DIR,
			DEV_DIR,
			LIB_DIR . 'Ajde' . DIRECTORY_SEPARATOR,
			PUBLIC_DIR . 'css' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
			PUBLIC_DIR . 'js' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
			PUBLIC_DIR . 'media' . DIRECTORY_SEPARATOR . '_core' . DIRECTORY_SEPARATOR,
			PUBLIC_DIR . 'media' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR
		);
		
		// files to overwrite
		$rootFiles = Find::findFilenames($updateDir, '*');
		
		// make sure we have the update dir
		if (!is_dir(TMP_DIR . 'update')) {
			throw new Exception('Update directory not found');
		}
		
		// make sure we have all the neccesary files
		foreach($installDirs as $installDir) {
			if (!is_dir($updateDir . $installDir)) {
				throw new Exception('Directory ' . $installDir . ' in update package not found');
			}
		}
		
		// make sure we have a new index.php before deleting the old one
		if (!is_file($updateDir . 'index.php')) {
			throw new Exception('File index.php in update package not found');
		}
		
		// delete index.php
		$delete_tries = 0;
		do {
			$delete_tries++;
			if ($delete_tries > 20) {
				throw new Exception('Unable to remove index.php, please try again later');
			}
			if ($delete_tries > 0) {
				sleep(1);
			}
			$deleted = @unlink('index.php');
		} while ($deleted === false);
		
		// empty directories and copy new files
		foreach($installDirs as $installDir) {
			if (is_dir($updateDir . $installDir)) {
				
				// truncate first
				Directory::truncate($installDir);
				
				// then copy
				Directory::copy($updateDir . $installDir, $installDir);
				
			} else {
				throw new Exception('This is weird, should we make updating semaphored?');
			}
		}
		
		// copy individual files
		foreach($rootFiles as $rootFile) {
			if (is_file($updateDir . $rootFile)) {
				
				copy($updateDir . $rootFile, $rootFile);
				
			}
		}
		
		// cleaning up TMP DIR
		unlink(TMP_DIR . 'update.zip');
		Directory::delete(TMP_DIR . 'update');		
		
		return true;
	}
	
	public function postHook()
	{
		return $this->updateDatabase();
	}
	
	public function updateDatabase()
	{
		$db = Db::getInstance();
		return $db->update();
	}

}