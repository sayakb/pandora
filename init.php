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
include_once('classes/class_lang.php');
include_once('classes/class_skin.php');
include_once('classes/class_nav.php');

// We need to instantiate the GSoD class first, just in case!
$gsod = new gsod();

// Instantiate general classes
$config = new config();
$core = new core();
$db = new db();
$lang = new lang();
$skin = new skin();
$nav = new nav();

// Before we do anything, let's add a trailing slash
$url = $core->request_uri();

if (strrpos($url, '/') != (strlen($url) - 1) && $nav->rewrite_on &&
    strpos($url, '.php') === false)
{
    $core->redirect($url . '/');
}
else
{
    unset($url);
}

// Set up the db connection
$db->connect();

// Assign defaut variables
$skin->assign(array(
    'root_path'         => $core->path(),
    'msg_visibility'    => 'hidden',
));

// Perform cron tasks
include_once('cron.php');

?>