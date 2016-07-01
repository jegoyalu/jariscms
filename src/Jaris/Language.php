<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0 
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * All the functions related to the translation of strings
 */
class Language
{

/**
 * Uses the $language variable to search for a language file on the language
 * directory to translate a short string.
 *
 * @param string $textToTranslate Text that is going to be translated.
 * @param string $po_file Optional parameter to indicate specific po file to use
 * relative to current language. Example: install.po
 *
 * @return string Translation if availbale or original.
 * @original t
 */
static function translate($textToTranslate, $po_file = null)
{
    $language = Site::$language;

    //To reduce the parsing of the file and just parse once.
    static $lang;

    $translation = $textToTranslate;

    if(!$lang)
    {
        $files = array();

        //Add main website translations
        //In case that a module has a system translation we execute this
        //first to keep original ones
        if($po_file == null)
        {
            if(file_exists("language/" . $language . "/" . "strings.po"))
            {
                $files[] = "language/" . $language . "/" . "strings.po";
            }

            $files[] = Site::dataDir() . "language/" . $language . "/" . "strings.po";
        }
        else
        {
            if(file_exists("language/" . $language . "/" . $po_file))
            {
                $files[] = "language/" . $language . "/" . $po_file;
            }

            $files[] = Site::dataDir() . "language/" . $language . "/" . $po_file;
        }

        //Add activated modules translations if available
        foreach(Modules::getInstalled() as $module_dir)
        {
            $module_translation = "";

            if($po_file == null)
            {
                $module_translation = Modules::directory($module_dir) . "language/$language/" . "strings.po";
            }
            else
            {
                $module_translation = Modules::directory($module_dir) . "language/$language/" . $po_file;
            }

            if(is_file($module_translation))
                $files[] = $module_translation;
        }

        $lang = self::generateCache($language, $files);
    }

    if($textToTranslate != "")
    {
        $available_translation = "";

        if(isset($lang[$textToTranslate]))
        {
            $available_translation .= $lang[$textToTranslate];
        }

        if($available_translation != "")
        {
            $translation = $available_translation;
        }
    }


    return $translation;
}

/**
 * Generates a cache file for a list of po files.
 *
 * @param string $language The language of the file.
 * @param array $files List of po files to generate the cache.
 * @return array All string translations
 * @original generate_language_cache
 */
static function generateCache($language, $files)
{
    $cache_file = Site::dataDir() . "language_cache/$language";

    $lang = array();

    //Create cache file
    if(!file_exists($cache_file))
    {
        if(file_exists(Site::dataDir() . "language/" . $language))
        {
            foreach($files as $file)
            {
                //Store the file path on the cache file
                //to know later which files are cached
                $lang[$file] = md5_file($file);

                //Store translation strings
                $lang += self::poParse($file);
            }

            file_put_contents($cache_file, serialize($lang));
        }
    }

    //Use existing cache file and update if neccesary
    else
    {
        $lang = unserialize(file_get_contents($cache_file));

        //Check for not cached files
        $po_file_not_cached = false;

        foreach($files as $file)
        {
            if(!isset($lang[$file]) || $lang[$file] != md5_file($file))
            {
                $po_file_not_cached = true;
                $lang[$file] = md5_file($file);
                $lang = array_merge($lang, self::poParse($file));
            }
        }

        //Save new translation files to cache
        if($po_file_not_cached)
        {
            file_put_contents($cache_file, serialize($lang));
        }
    }

    return $lang;
}

/**
 * Checks the existance of a data file translated to the
 * language matching the $language variable if available.
 *
 * @param string $data_file Original data file.
 * @param string $language_code To use optional language on path
 * conversion instead of the global $language variable.
 * @param bool $force Force the static function to always return the
 * data path for a language even if doesnt exist.
 *
 * @return string Data file that contains the translation or
 * original data file if translation not available.
 * @original dt
 */
static function dataTranslate($data_file, $language_code = null, $force = false)
{
    $language = Site::$language;

    $new_data_file = "";

    if($language_code)
    {
        $new_data_file = Site::dataDir() . "language/" . $language_code .
            "/" . str_replace(Site::dataDir() . "", "", $data_file)
        ;
    }
    else
    {
        $new_data_file = Site::dataDir() . "language/" . $language . "/" .
            str_replace(Site::dataDir() . "", "", $data_file)
        ;
    }

    if(file_exists($new_data_file) || $force)
    {
        return $new_data_file;
    }
    else
    {
        return $data_file;
    }
}

/**
 * Retreive the available languages on the system.
 *
 * @return array All the available languages in the following format.
 * $language["code"] = "name", for example: $language["en"] = "English"
 * @original get_languages
 */
static function getInstalled()
{
    $languages = array();

    $lang_dir = opendir(Site::dataDir() . "language");

    $directory_array = array();

    if(!is_bool($lang_dir))
    {
        while(($file = readdir($lang_dir)) !== false)
        {
            $directory_array[] = $file;
        }
    }

    $found_language = false;
    if(count($directory_array) > 0)
    {
        foreach($directory_array as $directory)
        {
            $current_directory = Site::dataDir() . "language/" . $directory;

            if(is_dir($current_directory) && $directory != "template")
            {
                if(file_exists($current_directory . "/info.php"))
                {
                    include($current_directory . "/info.php");
                    $languages[$language["code"]] = $language["name"];
                    $found_language = true;
                }
            }
        }
    }

    //Always add english since it's the core language
    $languages["en"] = "English";


    return $languages;
}

/**
 * Retreive the language information on an array
 *
 * @param string $language_code Example: en, en_US, es, es_PR, etc...
 *
 * @return array Languages info in the following format:
 * array("code"=>val, "name"=>val, "translator"=>val, "translator_email"=>val, "contributors"=>val)
 * if language couldn't be found returns empty array.
 * @original get_language_info
 */
static function getInfo($language_code)
{
    $lang_dir = Site::dataDir() . "language/$language_code";

    if(is_dir($lang_dir))
    {
        if(file_exists($lang_dir . "/info.php"))
        {
            $language = array();

            include($lang_dir . "/info.php");

            return $language;
        }
    }

    return array();
}

/**
 * Gets the human readable name of a language code.
 *
 * @param string $language_code the machine code of the language.
 *
 * @return string The human readable name of language code.
 * @original get_language_name
 */
static function getName($language_code)
{
    $languages = self::getInstalled();

    return $languages[$language_code];
}

/**
 * Checks the $_REQUEST["language"] and stores it's value
 * on $_SESSION["language"].
 *
 * @return string The language code to use on the $_SESSION if available or the default one.
 * @original get_current_language
 */
static function getCurrent()
{
    $language = Site::$language;

    if(isset($_REQUEST["language"]))
    {
        setcookie(
            "language",
            $_REQUEST["language"],
            time() + (60 * 60 * 24 * 365),
            "/"
        );

        $_COOKIE["language"] = $_REQUEST["language"];

        return $_REQUEST["language"];
    }
    elseif(isset($_COOKIE["language"]))
    {
        return $_COOKIE["language"];
    }
    else
    {
        if($language == "autodetect")
        {
            self::detect();
        }

        return $language;
    }
}

/**
 * Checks if a given language is available on the system.
 *
 * @param string $code The language code to check if exists on the system.
 * @original language_exists
 */
static function exists($code)
{
    if(file_exists(Site::dataDir() . "language/$code"))
    {
        return true;
    }

    return false;
}

/**
 * Checks the user browser language and sets the global
 * language variable to it if possible.
 * @original language_auto_detect
 */
static function detect()
{
    $language = Site::$language;

    if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
    {
        $user_languages = explode(
            ",",
            str_replace(" ", "", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        );

        foreach($user_languages as $user_language)
        {
            $language_code_array = explode(";", $user_language);

            $language_code = explode("-", $language_code_array[0]);

            if(count($language_code) > 1)
            {
                $glue = implode("-", $language_code);
                if(self::exists($glue) || $glue == "en")
                {
                    $language = $glue;
                    return;
                }
                elseif(
                    self::exists($language_code[0]) ||
                    $language_code[0] == "en"
                )
                {
                    $language = $language_code[0];
                    return;
                }
            }
            elseif(
                self::exists($language_code[0]) ||
                $language_code[0] == "en"
            )
            {
                $language = $language_code[0];
                return;
            }
        }
    }

    //Default language in case no match found
    $language = "en";
}

/**
 * Retreive all existing languages.
 *
 * @return array List of languages suitable to generate a select list on a form.
 * @original language_codes
 */
static function getCodes()
{
    $codes = array(
        "Afrikaans" => "af",
        "Albanian" => "sq",
        "Arabic (Algeria)" => "ar-dz",
        "Arabic (Bahrain)" => "ar-bh",
        "Arabic (Egypt)" => "ar-eg",
        "Arabic (Iraq)" => "ar-iq",
        "Arabic (Jordan)" => "ar-jo",
        "Arabic (Kuwait)" => "ar-kw",
        "Arabic (Lebanon)" => "ar-lb",
        "Arabic (libya)" => "ar-ly",
        "Arabic (Morocco)" => "ar-ma",
        "Arabic (Oman)" => "ar-om",
        "Arabic (Qatar)" => "ar-qa",
        "Arabic (Saudi Arabia)" => "ar-sa",
        "Arabic (Syria)" => "ar-sy",
        "Arabic (Tunisia)" => "ar-tn",
        "Arabic (U.A.E.)" => "ar-ae",
        "Arabic (Yemen)" => "ar-ye",
        "Arabic" => "ar",
        "Armenian" => "hy",
        "Assamese" => "as",
        "Azeri" => "az",
        "Basque" => "eu",
        "Belarusian" => "be",
        "Bengali" => "bn",
        "Bulgarian" => "bg",
        "Catalan" => "ca",
        "Chinese (China)" => "zh-cn",
        "Chinese (Hong Kong SAR)" => "zh-hk",
        "Chinese (Macau SAR)" => "zh-mo",
        "Chinese (Singapore)" => "zh-sg",
        "Chinese (Taiwan)" => "zh-tw",
        "Chinese" => "zh",
        "Croatian" => "hr",
        "Czech" => "cs",
        "Danish" => "da",
        "Divehi" => "div",
        "Dutch (Belgium)" => "nl-be",
        "Dutch (Netherlands)" => "nl",
        "English (Australia)" => "en-au",
        "English (Belize)" => "en-bz",
        "English (Canada)" => "en-ca",
        "English (Ireland)" => "en-ie",
        "English (Jamaica)" => "en-jm",
        "English (New Zealand)" => "en-nz",
        "English (Philippines)" => "en-ph",
        "English (South Africa)" => "en-za",
        "English (Trinidad)" => "en-tt",
        "English (United Kingdom)" => "en-gb",
        "English (United States)" => "en-us",
        "English (Zimbabwe)" => "en-zw",
        "English" => "en",
        "English (United States)" => "us",
        "Estonian" => "et",
        "Faeroese" => "fo",
        "Farsi" => "fa",
        "Finnish" => "fi",
        "French (Belgium)" => "fr-be",
        "French (Canada)" => "fr-ca",
        "French (Luxembourg)" => "fr-lu",
        "French (Monaco)" => "fr-mc",
        "French (Switzerland)" => "fr-ch",
        "French (France)" => "fr",
        "FYRO Macedonian" => "mk",
        "Gaelic" => "gd",
        "Georgian" => "ka",
        "German (Austria)" => "de-at",
        "German (Liechtenstein)" => "de-li",
        "German (Luxembourg)" => "de-lu",
        "German (Switzerland)" => "de-ch",
        "German (Germany)" => "de",
        "Greek" => "el",
        "Gujarati" => "gu",
        "Hebrew" => "he",
        "Hindi" => "hi",
        "Hungarian" => "hu",
        "Icelandic" => "is",
        "Indonesian" => "id",
        "Italian (Switzerland)" => "it-ch",
        "Italian (Italy)" => "it",
        "Japanese" => "ja",
        "Kannada" => "kn",
        "Kazakh" => "kk",
        "Konkani" => "kok",
        "Korean" => "ko",
        "Kyrgyz" => "kz",
        "Latvian" => "lv",
        "Lithuanian" => "lt",
        "Malay" => "ms",
        "Malayalam" => "ml",
        "Maltese" => "mt",
        "Marathi" => "mr",
        "Mongolian (Cyrillic)" => "mn",
        "Nepali (India)" => "ne",
        "Norwegian (Bokmal)" => "nb-no",
        "Norwegian (Nynorsk)" => "nn-no",
        "Norwegian (Bokmal)" => "no",
        "Oriya" => "or",
        "Polish" => "pl",
        "Portuguese (Brazil)" => "pt-br",
        "Portuguese (Portugal)" => "pt",
        "Punjabi" => "pa",
        "Rhaeto-Romanic" => "rm",
        "Romanian (Moldova)" => "ro-md",
        "Romanian" => "ro",
        "Russian (Moldova)" => "ru-md",
        "Russian" => "ru",
        "Sanskrit" => "sa",
        "Serbian" => "sr",
        "Slovak" => "sk",
        "Slovenian" => "ls",
        "Sorbian" => "sb",
        "Spanish (Argentina)" => "es-ar",
        "Spanish (Bolivia)" => "es-bo",
        "Spanish (Chile)" => "es-cl",
        "Spanish (Colombia)" => "es-co",
        "Spanish (Costa Rica)" => "es-cr",
        "Spanish (Dominican Republic)" => "es-do",
        "Spanish (Ecuador)" => "es-ec",
        "Spanish (El Salvador)" => "es-sv",
        "Spanish (Guatemala)" => "es-gt",
        "Spanish (Honduras)" => "es-hn",
        "Spanish (Mexico)" => "es-mx",
        "Spanish (Nicaragua)" => "es-ni",
        "Spanish (Panama)" => "es-pa",
        "Spanish (Paraguay)" => "es-py",
        "Spanish (Peru)" => "es-pe",
        "Spanish (Puerto Rico)" => "es-pr",
        "Spanish (United States)" => "es-us",
        "Spanish (Uruguay)" => "es-uy",
        "Spanish (Venezuela)" => "es-ve",
        "Spanish (Traditional Sort)" => "es",
        "Sutu" => "sx",
        "Swahili" => "sw",
        "Swedish (Finland)" => "sv-fi",
        "Swedish" => "sv",
        "Syriac" => "syr",
        "Tamil" => "ta",
        "Tatar" => "tt",
        "Telugu" => "te",
        "Thai" => "th",
        "Tsonga" => "ts",
        "Tswana" => "tn",
        "Turkish" => "tr",
        "Ukrainian" => "uk",
        "Urdu" => "ur",
        "Uzbek" => "uz",
        "Vietnamese" => "vi",
        "Xhosa" => "xh",
        "Yiddish" => "yi",
        "Zulu" => "zu"
    );

    return $codes;
}

/**
 * Generates an html form that enables to change the language.
 *
 * @return string The html form code.
 * @original language_form
 */
static function generateForm()
{
    $clean_urls = Site::$clean_urls;
    $page = Uri::get();
    $base_url = Site::$base_url;
    $language = Site::$base_url;

    $form = "<div class=\"language-form\">
    <form action=\"\" action=\"post\">";

    //Adds the current page as a parameter in case clean urls is disabled
    //to avoid the loss of the current page.
    if(!$clean_urls)
    {
        $form .= "<input type=\"hidden\" name=\"p\" value=\"$page\" />";
    }

    $form .= "<select name=\"language\">";

    if($languages = self::getInstalled())
    {
        foreach($languages as $code => $name)
        {
            if($language == $code)
            {
                $form .= "<option selected=\"selected\" value=\"$code\">$name</option>";
            }
            else
            {
                $form .= "<option value=\"$code\">$name</option>";
            }
        }
    }

    $form .= "</select>
            <input type=\"submit\" value=\"" . t("Submit") . "\" />
        </form></div>";

    return $form;
}

/**
 * Adds a new language to the system.
 *
 * @param string $language_code The code of the language, example: es
 * @param string $name Readable name of language, example: Español
 * @param string $translator Name of translator.
 * @param string $translator_email Email address of translator.
 * @param string $contributors List of contributors.
 *
 * @return bool True if language created succesfully or false if not.
 * @original add_language
 */
static function add($language_code, $name, $translator, $translator_email, $contributors)
{
    $language_path = Site::dataDir() . "language/" . $language_code;

    if(file_exists($language_path))
    {
        View::addMessage(t("The language already exist."), "error");

        return false;
    }
    else
    {
        FileSystem::makeDir($language_path, 0755, true);
        copy("language/template/strings.pot", $language_path . "/strings.po");

        $language_info = $language_path . "/info.php";

        $content = "<?php\n";
        $content .= "\$language[\"code\"] = \"$language_code\";\n";
        $content .= "\$language[\"name\"] = \"$name\";\n";
        $content .= "\$language[\"translator\"] = \"" . addcslashes($translator, '"') . "\";\n";
        $content .= "\$language[\"translator_email\"] = \"" . addcslashes($translator_email, '"') . "\";\n";
        $content .= "\$language[\"contributors\"] = \"" . addcslashes($contributors, '"') . "\";\n";
        $content .= "?>";

        if(!file_put_contents($language_info, $content))
        {
            View::addMessage(t("Language could not be added, check your write permissions on the <b>language</b> directory."), "error");

            return false;
        }
    }

    return true;
}

/**
 * Edit an existing language on the system.
 *
 * @param string $language_code The code of the language to edit.
 * @param string $translator The main translator of the language
 * @param string $translator_email The email of main translator
 * @param string $contributors List of contributors seperated by new lines
 *
 * @return bool True if language was modified or false if not.
 * @original edit_language
 */
static function edit($language_code, $translator, $translator_email, $contributors)
{
    $language_path = Site::dataDir() . "language/" . $language_code;

    if(!file_exists($language_path))
    {
        return false;
    }
    else
    {
        $language_info = $language_path . "/info.php";

        $content = "<?php\n";
        $content .= "\$language[\"code\"] = \"$language_code\";\n";
        $content .= "\$language[\"name\"] = \"" . self::getName($language_code) . "\";\n";
        $content .= "\$language[\"translator\"] = \"" . addcslashes($translator, '"') . "\";\n";
        $content .= "\$language[\"translator_email\"] = \"" . addcslashes($translator_email, '"') . "\";\n";
        $content .= "\$language[\"contributors\"] = \"" . addcslashes($contributors, '"') . "\";\n";
        $content .= "?>";

        if(!file_put_contents($language_info, $content))
        {
            return false;
        }
    }

    return true;
}

/**
 * Gets the amount of system strings translated, using the file
 * language/strings.po as reference for calculations.
 *
 * @param string $language_code The code of the language to check for translations.
 *
 * @return array In the format array("total_strings", "translated_strings", "percent")
 * @original amount_translated
 */
static function amountTranslated($language_code)
{
    //Check the template file for amount of strings
    $file = "language/template/strings.pot";
    $lang = self::poParse($file);

    $total_strings = count($lang);

    foreach($lang as $translation)
    {
        if($translation == "")
            $total_strings--;
    }

    unset($lang);

    $lang = self::getStrings($language_code);
    $total_strings += count($lang);

    $translated_strings = $total_strings;
    foreach($lang as $translation)
    {
        if(
            trim($translation["translation"]) == ""
            &&
            $translation["original"] != ""
        )
        {
            $translated_strings--;
        }
    }

    unset($lang);

    $percent = 0;
    if($translated_strings > 0)
    {
        $percent = round(($translated_strings / $total_strings) * 100, 2);
    }

    return array(
        "total_strings" => $total_strings,
        "translated_strings" => $translated_strings,
        "percent" => $percent
    );
}

/**
 * Retrieve all strings available for translation on a specific language.
 *
 * @param string $language_code The code of the language to retreive the strings.
 *
 * @return array In the format array[] = ("original"=>"text", "translation"=>"text")
 * @original get_language_strings
 */
static function getStrings($language_code)
{
    $strings = array();

    // Start with adding non translated system strings.
    $system_lang = array();

    if(file_exists("language/$language_code/strings.po"))
    {
        $system_lang = self::poParse("language/$language_code/strings.po");
    }

    foreach($system_lang as $index => $string)
    {
        if(trim($index) != "")
        {
            if($string == "")
            {
                $strings[] = array(
                    "original" => $index,
                    "translation" => $string
                );
            }
        }
    }

    $file = Site::dataDir() . "language/" . $language_code . "/strings.po";

    $lang = self::poParse($file);

    // Flag that indicates if uneeded system translations where found.
    $found_system = false;

    foreach($lang as $index => $string)
    {
        if(isset($system_lang[$index]) && trim($index) != "")
        {
            if($string != $system_lang[$index])
            {
                $strings[] = array(
                    "original" => $index,
                    "translation" => $string
                );
            }
            else
            {
                $found_system = true;
            }

            continue;
        }

        //Skip empty messages
        if(trim($index) != "")
        {
            $strings[] = array(
                "original" => $index,
                "translation" => $string
            );
        }
    }

    unset($system_lang);
    unset($lang);

    // Clean uneeded system translations
    if($found_system)
    {
        $lang = array();

        foreach($strings as $index => $translation)
        {
            $lang[$translation["original"]] = $translation["translation"];
        }

        self::poWrite(
            $lang,
            Site::dataDir() . "language/" . $language_code . "/strings.po"
        );
    }

    return $strings;
}

/**
 * Adds or edits a current string on the language strings.po file.
 *
 * @param string $language_code The language code to add or edit string.
 * @param string $original_text The original english text of the string.
 * @param string $translation The translation of the english text.
 *
 * @return bool True if changes applied successfully of false if not.
 * @original add_language_string
 */
static function addString($language_code, $original_text, $translation)
{
    $file = Site::dataDir() . "language/" . $language_code . "/strings.po";

    $lang = self::poParse($file);

    $lang[$original_text] = $translation;

    if(self::poWrite($lang, $file))
    {
        unset($lang);
        return true;
    }
    else
    {
        unset($lang);
        return false;
    }
}

/**
 * Remove a current string on the language strings.po file.
 *
 * @param string $language_code The language code to remove the string.
 * @param string $original_text The original english text of the string.
 *
 * @return bool True if changes applied successfully of false if not.
 * @original delete_language_string
 */
static function deleteString($language_code, $original_text)
{
    $file = Site::dataDir() . "language/" . $language_code . "/strings.po";

    $lang = self::poParse($file);

    unset($lang[$original_text]);

    if(self::poWrite($lang, $file))
    {
        unset($lang);
        return true;
    }
    else
    {
        unset($lang);
        return false;
    }
}

/**
 * Parses a .pot file generated by gettext tools.
 *
 * @param string $file The path of the file to translate.
 *
 * @return array In the format array["original text"] = "translation" or
 * empty array if translation doesn't exists.
 * @original po_parser
 */
static function poParse($file)
{
    if(!file_exists($file))
    {
        return array();
    }

    $file_rows = file($file);

    $original_string = "";
    $translations = array();

    $found_original = false;

    foreach($file_rows as $row_position=>$row)
    {
        if(!$found_original)
        {
            if(substr(trim($row), 0, 6) == "msgid ")
            {
                $found_original = true;
                $string = str_replace("msgid ", "", trim($row));
                $pattern = "/(\")(.*)(\")/";
                $replace = "\$2";

                if($string != '""')
                {
                    $string = preg_replace($pattern, $replace, $string);

                    $string = str_replace(
                        array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""),
                        array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""),
                        $string
                    );

                    $original_string = $string;
                }
                else
                {
                    $original_string = "";

                    $row_position++;
                    $string = trim($file_rows[$row_position]);

                    while($string{0} == '"')
                    {
                        $original_string .= preg_replace(
                            $pattern,
                            $replace,
                            $string
                        );

                        $row_position++;
                        $string = trim($file_rows[$row_position]);
                    }

                    $original_string = str_replace(
                        array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""),
                        array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""),
                        $original_string
                    );
                }
            }
        }
        else
        {
            if(substr(trim($row), 0, 7) == "msgstr ")
            {
                $found_original = false;
                $string = str_replace("msgstr ", "", trim($row));

                $pattern = "/(\")(.*)(\")/";
                $replace = "\$2";

                if($string != '""')
                {
                    $string = preg_replace($pattern, $replace, $string);

                    $string = str_replace(
                        array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""),
                        array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""),
                        $string
                    );

                    if($string != "")
                        $translations[$original_string] = $string;
                }
                else
                {
                    $translation_string = "";

                    $row_position++;

                    $string = "";
                    if(isset($file_rows[$row_position]))
                    {
                        $string .= trim($file_rows[$row_position]);
                    }

                    while(!empty($string[0]) && $string{0} == '"')
                    {
                        $translation_string .= preg_replace(
                            $pattern,
                            $replace,
                            $string
                        );

                        $row_position++;
                        $string = trim($file_rows[$row_position]);
                    }

                    $translation_string = str_replace(
                        array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""),
                        array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""),
                        $translation_string
                    );

                    if($translation_string != "")
                        $translations[$original_string] = $translation_string;
                }
            }
        }
    }

    unset($file_rows);

    return $translations;
}

/**
 * Parses a .pot file generated by gettext tools with
 * extra information included.
 * @param string $file
 * @original po_parser_with_headers
 */
static function poParseWithHeaders($file)
{
    //TODO: Implement this function
}

/**
 * Writes a simple .pot file for the use of jariscms.
 *
 * @param array $strings_array In the format
 * array["original string"] = "translation"
 * @param string $file the path of the file to output.
 *
 * @return bool true on success false on fail.
 * @original po_writer
 */
static function poWrite($strings_array, $file)
{
    $content = "";

    foreach($strings_array as $original => $translation)
    {
        $original = addcslashes($original, "\n\t\r\0\"\v\f\\");
        $content .= "msgid \"$original\"\n";

        $translation = addcslashes($translation, "\n\t\r\0\"\v\f\\");
        $content .= "msgstr \"$translation\"\n\n";
    }

    if(!file_put_contents($file, $content))
    {
        unset($content);
        return false;
    }

    unset($content);
    return true;
}

}