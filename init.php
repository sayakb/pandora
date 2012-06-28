<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Turn off error reporting
error_reporting(E_ALL);

// Include classes
include_once('classes/class_gsod.php');
include_once('classes/class_config.php');
include_once('classes/class_core.php');
include_once('classes/class_db.php');
include_once('classes/class_user.php');
include_once('classes/class_lang.php');
include_once('classes/class_email.php');
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