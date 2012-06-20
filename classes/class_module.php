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
            array('name' => 'login',             'access' => 'g'),
            array('name' => 'logout',            'access' => 'u'),
            array('name' => 'home',              'access' => 'g'),
            array('name' => 'view_programs',     'access' => 'u'),
            array('name' => 'view_projects',     'access' => 'u'),
            array('name' => 'program_home',      'access' => 'u'),
            array('name' => 'manage_programs',   'access' => 'a'),
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

        $is_valid = false;

        foreach ($this->pool as $module)
        {
            // Name matched. Access granted if it's a user module.
            // For admin module, user should have admin privileges
            if ($module['name'] == $mode)
            {
                // Guest module is always valid
                if ($module['access'] == 'g')
                {
                    $is_valid = true;
                }

                // User module is valid for authenticated users only
                if ($module['access'] == 'u')
                {
                    if ($auth->is_logged_in)
                    {
                        $is_valid = true;
                    }
                    else
                    {
                        $redir_url = urlencode($core->request_uri());
                        $core->redirect("?q=login&r={$redir_url}");
                    }
                }

                // Admins module is valid for administrators
                if ($module['access'] == 'a' && $auth->is_admin);
                {
                    $is_valid = true;
                }
            }
        }

        // Redirect to homepage if invalid module
        if (!$is_valid)
        {
            $core->redirect($core->path());
        }
    }
}

?>
