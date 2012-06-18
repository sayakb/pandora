/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

var isIe = (navigator.appName.indexOf("Microsoft") >= 0);

// Startup function
$(function() {
    $(".datepicker").datepicker({
        dateFormat: 'M dd, yy',
        changeMonth: true,
        changeYear: true,
    });
});