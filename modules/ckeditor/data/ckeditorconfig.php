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
        CKEditor Config
    field;

    field: content
    <?php
        if (empty($_REQUEST["group"])) {
            exit;
        }

        $config = unserialize(Jaris\Settings::get("toolbar_items", "ckeditor"));

        $site_css = Jaris\Settings::get("use_site_css", "ckeditor");
        if (!empty($site_css)) {
            $site_css = unserialize($site_css);
        } else {
            $site_css = [];
        }

        $output = "";

        if (empty($config[$_REQUEST["group"]])) {
            $output .= "CKEDITOR.editorConfig = function( config ) {
            	config.toolbarGroups = [
            		{ name: 'styles', groups: [ 'styles' ] },
            		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
            		'/',
            		{ name: 'insert', groups: [ 'insert' ] },
            		{ name: 'links', groups: [ 'links' ] },
            		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
            		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            		{ name: 'forms', groups: [ 'forms' ] },
            		{ name: 'tools', groups: [ 'tools' ] },
            		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            		{ name: 'colors', groups: [ 'colors' ] },
            		{ name: 'others', groups: [ 'others' ] },
            		{ name: 'about', groups: [ 'about' ] }
            	];

            	config.removeButtons = 'Font,HiddenField,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,CreateDiv,Language,BidiRtl,BidiLtr,Save,NewPage,Preview,Print,About,TextColor,BGColor,Flash,Smiley,Iframe,PageBreak,Scayt';
            };//end";
        } else {
            $output .= trim($config[$_REQUEST["group"]]) . "//end";
        }

        $content_css = "";
        if (
            isset($site_css[$_REQUEST["group"]])
            &&
            $site_css[$_REQUEST["group"]]
        ) {
            $theme_path = trim(
                parse_url(Jaris\Site::$theme_path)["path"],
                "/"
            );
            if (file_exists($theme_path . "/style.css")) {
                $css = Jaris\Uri::url($theme_path . "/style.css");
                $content_css .= "config.contentsCss = '$css';\n        ";
            }
        }

        print str_replace(
            "};//end",
            "    config.allowedContent = true;\n        "
                . "config.protectedSource.push( /<\?[\s\S]*?\?>/g );\n        "
                . $content_css
                . "// PHP code\n};",
            $output
        );
    ?>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
