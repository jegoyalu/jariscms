<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Functions related to the translation of uri's to appropiate system's
 * path and others.
 */
class Uri
{

/**
 * The current uri is cached on this var by self::get() for faster
 * retreival.
 * @var string
 */
    public static $current_uri;

    /**
     * Verifies the $_REQUEST['p'] used to change to different pages
     *
     * @return string home page if $_REQUEST['p'] is null or
     * the $_REQUEST['p'] value.
     */
    public static function get(): string
    {
        if (!self::$current_uri) {
            //Default home page.
            self::$current_uri = "home";

            if (!empty($_REQUEST['p'])) {
                self::$current_uri = trim($_REQUEST['p'], "/");
            }
            //Try to get home page set on site settings
            elseif ($home_page = Settings::get("home_page", "main")) {
                self::$current_uri = $home_page;
            }
        }

        return self::$current_uri;
    }

    /**
     * Checks an uri type.
     *
     * @param string $uri The uri to check for its type.
     *
     * @return string One of these values: page, user_picture, image, file, category.
     */
    public static function type(string $uri): string
    {
        $sections = explode("/", $uri);

        if ($sections[0] == "image" && $sections[1] == "user") {
            return "user_picture";
        } elseif ($sections[0] == "image") {
            return "image";
        } elseif ($sections[0] == "file") {
            return "file";
        } elseif ($sections[0] == "category") {
            return "category";
        } elseif (
        count($sections) == 2 &&
        $sections[0] == "user" &&
        Site::$user_profiles
    ) {
            return "user_profile";
        } else {
            return "page";
        }
    }

    /**
     * Translates an image uri to its real data path.
     *
     * @param string $uri The uri of the image to translate in
     * form of image/page/imageid.
     *
     * @return string The full path to the image file or "" if not found.
     */
    public static function getImagePath(string $uri): string
    {
        $data_file = Site::dataDir() . "pages/";
        $sections = explode("/", $uri);
        $image_id = $sections[count($sections) - 1];
        $sections_available = count($sections) - 2;

        if (count($sections) > 3) {
            $data_file .= "sections/";

            for ($i = 1; $i < $sections_available; ++$i) {
                $data_file .= $sections[$i] . "/";
            }

            $data_file .= substr($sections[$sections_available], 0, 1) . "/" .
            substr($sections[$sections_available], 0, 2) . "/" .
            $sections[$sections_available] . "/images.php";
        } else {
            $data_file .= "singles/";
            $data_file .= substr($sections[1], 0, 1) . "/" .
            substr($sections[1], 0, 2) . "/" . $sections[1] . "/images.php"
        ;
        }

        $images = Data::parse($data_file);

        //Search for the image id and return its path
        if ($images) {
            foreach ($images as $row => $fields) {
                //Return by image name
                if (strcmp($image_id, trim($fields['name'])) == "0") {
                    return str_replace(
                    "images.php",
                    "images/" . trim($fields['name']),
                    $data_file
                );
                }

                //Return by image id
                elseif (strcmp($row, $image_id) == "0") {
                    return str_replace(
                    "images.php",
                    "images/" . trim($fields['name']),
                    $data_file
                );
                }
            }
        }

        //Image not found if the end was reached
        return "";
    }

    /**
     * Translates a file uri to some useful info.
     *
     * @param string $uri The uri of the file to translate
     * in form of file/page/filename_or_id.
     *
     * @return array List in the format array(path, id, page_uri)
     * or empty array if not found.
     */
    public static function getFilePath(string $uri): array
    {
        //Remove the file/ part
        $uri = substr_replace($uri, "", 0, 5);

        $sections = explode("/", $uri);
        $file_id = $sections[count($sections) - 1];
        unset($sections[count($sections) - 1]);
        $uri = implode("/", $sections);

        $data_file = Pages\Files::getPath($uri);

        $files = Data::parse($data_file);

        $file_path = "";

        //Search for the file id and return its path
        foreach ($files as $row => $fields) {
            $found = false;

            if (strcmp($file_id, trim($fields['name'])) == "0") {
                $file_path = str_replace(
                "files.php",
                "files/" . trim($fields['name']),
                $data_file
            );

                $found = true;
            } elseif (strcmp($row, $file_id) == "0") {
                $file_path = str_replace(
                "files.php",
                "files/" . trim($fields['name']),
                $data_file
            );

                $found = true;
            }

            if ($found) {
                $file_array = [
                "path" => $file_path,
                "id" => $row,
                "page_uri" => $uri
            ];

                return $file_array;
            }
        }

        //File not found if the end was reached
        return [];
    }

    /**
     * Translates a user picture uri to some useful info.
     *
     * @param string $uri The uri of the user picture to
     * translate in form of image/user/username.
     *
     * @return array Data in the format array(username, path)
     * The path is set to false if no picture is found.
     */
    public static function getUserPicturePath(string $uri): array
    {
        $sections = explode("/", $uri);

        $uri_data = [
        "username" => $sections[2],
        "path" => Users::getPicturePath($sections[2])
    ];

        return $uri_data;
    }

    /**
     * Transform a page relative path to its uri.
     *
     * @param string $relative_path Path to trasform for example:
     * sections/admin/b/bl/blocks = admin/blocks or
     * singles/a/ac/access-denied = access-denied
     *
     * @return string Uri of the page.
     */
    public static function getFromPath(string $relative_path): string
    {
        $uri = "";

        $fragments = explode("/", $relative_path);

        $fragments_count = count($fragments);

        //Remove 2 letters folder.
        $fragments[$fragments_count - 2] = "";

        //Remove 1 letter folder.
        $fragments[$fragments_count - 3] = "";

        for ($i = 1; $i < $fragments_count; $i++) {
            if ($fragments[$i]) {
                $uri .= $fragments[$i] . "/";
            }
        }

        //remove last trailing slash
        $uri = rtrim($uri, "/");

        return $uri;
    }

    /**
     * This functions print the correct url based on clean_url or
     * simple ones, as check if the uri paramenter is a full address
     * like http://jegoyalu.com and just return it.
     *
     * @param string $uri The page address that we want to print of
     * full http address.
     * @param array $arguments The variables that we are going to pass
     * to the page in the format variables["name"] = "value"
     *
     * @return string A formatted url.
     * Example of clean url: mydomain.com/page?argument=value.
     * Without clean url mydomain.com/?p=page&argument=value
     */
    public static function url(string $uri, array $arguments = []): string
    {
        $base_url = Site::$base_url;
        $clean_urls = Site::$clean_urls;
        $static_images_generated = Site::$static_images_generated;

        static $image_static_serving = null;

        if ($image_static_serving == null) {
            $image_static_serving = Settings::get("image_static_serving", "main");
        }

        $url = "";

        if (
        "" . strpos($uri, "http://") . "" != "" ||
        "" . strpos($uri, "https://") . "" != ""
    ) {
            $url = $uri;
        } elseif (file_exists($uri)) {
            $url = $base_url . "/" . $uri;

            if (count($arguments) > 0) {
                $formated_arguments = "?";

                foreach ($arguments as $argument => $value) {
                    if (!is_array($value) && "" . $value . "" != "") {
                        $formated_arguments .= $argument . "=" .
                        rawurlencode($value) . "&"
                    ;
                    } elseif (is_array($value)) {
                        foreach ($value as $value_entry) {
                            if ("" . $value_entry . "" != "") {
                                $formated_arguments .= $argument . "[]=" .
                                rawurlencode($value_entry) . "&"
                            ;
                            }
                        }
                    }
                }

                $formated_arguments = rtrim($formated_arguments, "&");

                $url .= $formated_arguments;
            }
        } else {
            $url = "$base_url/";

            $url .= $clean_urls ? $uri : "?p=$uri";

            if (count($arguments) > 0) {
                $formated_arguments = $clean_urls ? "?" : "&";

                foreach ($arguments as $argument => $value) {
                    if (!is_array($value) && "" . $value . "" != "") {
                        $formated_arguments .= $argument . "=" .
                        rawurlencode($value) . "&"
                    ;
                    } elseif (is_array($value)) {
                        foreach ($value as $value_entry) {
                            if ("" . $value_entry . "" != "") {
                                $formated_arguments .= $argument . "[]=" .
                                rawurlencode($value_entry) . "&"
                            ;
                            }
                        }
                    }
                }

                $formated_arguments = rtrim($formated_arguments, "&");

                $url .= $formated_arguments;
            }
        }

        if ($image_static_serving) {
            $uri_type = Uri::type($uri);

            if ($uri_type == "image" || $uri_type == "user_picture") {
                //Static image is already avaible
                if ($image_url = Images::getStaticName($url)) {
                    return $image_url;
                }

                //Static image needs to be generated.
                else {
                    Site::$static_images_to_generate[$url] = 1;

                    if ($static_images_generated != true) {
                        //We set the static images generated flag to true in order
                        //to tell the internal cache system to store the cache of
                        //current page on next run with the static images url's
                        Site::$static_images_generated = true;
                    }
                }
            }
        }

        return $url;
    }

    /**
     * Stops php script execution and redirects to a new page.
     *
     * @param string $uri The page we are going to redirect.
     * @param array $arguments Arguments to pass to the url in the
     * format $arguments["name"] = "value"
     * @param bool $ssl Use ssl protocol when going to the page.
     */
    public static function go(string $uri, array $arguments = [], bool $ssl = false): void
    {
        if (!$ssl) {
            header("Location: " . self::url($uri, $arguments));
        } else {
            header(
            "Location: " .
            str_replace("http://", "https://", self::url($uri, $arguments))
        );
        }

        ob_end_clean();
        exit;
    }

    /**
     * Check if a url exists
     *
     * @param string $url The url to check.
     *
     * @return bool True if exist otherwise false.
     */
    public static function urlExists(string $url): bool
    {
        $url = @parse_url($url);

        if (!$url) {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int) $url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '') {
            $path = '/';
        }

        $path .= (isset($url['query'])) ? "?$url[query]" : '';

        if (PHP_VERSION >= 5) {
            $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
        } else {
            $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

            if (!$fp) {
                return false;
            }

            fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
            $headers = fread($fp, 4096);
            fclose($fp);
        }

        $headers = (is_array($headers)) ? implode("\n", $headers) : $headers;

        return (bool) preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
    }

    /**
     * Convertes any given string into a ready to use uri.
     *
     * @param string $string The string to convert to uri.
     * @param bool $allow_slashes If true, does not strip outs slashes (/).
     *
     * @return string uri ready to use
     */
    public static function fromText(string $string, bool $allow_slashes = false): string
    {
        $uri = $string;

        $uri = str_ireplace(
        [
            "á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "ñ",
            "Á", "É", "Í", "Ó", "Ú", "Ä", "Ë", "Ï", "Ö", "Ü", "Ñ"
        ],
        [
            "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n",
            "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n"
        ],
        $uri
    );

        $uri = trim($uri);

        $uri = strtolower($uri);

        // only take alphanumerical characters, but keep the spaces and dashes
        if (!$allow_slashes) {
            $uri = preg_replace('/[^a-zA-Z0-9 -]/', '', $uri);
        }

        // only take alphanumerical characters, but keep the
        // spaces, dashes and slashes
        else {
            $uri = preg_replace('/[^a-zA-Z0-9 -\/]/', '', $uri);
        }

        $uri = str_replace(' ', '-', $uri);

        //Replace consecutive dashes by a single one
        $uri = preg_replace('/([-]+)/', '-', $uri);

        return $uri;
    }
}
