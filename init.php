<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Turn off error reporting
error_reporting(E_ALL);

// Set default timezone to UTC
date_default_timezone_set('UTC');

// Include classes
include_once('classes/class_gsod.php');
include_once('classes/class_config.php');
include_once('classes/class_core.php');
include_once('classes/class_db.php');
include_once('classes/class_user.php');
include_once('classes/class_lang.php');
include_once('classes/class_email.php');
include_once('classes/class_cache.php');
include_once('classes/class_skin.php');
include_once('classes/class_module.php');
include_once('classes/class_donut.php');

// We need to instantiate the GSoD class first, just in case!
$gsod = new gsod();

// Instantiate general classes
$config = new config();
$core   = new core();
$db     = new db();
$user   = new user();
$lang   = new lang();
$email  = new email();
$cache  = new cache();
$skin   = new skin();
$module = new module();
$donut  = new donut();

// Set up the db connection
$db->connect();

// Assign defaut variables
$skin->assign(array(
    'root_path'         => $core->path(),
    'msg_visibility'    => 'hidden',
));

// Perform cron tasks
include_once('cron.php');

// Verify user authentication
$user->verify();

?>
