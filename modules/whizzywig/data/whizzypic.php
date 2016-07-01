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
        $groups = Jaris\Settings::get("groups", "whizzywig") ?
            unserialize(Jaris\Settings::get("groups", "whizzywig")) : false
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
                exit;
            }
        }

        $rtnfield = "if_url";
        $altfield = "if_alt";

        if($_REQUEST['element_id'])
        {
            $rtnfield = "if_url" . $_REQUEST['element_id'];
            $altfield = "if_alt" . $_REQUEST['element_id'];
        }

        $module_url = Jaris\Uri::url(Jaris\Modules::directory("whizzywig") . "whizzywig");

        $uri = $_REQUEST["uri"];
    ?>

    <html>

    <head>
    <title><?php print t("Image browser") ?></title>
    </head>

    <body>

    <div class="content">

    <style type="text/css">
        #picture {width:50%;height:100%;float:left;}

        #files {width:45%;height:100%;font-size:90%;float:right}

        #caption{font-size:1.2em}

        #preview {height:80%;width:100%}
    </style>

    <script type="text/javascript">
        function WantThis(url, description)
        {
            window.opener.document.getElementById('<?php echo $rtnfield; ?>').value = url;

            window.opener.document.getElementById('<?php echo $altfield; ?>').value = description;

            window.close();
        }
    </script>

    <div id="picture">
        <span id='caption'><?php print t("Image preview") ?></span><br><br>

        <iframe id='preview'>

        </iframe>
    </div>

    <div id="files" >
    <?php
        $images = Jaris\Pages\Images::getList($uri);

        if($uri && $images)
        {
            print "<h2>" .
                t("Hover over a name below to preview, click it to select.") .
                "</h2>" . "<br>"
            ;

            $image_list = "";

            foreach($images as $id => $fields)
            {
                $image_url = Jaris\Uri::url("image/$uri/{$fields['name']}");
                $image_list .= "<a href='#' onclick='WantThis(\"$image_url\", \"{$fields['description']}\")' onmouseover='document.getElementById(\"preview\").src=\"$image_url\";document.getElementById(\"caption\").innerHTML=\"<b>{$fields['name']}</b><br>{$fields['description']}\"'>{$fields['name']}</a></br>";
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
