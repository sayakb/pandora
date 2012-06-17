<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class module
{
    // Method to load a module
    function load($module_name)
    {
        global $gsod;

        if (file_exists(realpath("modules/mod_{$module_name}.php")))
        {
            // Set globals
            global $gsod, $config, $core, $db, $auth, $lang, $skin, $nav,
                   $module_title, $module_data;

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
        global $core;

        // Available modes
        $modes_ary = array('login', 'logout');

        // Return true if module is in valid array
        return in_array($mode, $modes_ary);
    }    
}

?>
