<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file
 *
 * Stores the installation script for module.
 */

function markdown_install()
{
    if(!Jaris\InputFormats::get("markdown"))
    {
        $fields = array();

        $fields["name"] = "Markdown";
        $fields["description"] = "Automatically generates html code for markdown syntax.";
        $fields["allowed_tags"] = "";
        $fields["parse_url"] = false;
        $fields["parse_email"] = false;
        $fields["parse_line_breaks"] = false;
        $fields["is_system"] = true;

        Jaris\InputFormats::add("markdown", $fields);
    }
}

?>
