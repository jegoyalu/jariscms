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

    <!DOCTYPE html>
    <html>

    <head>
    <title><?php print t("File browser") ?></title>
    </head>

    <body>

    <div class="content">

    <style type="text/css">
        #files {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            width: 100%;
        }

        #files a {
            transition: all 0.3s;
            border-bottom: solid 1px #d3d3d3;
            padding: 55px 20px 15px 20px;
            vertical-align: middle;
            align-items: center;
            display: flex;
            align-items: center;
            color: #777777;
            text-decoration: none;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background: transparent url(<?php print Jaris\Uri::url(Jaris\Modules::directory("ckeditor") . "images/document.png"); ?>) no-repeat center top;
            flex-grow: 1;
        }

        #files a div {
            margin: 0 auto 0 auto;
            text-align: center;
        }

        #files a.pdf
        {
            background-image: url(<?php print Jaris\Uri::url(Jaris\Modules::directory("ckeditor") . "images/pdf.png"); ?>);
        }

        #files a.ppt, #files a.odp
        {
            background-image: url(<?php print Jaris\Uri::url(Jaris\Modules::directory("ckeditor") . "images/pdf.png"); ?>);
        }

        #files a.png, #files a.gif, #files a.jpg, #files a.jpeg
        {
            background-image: url(<?php print Jaris\Uri::url(Jaris\Modules::directory("ckeditor") . "images/image.png"); ?>);
        }

        #files a:hover {
            border-bottom: solid 1px #000;
            color: #000;
        }
    </style>

    <script type="text/javascript">
        function WantThis(url)
        {
            window.opener.CKEDITOR.tools.callFunction('<?php print $_REQUEST['CKEditorFuncNum']; ?>', url);
            window.close();
        }
    </script>

    <h3 style="border-bottom: solid 1px #d3d3d3; padding-bottom: 3px;">
        <?php print t("Click a file below to select.") ?>
    </h3>

    <div id="files" >
        <?php
        $files = Jaris\Pages\Files::getList($uri);

        if($files)
        {
            $flist = "";
            foreach($files as $file)
            {
                $url = Jaris\Uri::url("file/$uri/{$file['name']}");
                $file_class = end(explode(".", $file['name']));
                $file_description = trim($file["description"]) != "" ?
                    $file["description"] : $file["name"]
                ;

                $flist .= "<a title=\"$file_description\" class=\"$file_class\" href='#' onclick='WantThis(\"$url\")'>"
                    . "<div>" . $file['name'] . "</div>"
                    . "</a>";
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
