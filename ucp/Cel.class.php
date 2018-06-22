<?php
/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2014 Schmooze Com, INC
 */
namespace UCP\Modules;
use \UCP\Modules as Modules;

class Cel extends Modules{
	protected $module = 'Cel';
	private $limit = 15;
	private $break = 5;

	public function __construct($Modules) {
		$this->Modules = $Modules;
		$this->cel = $this->UCP->FreePBX->Cel;
		$this->user = $this->UCP->User->getUser();

		if($this->UCP->Session->isMobile || $this->UCP->Session->isTablet) {
			$this->limit = 7;
		}
	}

	/**
	* Setup Menu Items for display in UCP
	*/
	public function getMenuItems() {
		if(!$this->UCP->getCombinedSettingByID($this->user['id'],'Cel','enable')) {
			return array();
		}
		$extensions = $this->UCP->getCombinedSettingByID($this->user['id'],'Cel','assigned');
		$menu = array();
		if(!empty($extensions)) {
			$menu = array(
				"rawname" => "cel",
				"name" => _("Call Event Logs"),
				"badge" => false
			);
			foreach($extensions as $e) {
				$data = $this->UCP->FreePBX->Core->getDevice($e);
				if(empty($data) || empty($data['description'])) {
					$data = $this->UCP->FreePBX->Core->getUser($e);
					$name = $data['name'];
				} else {
					$name = $data['description'];
				}
				$menu["menu"][] = array(
					"rawname" => $e,
					"name" => $e . (!empty($name) ? " - " . $name : ""),
					"badge" => false
				);
			}
		}
		return !empty($menu["menu"]) ? $menu : array();
	}

	function getDisplay() {
		$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$ext = !empty($_REQUEST['sub']) ? $_REQUEST['sub'] : '';
		if(!$this->_checkExtension($ext)) {
			return _('Not Authorized');
		}

		$html = '';

		$displayvars = array(
			'ext' => $ext,
		);
		$displayvars['showPlayback'] = $this->_checkPlayback($ext);
		$displayvars['showdownload'] = $this->_checkDownload($ext);
		$displayvars['script'] = "var showDownload = ".json_encode($this->_checkDownload($ext)).";var showPlayback = ".json_encode($this->_checkPlayback($ext)).";var supportedHTML5 = '".implode(",",$this->UCP->FreePBX->Media->getSupportedHTML5Formats())."';";
		$html .= $this->load_view(__DIR__.'/views/table.php',$displayvars);

		return $html;
	}

	/**
	* Determine what commands are allowed
	*
	* Used by Ajax Class to determine what commands are allowed by this class
	*
	* @param string $command The command something is trying to perform
	* @param string $settings The Settings being passed through $_POST or $_PUT
	* @return bool True if pass
	*/
	function ajaxRequest($command, $settings) {
		switch($command) {
			case 'gethtml5':
			case 'playback':
			case "grid":
			case 'download':
				return true;
			break;
			default:
				return false;
			break;
		}
	}

	/**
	* The Handler for all ajax events releated to this class
	*
	* Used by Ajax Class to process commands
	*
	* @return mixed Output if success, otherwise false will generate a 500 error serverside
	*/
	function ajaxHandler() {
		$return = array("status" => false, "message" => "");
		switch($_REQUEST['command']) {
			case 'gethtml5':
				global $amp_conf;
				include_once(dirname(__DIR__)."/crypt.php");
				$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'CorrectHorseBatteryStaple';
				$crypt = new \Crypt();
				$file = $crypt->decrypt($_REQUEST['file'],$REC_CRYPT_PASSWORD);
				if(!$this->cel->validateMonitorPath($file)) {
					return array("status" => false, "message" => _("File does not exist"));
				}
				if(!file_exists($file)) {
					return array("status" => false, "message" => _("File does not exist"));
				}
				$media = $this->UCP->FreePBX->Media();
				$media->load($file);
				$files = $media->generateHTML5();
				$final = array();
				foreach($files as $format => $name) {
					$final[$format] = "index.php?quietmode=1&module=cel&command=playback&file=".$name;
				}
				return array("status" => true, "files" => $final);
			break;
			case "grid":
				$limit = $_REQUEST['limit'];
				$ext = $_REQUEST['extension'];
				$order = $_REQUEST['order'];
				$orderby = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : "timestamp";
				$callsarray = $this->cel->cel_getreport($_REQUEST,$ext);
				$calls = $callsarray['rows'];
				$count = $callsarray['total'];
				return array(
					"total" => $count,
					"rows" => $calls
				);
			break;
			default:
				return false;
			break;
		}
		return $return;
	}

	/**
	* The Handler for quiet events
	*
	* Used by Ajax Class to process commands in which custom processing is needed
	*
	* @return mixed Output if success, otherwise false will generate a 500 error serverside
	*/
	function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
			case "download":
				global $amp_conf;

				include_once(dirname(__DIR__)."/crypt.php");
				$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'CorrectHorseBatteryStaple';

				$crypt = new \Crypt();
				$file = $crypt->decrypt($_REQUEST['id'],$REC_CRYPT_PASSWORD);
				$this->downloadFile($file,$_REQUEST['ext']);
				return true;
			break;
			case "playback":
				$media = $this->UCP->FreePBX->Media();
				$media->getHTML5File($_REQUEST['file']);
				return true;
			break;
			default:
				return false;
			break;
		}
		return false;
	}

	/**
	 * Download a file to listen to on your desktop
	 * @param  string $msgid The message id
	 * @param  int $ext   Extension wanting to listen to
	 */
	private function downloadFile($file,$ext) {
		if(!$this->_checkExtension($ext)) {
			header("HTTP/1.0 403 Forbidden");
			echo _("Forbidden");
			exit;
		}
		if(!file_exists($file)) {
			header("HTTP/1.0 404 Not Found");
			echo _("Not Found");
			exit;
		}
		//dont allow people do download random files on the system
		if(!$this->cel->validateMonitorPath($file)) {
			header("HTTP/1.0 403 Forbidden");
			echo _("Forbidden");
			exit;
		}
		$media = $this->UCP->FreePBX->Media;
		$mimetype = $media->getMIMEtype($file);
		header("Content-length: " . filesize($file));
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header('Content-Disposition: attachment;filename="' . basename($file).'"');
		header('Content-type: ' . $mimetype);
		readfile($file);
	}

	private function _checkExtension($extension) {
		if(!$this->UCP->getCombinedSettingByID($this->user['id'],'Cel','enable')) {
			return false;
		}
		$extensions = $this->UCP->getCombinedSettingByID($this->user['id'],'Cel','assigned');
		return in_array($extension,$extensions);
	}

	private function _checkDownload($extension) {
		if($this->_checkExtension($extension)) {
			$user = $this->UCP->User->getUser();
			$dl = $this->UCP->getCombinedSettingByID($user['id'],'Cel','download');
			return is_null($dl) ? true : $dl;
		}
		return false;
	}

	private function _checkPlayback($extension) {
		if($this->_checkExtension($extension)) {
			$user = $this->UCP->User->getUser();
			$pb = $this->UCP->getCombinedSettingByID($user['id'],'Cel','playback');
			return is_null($pb) ? true : $pb;
		}
		return false;
	}
}
