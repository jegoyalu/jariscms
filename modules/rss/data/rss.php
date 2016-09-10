<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *https://opensource.org/licenses/GPL-3.0.
 *
 *@file Database file that stores the user list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        RSS
    field;

    field: content
        <?php
            $type_data = "";
            $type_name = "";

            if(trim($_REQUEST["type"]) != "")
            {
                $type_data = Jaris\Types::get($_REQUEST["type"]);
            }

            if(isset($type_data["name"]))
            {
                $type_name = t($type_data["name"]) . " - ";
            }

            $title = str_replace("&", "and", t(Jaris\Settings::get("title", "main")));
            $description = t(Jaris\Settings::get("title", "slogan"));
            $link = Jaris\Uri::url("");
            $last_build_date = date("r", time());

            $rss_settings = Jaris\Settings::getAll("rss");

            $rss_settings["description_words"] = $rss_settings["description_words"] ?
                $rss_settings["description_words"]
                :
                45
            ;

            $rss_settings["images_enable"] = isset($rss_settings["images_enable"]) ?
                $rss_settings["images_enable"]
                :
                true
            ;

            $rss_settings["images_keep_aspect_raio"] = isset($rss_settings["images_keep_aspect_raio"]) ?
                $rss_settings["images_keep_aspect_raio"]
                :
                true
            ;

            $rss_settings["images_width"] = $rss_settings["images_width"] ?
                $rss_settings["images_width"]
                :
                512
            ;

            $rss_settings["images_height"] = $rss_settings["images_height"] ?
                $rss_settings["images_height"]
                :
                384
            ;

            print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
                . "<rss version=\"2.0\">\n"
                . "<channel>\n"
                    . "<title>{$type_name}$title</title>\n"
                    . "<description>$description</description>\n"
                    . "<link>$link</link>\n"
                    . "<lastBuildDate>$last_build_date</lastBuildDate>\n"
            ;

            $type = "";
            if(trim($_REQUEST["type"]) != "")
            {
                $type = str_replace("'", "''", $_REQUEST["type"]);
                $type = "and type='$type'";
            }

            $user = Jaris\Authentication::currentUser();
            $group = Jaris\Authentication::currentUserGroup();

            $pages = Jaris\Sql::getDataList(
                "search_engine",
                "uris",
                0,
                20,
                "where has_permissions > 0 and has_user_permissions > 0 and "
                    . "approved='a' $type order by created_date desc",
                "uri, haspermission(groups, '$group') as has_permissions, "
                    . "hasuserpermission(users, '$user') as has_user_permissions"
            );

            $is_first = true;

            foreach($pages as $data)
            {
                $page_data = Jaris\Pages::get($data["uri"], Jaris\Language::getCurrent());

                $search = array (
                    "'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                    "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                    "'([\r\n])[\s]+'",                 // Strip out white space
                    "'&(quot|#34);'i",                 // Replace html entities
                    //"'&(amp|#38);'i",
                    "'&(nbsp|#160);'i",
                    "'&(iexcl|#161);'i",
                    "'&(cent|#162);'i",
                    "'&(pound|#163);'i",
                    "'&(copy|#169);'i",
                    "'&#(\d+);'e"
                );                    // evaluate as php

                $replace = array (
                    "",
                    "",
                    "\\1",
                    "\"",
                    //"&",
                    " ",
                    chr(161),
                    chr(162),
                    chr(163),
                    chr(169),
                    "chr(\\1)"
                );


                $title = str_replace(
                    array("&", "&amp;"),
                    "and",
                    $page_data["title"]
                );

                //$description = preg_replace(
                //$search, $replace,
                //Jaris\Util::contentPreview($page_data["content"], 45, true)
                //);

                $description = preg_replace(
                    "'([\r\n])[\s]+'",
                    "\\1",
                    Jaris\Util::contentPreview(
                        $page_data["content"],
                        $rss_settings["description_words"],
                        true
                    )
                );

                $link = Jaris\Uri::url($data["uri"]);

                $date = date("r", $page_data["created_date"]);

                $image_code = "";

                if($rss_settings["images_enable"])
                {
                    $image_list = Jaris\Pages\Images::getList($data["uri"]);

                    if(count($image_list) > 0)
                    {
                        foreach($image_list as $image)
                        {
                            $image_url = Jaris\Uri::url(
                                "image/" . $data["uri"] . "/" . $image["name"],
                                array(
                                    "w"=>$rss_settings["images_width"],
                                    "h"=>$rss_settings["images_height"],
                                    "ar"=>$rss_settings["images_keep_aspect_raio"]
                                )
                            );

                            $image_code = '<a href="'.$link.'"><img src="'.$image_url.'" /></a><br />';

                            break;
                        }
                    }
                }

                //Adds to the channel the publication date of latest content
                if($is_first)
                {
                    print "<pubDate>$date</pubDate>\n";
                    $is_first = false;
                }

                print "<item>\n"
                    . "<title>$title</title>\n"
                    . "<description>\n"
                    . "<![CDATA["
                    . $image_code
                    . $description
                    . "]]>\n"
                    . "</description>\n"
                    . "<link>$link</link>\n"
                    . "<pubDate>$date</pubDate>\n"
                . "</item>\n";
            }

            print "</channel>\n"
                . "</rss>"
            ;
        ?>
    field;

    field: rendering_mode
        xml
    field;

    field: is_system
        1
    field;
row;
