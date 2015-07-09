<?php

namespace Ajde\Config;

use Ajde\FileSystem\Find;


class Repository
{
    public function __construct()
    {
        $this->readConfigDir();
        $this->defaults();
    }

    public function readConfigDir()
    {
        foreach(Find::findFiles(CONFIG_DIR, '*.json') as $configFile)
        {
            d($configFile);
        }
    }

    public function defaults()
    {
        // URI fragments
        $this->site_protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
        $this->site_domain = $_SERVER['SERVER_NAME'];
        $this->site_path = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        // Assembled URI
        $this->site_root = $this->site_protocol . $this->site_domain . $this->site_path;

        // Assembled URI with language identifier
        $this->lang_root = $this->site_root;

        // Set default timezone now
        date_default_timezone_set($this->timezone);
    }
}