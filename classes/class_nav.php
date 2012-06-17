<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class nav
{
    // Class level variables
    var $rewrite_on;

    // Constructor
    function __construct()
    {
        $this->rewrite_on = $this->check_rewrite();
    }
    
    // Check if mod_rewrite is enabled or not
    function check_rewrite()
    {
        if (function_exists('apache_get_modules'))
        {
            $modules = apache_get_modules();
            return in_array('mod_rewrite', $modules);
        }
        else
        {
            return getenv('HTTP_MOD_REWRITE') == 'On';
        }
    }

    // Gets a root navigation path
    function get($nav_key, $page = 1)
    {
        try
        {
            global $core;
            
            // Set URL bases
            $base = $core->path();

            // URLs when rewrite is enabled
            $rewrite_ary = array(
                'nav_home'      => $base,
                'nav_list'      => $base,
                'nav_login'     => "{$base}user/login/",
                'nav_logout'    => "{$base}user/logout/",
            );

            // URLs when rewrite is disabled
            $general_ary = array(
                'nav_home'      => $base,
                'nav_list'      => $base,
                'nav_login'     => "{$base}index.php?q=login",
                'nav_logout'    => "{$base}index.php?q=logout",
            );

            // Generate the navigation URL
            if ($this->rewrite_on)
            {
                $url = $rewrite_ary[$nav_key];
            }
            else
            {
                $url = $general_ary[$nav_key];
            }

            return $url;
        }
        catch (Exception $e)
        {
            return null;
        }
    }
}

?>