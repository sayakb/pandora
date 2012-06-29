/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Startup function
$(document).ready(function() {
    $("#block-link").removeClass("hidden");
    $("#block-deadlines").hide();

    $("#block-link").click(function() {
        $(this).hide();

        $("#block-deadlines")
        .css({
            display: "inline-block",
            height: 0,
            opacity: 0
        })
        .animate({
            opacity: 1,
            height: 47,
        });

        return false;
    });

    if (typeof($().datetimepicker) == "function") {
        $(".datepicker").datetimepicker({
            dateFormat: "M dd yy,",
            timeFormat: "hh:mm tt",
            ampm: true,
            changeMonth: true,
            changeYear: true,
        });
    }
});