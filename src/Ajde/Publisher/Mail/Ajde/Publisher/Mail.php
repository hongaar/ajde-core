<?php


namespace Ajde\Publisher;

use Ajde\Publisher;
use Ajde\Mailer;
use Config;



class Mail extends Publisher
{
	private $_recipients;
	
	public function setRecipients($addresses) {
		$this->_recipients = $addresses;
	}
	
	public function publish()
	{
		$mailer = new Mailer();
		
		$mailer->From = Config::get('email');
		$mailer->FromName = Config::get('sitename');
		$mailer->Subject = $this->getTitle();
		$mailer->Body = $this->getMessage() . PHP_EOL . PHP_EOL . $this->getUrl();	
		$mailer->AltBody = strip_tags($mailer->Body);
		$mailer->isHTML(true);
		
		$count = 0;
		foreach($this->_recipients as $to) {
			$mailer->clearAllRecipients();
			$mailer->addAddress($to);
			if ($mailer->send()) {
				$count++;
			}
		}
		
		return "javascript:alert('Message sent as e-mail to " . $count . " recipients');";
	}
	
}