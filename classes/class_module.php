<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class module
{
    // Global vars
    var $pool;

    // Constructor
    function __construct()
    {
        $this->pool = array(
            array('name' => 'login',     'access' => 'u'),
            array('name' => 'logout',    'access' => 'u'),
            array('name' => 'programs',  'access' => 'a'),
            array('name' => 'proposals', 'access' => 'a'),
            array('name' => 'users',     'access' => 'a'),
        );
    }
    
    // Method to load a module
    function load($module_name)
    {
        global $gsod;

        if (file_exists(realpath("modules/mod_{$module_name}.php")))
        {
            // Set globals
            global $gsod, $config, $core, $db, $auth, $lang, $skin, $module_title, $module_data;

            // Include the module
            include("modules/mod_{$module_name}.php");
        }
        else
        {
            $message  = 'Pandora module error<br /><br />';
            $message .= 'Error: Cannot find specified module<br />';
            $message .= 'Make sure the module scripts exist inside the modules/ folder';

            $gsod->trigger($message);
        }
    }

    // Method to validate the current module
    function validate($mode)
    {
        global $core, $auth;

        foreach ($this->pool as $module)
        {
            // Name matched. Access granted if it's a user module.
            // For admin module, user should have admin privileges
            if ($module['name'] == $mode)
            {
                return ($module['access'] == 'u') || ($module['access'] == 'a' && $auth->is_admin);
            }
        }

        // Module was not found in the pool
        return false;
    }  
}

?>
