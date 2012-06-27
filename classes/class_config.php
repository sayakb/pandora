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
    var $webmaster;    
    var $skin_name;
    var $lang_name;
    var $per_page;

    var $ldap_server;
    var $ldap_port;
    var $ldap_base_dn;
    var $ldap_uid;
    var $ldap_filter;
    var $ldap_group;
    var $ldap_admin_group;
    var $ldap_user_dn;
    var $ldap_password;
    var $ldap_fullname;
    var $ldap_mail;
    var $ldap_avatar;

    var $smtp_host;
    var $smtp_port;
    var $smtp_username;
    var $smtp_password;
    var $smtp_from;
    
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
            $this->db_host          = isset($db_host) ? $db_host : '';
            $this->db_port          = isset($db_port) ? $db_port : '';
            $this->db_name          = isset($db_name) ? $db_name : '';
            $this->db_username      = isset($db_username) ? $db_username : '';
            $this->db_password      = isset($db_password) ? $db_password : '';
            $this->db_prefix        = isset($db_prefix) ? $db_prefix : '';
            
            $this->site_name        = isset($site_name) ? $site_name : 'Pandora';
            $this->site_copyright   = isset($site_copyright) ? $site_copyright : '&copy; 2012 KDE';
            $this->webmaster        = isset($webmaster) ? $webmaster : '';
            $this->skin_name        = isset($skin_name) ? $skin_name : 'Neverland';
            $this->lang_name        = isset($lang_name) ? $lang_name : 'en-gb';
            $this->per_page         = isset($per_page) ? $per_page : 10;
            
            $this->ldap_server      = isset($ldap_server) ? $ldap_server : '';
            $this->ldap_port        = isset($ldap_port) ? $ldap_port : '';
            $this->ldap_base_dn     = isset($ldap_base_dn) ? $ldap_base_dn : '';
            $this->ldap_uid         = isset($ldap_uid) ? $ldap_uid : '';
            $this->ldap_filter      = isset($ldap_filter) ? $ldap_filter : '';
            $this->ldap_group       = isset($ldap_group) ? $ldap_group : '';
            $this->ldap_admin_group = isset($ldap_admin_group) ? $ldap_admin_group : '';
            $this->ldap_user_dn     = isset($ldap_user_dn) ? $ldap_user_dn : '';
            $this->ldap_password    = isset($ldap_password) ? $ldap_password : '';
            $this->ldap_fullname    = isset($ldap_fullname) ? $ldap_fullname : '';
            $this->ldap_mail        = isset($ldap_mail) ? $ldap_mail : '';
            $this->ldap_avatar      = isset($ldap_avatar) ? $ldap_avatar : '';

            $this->smtp_host        = isset($smtp_host) ? $smtp_host : 'localhost';
            $this->smtp_port        = isset($smtp_port) ? $smtp_port : 25;
            $this->smtp_username    = isset($smtp_username) ? $smtp_username : '';
            $this->smtp_password    = isset($smtp_password) ? $smtp_password : '';
            $this->smtp_from        = isset($smtp_from) ? $smtp_from : 'webmaster@yoursite.com';
        }
    }
}

?>
