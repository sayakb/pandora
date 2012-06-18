<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Log out the user
$auth->logout();

// Redirect to homepage
$core->redirect($core->path());

?>