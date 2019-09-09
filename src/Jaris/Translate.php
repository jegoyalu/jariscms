<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions dealing with the translation of content.
 */
class Translate
{

/**
 * Receives parameters: $page, $data, $language_code
 * @var string
 */
    const SIGNAL_TRANSLATE_PAGE = "hook_translate_page";

    /**
     * Receives parameters: $fields, $language_code
     * @var string
     */
    const SIGNAL_TRANSLATE_BLOCK = "hook_translate_block";

    /**
     * Creates translation for a given page and stores it.
     *
     * @param string $page The uri of the page to translate,
     * example: mysection/mypage
     * @param array $data An array of the translated data in the format:
     * data = array("title"=>"value", "content"=>value ...)
     * @param string $language_code
     *
     * @return bool False if failed to wrote or true on success.
     */
    public static function page(string $page, array $data, string $language_code): bool
    {
        $path = Language::dataTranslate(
        Pages::getPath($page),
        $language_code,
        true
    );

        $data["users"] = serialize($data["users"]);
        $data["groups"] = serialize($data["groups"]);
        $data["categories"] = serialize($data["categories"]);

        //Edit translation if already exist
        if (file_exists($path)) {
            //Call create_page hook before creating the page
            Modules::hook("hook_translate_page", $page, $data, $language_code);

            Data::edit(0, $data, $path . "/data.php");
        }

        //Create translation if doesnt exist.
        else {
            FileSystem::makeDir($path, 0755, true);
            FileSystem::makeDir($path . "/blocks", 0755, true);

            if (!Data::add($data, $path . "/data.php")) {
                return false;
            }
        }

        return true;
    }

    /**
     * Used to move a translation from location when a page uri is changed.
     *
     * @param string $actual_uri The original uri of the page.
     * @param string $new_uri The new uri or path of the page.
     *
     * @return bool True on success false on fail.
     */
    public static function movePage(string $actual_uri, string $new_uri): bool
    {
        $languages = Language::getInstalled();

        //move all tranaslations of the specified page
        foreach ($languages as $code => $name) {
            $actual_path = Language::dataTranslate(
            Pages::getPath($actual_uri),
            $code,
            true
        );

            $new_path = Language::dataTranslate(
            Pages::getPath($new_uri),
            $code,
            true
        );

            if (file_exists($actual_path)) {
                if (FileSystem::makeDir($new_path, 0755, true)) {
                    FileSystem::recursiveMoveDir($actual_path, $new_path);

                    //Clears the page directory to be able to delete it
                    FileSystem::recursiveRemoveDir($actual_path, true);

                    self::removeEmptyDirectories($actual_path, $code);
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Delete all the translations for a page.
     *
     * @param string $page the uri of the page to delete its translations.
     *
     * @return bool True on success false on fail.
     */
    public static function deletePage(string $page): bool
    {
        $languages = Language::getInstalled();

        //Delete all tranaslations of the specified page
        foreach ($languages as $code => $name) {
            $path = Language::dataTranslate(Pages::getPath($page), $code, true);

            if (file_exists($path)) {
                //Clears the page directory to be able to delete it
                if (!FileSystem::recursiveRemoveDir($path, true)) {
                    return false;
                }

                self::removeEmptyDirectories($path, $code);
            }
        }

        return true;
    }

    /**
     * Creates translation for a given block and stores it.
     *
     * @param array $data An array of the translated data in the format:
     * data = array("title"=>"value", "content"=>value ...)
     * @param string $language_code
     *
     * @return bool False if failed to wrote or true on success.
     */
    public static function block(array $data, string $language_code): bool
    {
        $path = Language::dataTranslate("blocks", $language_code, true);

        if (!is_dir($path)) {
            FileSystem::makeDir($path, 0755, true);
        }

        $fields = [
        "description" => $data["description"],
        "title" => $data["title"]
    ];

        if (isset($data["content"])) {
            $fields["content"] = $data["content"];
        }

        //Call translate_block hook before string the translation
        Modules::hook("hook_translate_block", $fields, $language_code);

        $file = $path . "/{$data["id"]}.php";

        //Edit translation if already exist
        if (file_exists($file)) {
            return Data::edit(0, $fields, $file);
        }

        //Create translation if doesnt exist.
        else {
            return Data::add($fields, $file);
        }

        return false;
    }

    /**
     * Starts deleting empty directories from the deepest one to its root.
     *
     * @param string $path The path in which the empty directories are
     * going to be deleted.
     * @param string $code The language code.
     */
    public static function removeEmptyDirectories(string $path, string $code): void
    {
        //This is the directory that is not going to be deleted
        $main_dir = Site::dataDir() . "language/$code/pages/singles/";

        //Checks if the path belongs to the sections path
        $path = str_replace(
        Site::dataDir() . "language/$code/pages/sections/",
        "",
        $path,
        $count
    );

        if ($count > 0) {
            $main_dir = Site::dataDir() . "language/$code/pages/sections/";
        } else {
            $path = str_replace(
            Site::dataDir() . "language/$code/pages/singles/",
            "",
            $path,
            $count
        );
        }

        $directories = explode("/", $path);
        $directory_count = count($directories);

        for ($i = 0; $i < $directory_count; $i++) {
            $sub_directory = "";
            for ($c = 0; $c < $directory_count - $i; $c++) {
                $sub_directory .= $directories[$c] . "/";
            }

            rmdir($main_dir . $sub_directory);
        }
    }
}
