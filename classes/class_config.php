<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class config
{
    // Declare config variables
    var $db_host;
    var $db_port;
    var $db_name;
    var $db_username;
    var $db_password;
    var $db_prefix;

    var $site_name;
    var $site_copyright;
    var $skin_name;
    var $lang_name;

    var $ldap_server;
    var $ldap_port;
    var $ldap_base_dn;
    var $ldap_uid;
    var $ldap_filter;
    var $ldap_user_dn;
    var $ldap_password;
    
    // Constructor
    function __construct()
    {
        global $core;
        
        // Set the load flag
        $load_data = true;
        
        if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        {
            if (file_exists(realpath('../config.php')))
            {
                include('../config.php');
            }
            else
            {
                $load_data = false;
            }
        }
        else
        {
            if (file_exists(realpath('config.php')))
            {
                include('config.php');
            }
            else
            {
                $load_data = false;
            }
        }
        
        // Set the data
        if ($load_data)
        {
            $this->db_host         = isset($db_host) ? $db_host : '';
            $this->db_port         = isset($db_port) ? $db_port : '';
            $this->db_name         = isset($db_name) ? $db_name : '';
            $this->db_username     = isset($db_username) ? $db_username : '';
            $this->db_password     = isset($db_password) ? $db_password : '';
            $this->db_prefix       = isset($db_prefix) ? $db_prefix : '';
            
            $this->site_name       = isset($site_name) ? $site_name : 'Pandora';
            $this->site_copyright  = isset($site_copyright) ? $site_copyright : '&copy; 2012 KDE';
            $this->skin_name       = isset($skin_name) ? $skin_name : 'Neverland';
            $this->lang_name       = isset($lang_name) ? $lang_name : 'en-gb';
            
            $this->ldap_server     = isset($ldap_server) ? $ldap_server : '';
            $this->ldap_port       = isset($ldap_port) ? $ldap_port : '';
            $this->ldap_base_dn    = isset($ldap_base_dn) ? $ldap_base_dn : '';
            $this->ldap_uid        = isset($ldap_uid) ? $ldap_uid : '';
            $this->ldap_filter     = isset($ldap_filter) ? $ldap_filter : '';
            $this->ldap_user_dn    = isset($ldap_user_dn) ? $ldap_user_dn : '';
            $this->ldap_password   = isset($ldap_password) ? $ldap_password : '';
        }
    }
}

?>
