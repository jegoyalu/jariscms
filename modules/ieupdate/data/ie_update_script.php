<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        IE update script
    field;

    field: content
    //<script>
    $(document).ready(function() {

        if ($.browser.msie)
        {
            if (parseInt($.browser.version) <= 10)
            {
                $("body").prepend($(
                    '<div id="ie-update-popup">' +
                    '<div class="close"><a title="<?php print t("close") ?>" href="#">X</a></div>' +
                    '<div style="clear: both"></div>' +
                    '<center>' +
                    '<h3 class="message"><?php print t("Your current browser is not supported. Please upgrade to one of the following:") ?></h3>' +
                    '<a href="http://microsoft.com/windows/internet-explorer/" target="_blank"><img style="border: 0" alt="Internet Explorer 11+" src="<?php print Jaris\Uri::url(Jaris\Modules::directory("ieupdate") . "images/internet11.png") ?>" /></a>' +
                    '<a href="http://www.mozilla.com/" target="_blank"><img style="border: 0" alt="Mozilla Firefox" src="<?php print Jaris\Uri::url(Jaris\Modules::directory("ieupdate") . "images/mozilla.png") ?>" /></a>' +
                    '<a href="http://www.google.com/chrome" target="_blank"><img style="border: 0" alt="Google Chrome" src="<?php print Jaris\Uri::url(Jaris\Modules::directory("ieupdate") . "images/chrome.png") ?>" /></a>' +
                    '<a href="http://www.opera.com/browser/" target="_blank"><img style="border: 0" alt="Opera Browser" src="<?php print Jaris\Uri::url(Jaris\Modules::directory("ieupdate") . "images/opera-browser.png") ?>" /></a>' +
                    '<a href="http://www.apple.com/safari/" target="_blank"><img style="border: 0" alt="Safari Browsser" src="<?php print Jaris\Uri::url(Jaris\Modules::directory("ieupdate") . "images/safari-browser.png") ?>" /></a>' +
                    '</center>' +
                    '</div>'
                ).hide().fadeIn());

                $("#ie-update-popup .close a").click(function() {
                    $("#ie-update-popup").fadeOut();
                });

                $("#ie-update-popup").css("left", ($(window).width() / 2) - ($("#ie-update-popup").width() / 2) + "px");

                $("#ie-update-popup").css("top", ($(window).height() / 2) - ($("#ie-update-popup").height() / 2) + "px");
            }
        }
    });
    //</script>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
