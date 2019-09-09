<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the control center page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Control Center") ?>
    field;

    field: content
    <?php
        //Stop unauthorized access
        if(!Jaris\Authentication::isUserLogged())
        {
            Jaris\Authentication::protectedPage();
        }

        Jaris\View::addSystemScript("optional/chili-1.7.pack.js");
        Jaris\View::addSystemScript("optional/jquery.easing.js");
        Jaris\View::addSystemScript("optional/jquery.dimensions.js");
        Jaris\View::addSystemScript("optional/jquery.accordion.js");
    ?>
    <script type="text/javascript">
        jQuery().ready(function() {
            jQuery('div.administration-list').accordion({
                header: 'h2',
                autoheight: false,
                active: false,
                alwaysOpen: false
            });
        });
    </script>
    <?php
        $sections = Jaris\System::generateAdminPageSections();

        Jaris\System::generateAdminPage($sections);
    ?>
    field;

    field: is_system
        1
    field;
row;
