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
        <?php print t("Image browser") ?>
    field;

    field: content
    <?php
        $groups = Jaris\Settings::get("groups", "ckeditor") ?
            unserialize(Jaris\Settings::get("groups", "ckeditor")) : false
        ;

        //Check if current user is on one of the groups that can use the editor
        if($groups)
        {
            $user_is_in_group = false;
            foreach($groups as $machine_name => $value)
            {
                if(Jaris\Authentication::currentUserGroup() == $machine_name && $value)
                {
                    $user_is_in_group = true;
                    break;
                }
            }

            if(!Jaris\Authentication::isAdminLogged() && !$user_is_in_group)
            {
                print "<h2>Access Denied</h2>";
                exit;
            }
        }

        $uri = $_REQUEST["uri"];
    ?>

    <html>

    <head>
    <title><?php print t("Image browser") ?></title>
    </head>

    <body>

    <div class="content">

    <style type="text/css">
        #files {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        #files a {
            transition: all 0.3s;
            opacity: 0.7;
            border: solid 1px #d3d3d3;
            padding: 1px;
            display: inline-block;
            margin-bottom: 5px;
            vertical-align: middle;
            align-items: center;
            margin-right: 5px;
            display: flex;
            align-items: center;
        }

        #files a:hover {
            opacity: 1.0;
        }
    </style>

    <script type="text/javascript">
        function WantThis(url, description)
        {
            window.opener.CKEDITOR.tools.callFunction('<?php print $_REQUEST['CKEditorFuncNum']; ?>', url);
            window.close();
        }
    </script>

    <h3 style="border-bottom: solid 1px #d3d3d3; padding-bottom: 3px;">
        <?php print t("Click on the image to add.") ?>
    </h3>

    <div id="files" >
    <?php
        $images = Jaris\Pages\Images::getList($uri);

        if($uri && $images)
        {
            $image_list = "";

            foreach($images as $id => $fields)
            {
                $image_url = Jaris\Uri::url("image/$uri/{$fields['name']}");
                $image_thumbnail = Jaris\Uri::url("image/$uri/{$fields['name']}", array("w" => "100"));
                $image_list .= "<a title=\"{$fields['description']}\" href='#' onclick='WantThis(\"$image_url\", \"{$fields['description']}\")'><img src=\"$image_thumbnail\" /></a>";
            }

            print $image_list;
        }
        else
        {
            print "<h2>" . t("No images available.") . "</h2>";
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
