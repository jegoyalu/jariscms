<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module functions file.
 */

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Site::SIGNAL_PAGE_DATA,
    function(&$page_data)
    {
        if($page_data[0]["type"] == "faq")
        {
            Jaris\View::addStyle(Jaris\Modules::directory("faq")
                . "styles/jquery.simpleFAQ.css")
            ;

            Jaris\View::addScript(Jaris\Modules::directory("faq")
                . "scripts/jquery.simpleFAQ.js")
            ;
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Forms::SIGNAL_GENERATE_FORM,
    function(&$parameters, &$fieldsets)
    {
        if(
            Jaris\Uri::get() == "admin/pages/add" &&
            $parameters["name"] == "add-page-faq"
        )
        {
            Jaris\View::addScript(Jaris\Modules::directory("faq")
                . "scripts/questions.js")
            ;

            $attributes_html = Jaris\Forms::beginFieldset(
                t("Questions"), true, false
            );

            $attributes_html .= "<script>\n"
                . "question_label='".t("Question")."';\n"
                . "answer_label='".t("Answer")."';\n"
                . "</script>\n"
            ;

            $attributes_html .= '<table id="questions-table" style="width: 100%">';
            $attributes_html .= '<thead>';
            $attributes_html .= '<tr>';
            $attributes_html .= '<td style="width: auto"></td>';
            $attributes_html .= '<td style="width: 20px"></td>';
            $attributes_html .= '</tr>';
            $attributes_html .= '</thead>';
            $attributes_html .= '<tbody>';
            $attributes_html .= '</tbody>';
            $attributes_html .= '</table>';

            $attributes_html .= '<a id="add-question" '
                . 'href="javascript:add_question()" '
                . 'style="cursor: pointer; display: block; margin-top: 8px">'
                . t("Add Question")
                . '</a>'
            ;

            $attributes_html .= Jaris\Forms::endFieldset();

            $field = array("type"=>"other", "html_code"=>$attributes_html);

            Jaris\Forms::addFieldAfter($field, "content", $fieldsets);
        }
        elseif(
            (
                Jaris\Uri::get() == "admin/pages/edit" &&
                $parameters["name"] == "edit-page-faq"
            ) ||
            (
                Jaris\Uri::get() == "admin/languages/translate" &&
                $_REQUEST["type"] == "page"
            )
        )
        {
            $page_data = Jaris\Pages::get($_REQUEST["uri"]);

            if(
                Jaris\Uri::get() == "admin/languages/translate" &&
                $page_data["type"] != "faq"
            )
            {
                return;
            }
            elseif(Jaris\Uri::get() == "admin/languages/translate")
            {
                $page_data = Jaris\Pages::get(
                    $_REQUEST["uri"],
                    $_REQUEST["code"]
                );
            }

            if(isset($page_data["questions"]))
            {
                $page_data["questions"] = unserialize($page_data["questions"]);
            }

            Jaris\View::addScript(
                Jaris\Modules::directory("faq")
                    . "scripts/questions.js"
            );

            $attributes_html = Jaris\Forms::beginFieldset(
                t("Questions"), true, true
            );

            $attributes_html .= '<table id="questions-table" style="width: 100%">';
            $attributes_html .= '<thead>';
            $attributes_html .= '<tr>';
            $attributes_html .= '<td style="width: auto"></td>';
            $attributes_html .= '<td style="width: 20px"></td>';
            $attributes_html .= '</tr>';
            $attributes_html .= '</thead>';
            $attributes_html .= '<tbody>';

            $question_id = 1;
            $whizzywig_html = "";

            if(is_array($page_data["questions"]))
            {
                $questions = "";

                foreach($page_data["questions"] as $question=>$answer)
                {
                    $question = htmlspecialchars($question);
                    $answer = htmlspecialchars($answer);

                    $questions .= '<tr style="width: 100%; '
                        . 'border-bottom: solid 1px #d3d3d3; '
                        . 'margin-bottom: 15px;" '
                        . 'id="question-'.$question_id.'">'
                    ;

                    $questions .= '<td style="width: auto">';

                    $questions .= '<div style="padding-top: 7px; '
                        . 'margin-bottom: 3px;">'
                        . '<input style="width: 90%;" '
                        . 'placeholder="'.t("Question").'" type="text" '
                        . 'name="question_title['.$question_id.']" '
                        . 'value="'.$question.'" />'
                        . '</div>'
                    ;

                    $questions .= '<div style="padding-bottom: 7px;">'
                        . '<textarea style="width: 90%;" '
                        . 'id="answer-'.$question_id.'" '
                        . 'placeholder="'.t("Answer").'" '
                        . 'name="question_answer['.$question_id.']">'
                        .$answer
                        .'</textarea>'
                        . '</div>'
                    ;

                    $questions .= '</td>';

                    $questions .= "<td style=\"width: auto; text-align: center; "
                        . "vertical-align: center;\">"
                    ;

                    $questions .= "<a "
                        . "href=\"javascript:remove_question(".$question_id.")\">"
                        . "X"
                        . "</a>"
                    ;

                    $questions .= "</td>";

                    $questions .= "</tr>";

                    $question_id++;
                }

                $attributes_html .= $questions;

                $whizzywig_html .= '$(document).ready(function(){' . "\n";
                $whizzywig_html .= 'if(typeof whizzywig == "object")' . "\n";
                $whizzywig_html .= '{' . "\n";

                for($current_id=1; $current_id<=$question_id; $current_id++)
                {
                    $whizzywig_html .= '  whizzywig.makeWhizzyWig("answer-"+'.$current_id.', "all");' . "\n";
                }

                $whizzywig_html .= '}' . "\n";
                $whizzywig_html .= 'else if(typeof CKEDITOR == "object")' . "\n";
                $whizzywig_html .= '{' . "\n";

                for($current_id=1; $current_id<=$question_id; $current_id++)
                {
                    $whizzywig_html .= '  CKEDITOR.replace("answer-"+'.$current_id.');' . "\n";
                }

                $whizzywig_html .= '}' . "\n";
                $whizzywig_html .= '});' . "\n";
            }

            $attributes_html .= '</tbody>';
            $attributes_html .= '</table>';

            $attributes_html .= '<div><a id="add-question" '
                . 'href="javascript:add_question()" '
                . 'style="cursor: pointer; display: block; margin-top: 8px">'
                . t("Add Question")
                . '</a></div>'
            ;

            $attributes_html .= "<script>\n"
                . "question_id=".$question_id.";\n"
                . "question_label='".t("Question")."';\n"
                . "answer_label='".t("Answer")."';\n"
                . "$whizzywig_html"
                . "</script>\n"
            ;

            $attributes_html .= Jaris\Forms::endFieldset();

            $field = array("type"=>"other", "html_code"=>$attributes_html);

            Jaris\Forms::addFieldAfter($field, "content", $fieldsets);
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_CREATE_PAGE,
    function(&$uri, &$data, &$path)
    {
        if($data["type"] == "faq")
        {
            $questions = array();

            if(is_array($_REQUEST["question_title"]))
            {
                foreach($_REQUEST["question_title"] as $pos=>$title)
                {
                    $questions[$title] = $_REQUEST["question_answer"][$pos];
                }

                $data["questions"] = serialize($questions);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Pages::SIGNAL_EDIT_PAGE_DATA,
    function(&$page, &$new_data, &$page_path)
    {
        if($new_data["type"] == "faq")
        {
            $questions = array();

            if(is_array($_REQUEST["question_title"]))
            {
                foreach($_REQUEST["question_title"] as $pos=>$title)
                {
                    $questions[$title] = $_REQUEST["question_answer"][$pos];
                }

                $new_data["questions"] = serialize($questions);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\Translate::SIGNAL_TRANSLATE_PAGE,
    function(&$page, &$new_data, &$language_code)
    {
        if($new_data["type"] == "faq")
        {
            //Keep original questions translation when editing the page
            if(Jaris\Uri::get() == "admin/pages/edit")
            {
                $translation_data = Jaris\Pages::get(
                    $page,
                    $language_code
                );

                $new_data["questions"] = $translation_data["questions"];

                return;
            }

            //This code gets executed when translating.
            $questions = array();

            if(is_array($_REQUEST["question_title"]))
            {
                foreach($_REQUEST["question_title"] as $pos=>$title)
                {
                    $questions[$title] = $_REQUEST["question_answer"][$pos];
                }

                $new_data["questions"] = serialize($questions);
            }
        }
    }
);

Jaris\Signals\SignalHandler::listenWithParams(
    Jaris\View::SIGNAL_THEME_CONTENT,
    function(&$content, &$content_title, &$content_data)
    {
        if($content_data["type"] == "faq")
        {
            if(isset($content_data["questions"]))
            {
                $content_data["questions"] = unserialize(
                    $content_data["questions"]
                );
            }

            if(is_array($content_data["questions"]))
            {
                $faq = '<ul id="faqs">' . "\n";

                foreach($content_data["questions"] as $question=>$answer)
                {
                    $question = htmlspecialchars_decode($question);
                    $answer = htmlspecialchars_decode($answer);

                    $faq .= '<li>' . "\n"
                        . '<p class="question">'.t($question).'</p>' . "\n"
                        . '<div class="answer">'.t($answer).'</div>' . "\n"
                        . '<p class="tags"></p>' . "\n"
                        . '</li>' . "\n"
                    ;
                }

                $faq .= '</ul>' . "\n";

                $faq .= '<script>' . "\n"
                    . '$("#faqs").simpleFAQ();' . "\n"
                    . '</script>' . "\n"
                ;

                $content .= $faq;
            }
        }
    }
);