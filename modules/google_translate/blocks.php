<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the center blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: module_identifier
        google_translate_block
    field;

    field: position
        none
    field;

    field: description
        google translate
    field;

    field: title
        Translate
    field;

    field: content
    <?php
        $translante_language = "";

        if ($language = Jaris\Settings::get("input_language", "google_translate")) {
            $translante_language = $language;
        } else {
            $translante_language = "en";
        }
    ?>
    <div id="google_translate_element"></div>
    <script>
        function googleTranslateElementInit(){
            new google.translate.TranslateElement(
                {
                    pageLanguage: '<?php print $translante_language ?>'
                },
                'google_translate_element'
            );
        }
    </script>
    <script src="http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    field;

    field: order
        0
    field;

    field: display_rule
        all_except_listed
    field;

    field: pages

    field;

    field: return

    field;

    field: is_system
        1
    field;
row;