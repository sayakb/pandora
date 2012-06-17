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
$skin->init('tpl_home');

// Validate and set-up module
$module_title = $lang->get('home');
$module_data = null;
$module_valid = $module->validate($mode);

if ($module_valid)
{
    $module->load($mode);
}

// Assign skin vars
$skin->assign(array(
    'module_title'          => $module_title,
    'module_data'           => $module_data,
    'module_visibility'     => $module_valid ? 'visible' : 'hidden',
    'home_visibility'       => $module_valid ? 'hidden' : 'visible',
));

// Output the page
$skin->title($module_title . ' &bull; ' . $config->site_name);
$skin->output();

?>