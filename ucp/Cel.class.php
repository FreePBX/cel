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

	public function __construct($Modules) {
		$this->Modules = $Modules;
		$this->cel = $this->UCP->FreePBX->Cel;
		$this->user = $this->UCP->User->getUser();
	}

	/**
	* Setup Menu Items for display in UCP
	*/
	public function getMenuItems() {
		$menu = array(
			"rawname" => "cel",
			"name" => _("Call Event Logs")
		);
		return $menu;
	}

	function getDisplay() {
		$html = '';
		$displayvars = array();

		$user = $this->UCP->User->getUser();

		$html .= $this->load_view(__DIR__.'/views/search.php',$displayvars);

		return $html;
	}
}
