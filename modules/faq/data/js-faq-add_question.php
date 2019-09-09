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
        Javascript code to facilitate parts addition to engines.
    field;

    field: content
    //<script>
    question_id = 1;
    question_label = "<?php print t("Question") ?>";
    answer_label = "<?php print t("Answer") ?>";

    function add_question(question, answer)
    {
        var question_value="";
        var answer_value="";

        if(question)
            question_value = 'value="'+question+'"';

        if(answer)
            answer_value = answer;

        row = '<tr style="width: 100%; border-bottom: solid 1px #d3d3d3; margin-bottom: 15px;" id="question-' + question_id + '">';

        row += '<td><a class="sort-handle"></a></td>';

        row += '<td style="width: auto">';
        row += '<div style="padding-top: 7px; margin-bottom: 3px;"><input style="width: 90%;" placeholder="'+question_label+'" type="text" name="question_title[' + question_id + ']" '+question_value+' /></div>';
        row += '<div style="padding-bottom: 7px;"><textarea id="answer-'+question_id+'" style="width: 90%;" placeholder="'+answer_label+'" name="question_answer[' + question_id + ']">'+answer_value+'</textarea></div>';
        row += '</td>';

        row += "<td style=\"width: auto; text-align: center; vertical-align: center;\">";
        row += "<a href=\"javascript:remove_question(" + question_id + ")\">X</a>";
        row += "</td>";

        row += "</tr>";

        $("#questions-table > tbody").append($(row));

        if(typeof whizzywig == "object")
        {
            whizzywig.makeWhizzyWig("answer-"+question_id, "all");
        }
        <?php if(Jaris\Modules::isInstalled("ckeditor")){ ?>
        else if(typeof CKEDITOR == "object")
        {
            <?php
                $uicolor = unserialize(Jaris\Settings::get("uicolor", "ckeditor"));
                $plugins = unserialize(Jaris\Settings::get("plugins", "ckeditor"));

                if(!is_array($uicolor))
                    $uicolor = array();

                if(empty($uicolor[Jaris\Authentication::currentUserGroup()]))
                {
                    $uicolor[Jaris\Authentication::currentUserGroup()] = "FFFFFF";
                }

                if(
                    empty($plugins[Jaris\Authentication::currentUserGroup()]) &&
                    !is_array($plugins[Jaris\Authentication::currentUserGroup()])
                )
                {
                    $plugins[Jaris\Authentication::currentUserGroup()] = array(
                        "quicktable", "youtube", "codemirror"
                    );
                }

                $lang = "";
                if(Jaris\Language::getCurrent() == "es")
                {
                    $lang .= "language: 'es',";
                }

                $editor_image_browser = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("ckeditorpic", "ckeditor"),
                    array("uri" => $_REQUEST["uri"])
                );

                $editor_image_uploader = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("ckeditorpicup", "ckeditor"),
                    array("uri" => $_REQUEST["uri"])
                );

                $editor_link_browser = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("ckeditorlink", "ckeditor"),
                    array("uri" => $_REQUEST["uri"])
                );

                $editor_link_uploader = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("ckeditorlinkup", "ckeditor"),
                    array("uri" => $_REQUEST["uri"])
                );

                $editor_config = Jaris\Uri::url(
                    Jaris\Modules::getPageUri("ckeditorconfig", "ckeditor"),
                    array("group" => Jaris\Authentication::currentUserGroup())
                );

                $interface_color = $uicolor[Jaris\Authentication::currentUserGroup()];

                $plugins_list = implode(",", $plugins[Jaris\Authentication::currentUserGroup()]);

                $codemirror = in_array("codemirror", $plugins[Jaris\Authentication::currentUserGroup()]) ?
                    "codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},"
                    :
                    ""
                ;

                echo "CKEDITOR.replace('answer-'+question_id, {"
                    . "customConfig: '$editor_config',"
                    . "uiColor: '#$interface_color',"
                    . "filebrowserBrowseUrl: '$editor_link_browser',"
                    . "filebrowserImageBrowseUrl: '$editor_image_browser',"
                    . "filebrowserUploadUrl: '$editor_link_uploader',"
                    . "filebrowserImageUploadUrl: '$editor_image_uploader',"
                    . "extraPlugins: '$plugins_list',"
                    . "codemirror: {mode: 'application/x-httpd-php', theme: 'monokai'},"
                    . "$codemirror"
                    . "$lang"
                    . "});"
                ;
            ?>
        }
        <?php } ?>

        question_id++;
    }

    function remove_question(id)
    {
        $("#question-" + id).fadeOut("slow", function(){
            $(this).remove();
        });
    }
    //</script>
    field;

    field: rendering_mode
        javascript
    field;

    field: is_system
        1
    field;
row;
