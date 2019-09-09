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
        <?php print t("File browser") ?>
    field;

    field: content
    <?php
        $groups = Jaris\Settings::get("groups", "whizzywig") ?
            unserialize(Jaris\Settings::get("groups", "whizzywig")) : false
        ;

        //Check if current user is on one of the groups that can use the editor
        if($groups)
        {
            $user_is_in_group = false;
            foreach($groups as $machine_name => $value)
            {
                if(
                    Jaris\Authentication::currentUserGroup() == $machine_name
                    &&
                    $value
                )
                {
                    $user_is_in_group = true;
                    break;
                }
            }

            if(!Jaris\Authentication::isAdminLogged() && !$user_is_in_group)
            {
                exit;
            }
        }

        $rtnfield = "lf_url";

        if($_REQUEST['element_id'])
        {
            $rtnfield = "lf_url" . $_REQUEST['element_id'];
        }

        $module_url = Jaris\Uri::url(
            Jaris\Modules::directory("whizzywig") . "whizzywig"
        );

        $uri = $_REQUEST["uri"];
    ?>

    <html>

    <head>
    <title><?php print t("File browser") ?></title>
    </head>

    <body>

    <div class="content">
    <script type="text/javascript">
        function WantThis(url)
        {
            window.opener.document
                .getElementById('<?php echo $rtnfield; ?>').value = url
            ;

            window.close();
        }
    </script>

    <div id="files" >
        <?php print t("Click a name below to select.") ?><br>

        <?php
        $files = Jaris\Pages\Files::getList($uri);
        $flist = "";

        if($files)
        {
            foreach($files as $file)
            {
                $url = Jaris\Uri::url("file/$uri/{$file['name']}");

                $flist .= "<div style='float:left;width:20em'>"
                    . "<a href='#' onclick='WantThis(\"$url\")'>"
                    . $file['name']
                    . "</a>"
                    . "</div>"
                ;
            }
            echo $flist;
        }
        else
        {
            print "<h2>" . t("No files available.") . "</h2>";
        }
        ?>
    </div>
    </div>

    </body>
    </html>
    field;

    field: rendering_mode
        plain_html
    field;

    field: is_system
        1
    field;
row;
