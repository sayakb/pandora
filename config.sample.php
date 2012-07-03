<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

///NOTE: Unless mentioned explicitly, all settings are mandatory

// DB Hostname
$db_host = "";

// DB port (optional)
$db_port = "";

// DB name
$db_name = "";

// DB username (should have rw access)
$db_username = "";

// Password for DB user
$db_password = "";

// Table prefix
$db_prefix = "";

// Site name
$site_name = "Pandora";

// Site copyright notice
$site_copyright = "&copy; KDE Webteam";

// Webmaster's email address
$webmaster = "webmaster@kde.org";

// Name of current skin
$skin_name = "Neverland";

// Currently active language
$lang_name = "en-gb";

// No. of items to display per page for lists
$per_page = 20;

// LDAP server address
$ldap_server = "";

// LDAP server port
$ldap_port = "";

// Base DN to be used when searching a user
$ldap_base_dn = "";

// Attribute that is used to search the username
$ldap_uid = "";

// Attribute that is used to search user's groups
$ldap_group = "";

// Name of the admin group
$ldap_admin_group = "";

// DN for making initial connection (leave blank for anonymous binding)
$ldap_user_dn = "";

// Password to be used against the userDN above (leave blank for anonymous binding)
$ldap_password = "";

// Full name attribute for the user
$ldap_fullname = "";

// Email ID attribute for the user
$ldap_mail = "";

// Avatar attribute for the user
$ldap_avatar = "";

// SMTP host for sending mail
$smtp_host = "localhost";

// Port used on the SMTP server for sending mail
$smtp_port = 25;

// SMTP server username (optional)
$smtp_username = "";

// SMTP server password (leave blank if no smtp_username spacified)
$smtp_password = "";

// From address for sending emails
$smtp_from = "webmaster@yoursite.com";

?>