/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

var isIe = (navigator.appName.indexOf("Microsoft") >= 0);

// Startup function
$(function() {
    $(".datepicker").datetimepicker({
        dateFormat: 'M dd yy,',
        timeFormat: 'hh:mm tt',
        ampm: true,
        changeMonth: true,
        changeYear: true,
    });
});