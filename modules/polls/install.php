<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *https://opensource.org/licenses/GPL-3.0.
 *
 *@file Jaris CMS module install file
 *
 *Stores the installation script for polls module.
 */

function polls_install()
{
    //To help translation tools
    $string = t("Poll");
    $string = t("A poll where users can vote.");
    $string = t("More Details");

    //Create new catalog type
    $new_type["name"] = "Poll";
    $new_type["description"] = "A poll where users can vote.";

    Jaris\Types::add("poll", $new_type);

    //Add polls block
    $block["description"] = "display the most recent poll";
    $block["title"] = "";
    $block["content"] = '';
    $block["order"] = "0";
    $block["display_rule"] = "all_except_listed";
    $block["pages"] = "";
    $block["return"] = "";
    $block["is_system"] = true;
    $block["return"] = "";
    $block["poll_block"] = "1";
    $block["poll_page"] = "";

    Jaris\Blocks::add($block, "none");
}

?>