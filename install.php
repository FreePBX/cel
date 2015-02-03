<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Schmooze Com Inc.
//
//
global $db;
global $amp_conf;

// Retrieve database and table name if defined, otherwise use FreePBX default
$db_name = !empty($amp_conf['CDRDBNAME'])?$amp_conf['CDRDBNAME']:"asteriskcdrdb";

// if CDRDBHOST and CDRDBTYPE are not empty then we assume an external connection and don't use the default connection
//
if (!empty($amp_conf["CDRDBHOST"]) && !empty($amp_conf["CDRDBTYPE"])) {
	$db_hash = array('mysql' => 'mysql', 'postgres' => 'pgsql');
	$db_type = $db_hash[$amp_conf["CDRDBTYPE"]];
	$db_host = $amp_conf["CDRDBHOST"];
	$db_port = empty($amp_conf["CDRDBPORT"]) ? '' :  ':' . $amp_conf["CDRDBPORT"];
	$db_user = empty($amp_conf["CDRDBUSER"]) ? $amp_conf["AMPDBUSER"] : $amp_conf["CDRDBUSER"];
	$db_pass = empty($amp_conf["CDRDBPASS"]) ? $amp_conf["AMPDBPASS"] : $amp_conf["CDRDBPASS"];
	$datasource = $db_type . '://' . $db_user . ':' . $db_pass . '@' . $db_host . $db_port . '/' . $db_name;
	$dbcdr = DB::connect($datasource); // attempt connection
	if(DB::isError($dbcdr)) {
		die_freepbx($dbcdr->getDebugInfo());
	}
} else {
	$dbcdr = $db;
}

$db_cel_name = !empty($amp_conf['CELDBNAME'])?$amp_conf['CELDBNAME']:$db_name;
$db_cel_table_name = !empty($amp_conf['CELDBTABLENAME'])?$amp_conf['CELDBTABLENAME']:"cel";

outn(_("Creating $db_cel_table_name if needed.."));
$sql = "
CREATE TABLE IF NOT EXISTS `" . $db_cel_name . "`.`" . $db_cel_table_name . "` (
  `id` int(11) NOT NULL auto_increment,
  `eventtype` varchar(30) NOT NULL,
  `eventtime` datetime NOT NULL,
  `cid_name` varchar(80) NOT NULL,
  `cid_num` varchar(80) NOT NULL,
  `cid_ani` varchar(80) NOT NULL,
  `cid_rdnis` varchar(80) NOT NULL,
  `cid_dnid` varchar(80) NOT NULL,
  `exten` varchar(80) NOT NULL,
  `context` varchar(80) NOT NULL,
  `channame` varchar(80) NOT NULL,
  `src` varchar(80) NOT NULL,
  `dst` varchar(80) NOT NULL,
  `channel` varchar(80) NOT NULL,
  `dstchannel` varchar(80) NOT NULL,
  `appname` varchar(80) NOT NULL,
  `appdata` varchar(80) NOT NULL,
  `amaflags` int(11) NOT NULL,
  `accountcode` varchar(20) NOT NULL,
  `uniqueid` varchar(32) NOT NULL,
  `linkedid` varchar(32) NOT NULL,
  `peer` varchar(80) NOT NULL,
  `userdeftype` varchar(255) NOT NULL,
  `extra` varchar(512) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uniqueid_index` (`uniqueid`),
  KEY `linkedid_index` (`linkedid`)
)
";
$check = $dbcdr->query($sql);
if(DB::IsError($check)) {
	die_freepbx("Can not create $db_cel_table_name table");
} else {
	out(_("OK"));
}

outn(_("checking for extra field.."));
$sql = "SELECT `extra` FROM `" . $db_cel_name . "`.`" . $db_cel_table_name . "`";
$check = $dbcdr->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// rename field
	$sql = "ALTER TABLE `" . $db_cel_name . "`.`" . $db_cel_table_name . "` CHANGE `eventextra` `extra` varchar(512)";
	$result = $dbcdr->query($sql);
	if(DB::IsError($result)) {
		out(_("ERROR failed to update extra field"));
	} else {
		out(_("OK"));
	}
} else {
	out(_("already exists"));
}

outn(_("checking for userfield field.."));
$sql = "SELECT `userfield` FROM `" . $db_cel_name . "`.`" . $db_cel_table_name . "`";
$check = $dbcdr->getRow($sql, DB_FETCHMODE_ASSOC);
if(!DB::IsError($check)) {
	// delete field
	$sql = "ALTER TABLE `" . $db_cel_name . "`.`" . $db_cel_table_name . "` DROP COLUMN `userfield`";
	$result = $dbcdr->query($sql);
	if(DB::IsError($result)) {
		out(_("ERROR failed to update userfield field"));
	} else {
		out(_("OK"));
	}
} else {
	out(_("already deleted"));
}

$set['value'] = true;
$set['defaultval'] = true;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 3;
$set['module'] = 'cel';
$set['category'] = 'CEL Report Module';
$set['emptyok'] = 0;
$set['sortorder'] = 10;
$set['name'] = 'Enable CEL Reporting';
$set['description'] = 'Setting this true will enable the CEL module to create call reports from CEL data. Although the CEL module will assure there is a CEL table available, the reporting functionality in Asterisk and associated ODBC database and CEL configuration must be done outside of FreePBX either by the user or at the Distro level.';
$set['type'] = CONF_TYPE_BOOL;

$freepbx_conf =& freepbx_conf::create();
if (!$freepbx_conf->conf_setting_exists('CEL_ENABLED')) {
	$freepbx_conf->define_conf_setting('CEL_ENABLED',$set,true);
} else if ($freepbx_conf->get_conf_default_setting('CEL_ENABLED') == false) {
	/* Setting exists but was created by deprecated CDR module.  Take ownership of it. */
	$freepbx_conf->remove_conf_setting('CEL_ENABLED');
	$freepbx_conf->define_conf_setting('CEL_ENABLED',$set,true);
}
