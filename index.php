<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Invoke required files
include_once('init.php');

// Collect some data
$mode = $core->variable('q', 'home');

// Initialize the skin
$skin->init('tpl_master');

// Set the default header
$skin->set_header(array(
    'Content-Type'  => 'text/html; charset=utf-8'
));

// Validate and set-up module
$module->validate($mode);
$module->load($mode);

// Assign skin vars
$skin->assign(array(
    'module_title'          => $module_title,
    'module_data'           => $module_data,
));

// Output the page
$skin->title($module_title . ' &bull; ' . $config->site_name);
$skin->output();

?>