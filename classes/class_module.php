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
            array('name' => 'timeline',          'access' => 'g'),
            array('name' => 'user_avatar',       'access' => 'u'),
            array('name' => 'user_profile',      'access' => 'u'),
            array('name' => 'view_programs',     'access' => 'g'),
            array('name' => 'view_projects',     'access' => 'u'),
            array('name' => 'program_home',      'access' => 'g'),
            array('name' => 'approve_mentors',   'access' => 'a'),
            array('name' => 'view_participants', 'access' => 'a'),
            array('name' => 'edit_templates',    'access' => 'a'),
            array('name' => 'user_ban',          'access' => 'a'),
            array('name' => 'manage_programs',   'access' => 'a'),
            array('name' => 'notifications',     'access' => 'a'),
        );
    }

    // Method to load a module
    function load($module_name)
    {
        global $gsod;

        if (file_exists(realpath("modules/mod_{$module_name}.php")))
        {
            // Set globals
            global $gsod, $config, $core, $db, $user, $lang, $skin, $email,
                   $cache, $donut, $module_title, $module_data;

            // Include the module
            include("modules/mod_{$module_name}.php");
        }
        else
        {
            $title    = 'Module handler error';
            $message  = 'Error: Cannot find specified module<br />';
            $message .= 'Make sure the module scripts exist inside the modules/ folder';

            $gsod->trigger($title, $message);
        }
    }

    // Method to validate the current module
    function validate($mode)
    {
        global $core, $user;

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
                if ($module['access'] == 'u' || $module['access'] == 'a')
                {
                    if ($user->is_logged_in)
                    {
                        $is_valid = $module['access'] == 'u' || ($module['access'] == 'a' && $user->is_admin);
                    }
                    else
                    {
                        $redir_url = urlencode($core->request_uri());
                        $core->redirect("?q=login&r={$redir_url}");
                    }
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
