<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Miscelaneous functions to manage files and directories.
 */
class FileSystem
{

/**
 * Copies a file to another path and renames it if
 * already a file with the same name exist. Also strips any special
 * characters. @see rename_file_if_exist()
 *
 * @param string $source The file to copy.
 * @param string $destination The new path of the file.
 *
 * @return string|bool File name of the new file copy with
 * the path stripped or false if failed.
 * @original copy_file
 */
static function copy($source, $destination)
{
    //Strip any special characters from filename
    $name = explode("/", $destination);

    $file_name = $name[count($name) - 1];

    $extension = "";

    $file_name_no_extension = Uri::fromText(
        self::stripExtension($file_name, $extension)
    );

    $name[count($name) - 1] = $file_name_no_extension . "." . $extension;

    $destination = implode("/", $name);

    $destination = self::renameIfExist($destination);

    if(!copy($source, $destination))
    {
        return false;
    }

    $name = explode("/", $destination);

    return $name[count($name) - 1];
}

/**
 * Moves a file to another path and renames it if
 * already a file with the same name exist. Also strips any special
 * characters. @see rename_file_if_exist()
 *
 * @param string $source The file to move.
 * @param string $destination The new path of the file.
 *
 * @return string|bool File name of the file moved with
 * the path stripped or false if failed.
 * @original move_file
 */
static function move($source, $destination)
{
    //Strip any special characters from filename
    $name = explode("/", $destination);

    $file_name = $name[count($name) - 1];

    $extension = "";

    $file_name_no_extension = Uri::fromText(
        self::stripExtension($file_name, $extension)
    );

    $name[count($name) - 1] = $file_name_no_extension . "." . $extension;

    $destination = implode("/", $name);

    $destination = self::renameIfExist($destination);

    if(!rename($source, $destination))
    {
        return false;
    }

    $name = explode("/", $destination);

    return $name[count($name) - 1];
}

/**
 * Check if a filename or directory already exist and generates a new one with a
 * number appended. For example if /home/test/text.txt exist
 * returns /home/test/text-0.txt
 *
 * @param string $file_name The full file path to check for existence.
 *
 * @return string The file name renamed if exist or the same file name.
 * @original rename_file_if_exist
 */
static function renameIfExist($file_name)
{
    $file_index = 0;

    //Check if the file already exist and appends an index
    //on it to not overwrite  the existing one.
    while(file_exists($file_name))
    {
        $segments = explode("/", $file_name);

        $filename_segments = explode(".", $segments[count($segments) - 1]);

        if(count($filename_segments) > 1)
        {
            $ext = "." . $filename_segments[count($filename_segments) - 1];
        }
        else
        {
            $ext = "";
        }

        $filename = "";

        for($i = 0; $i < count($segments) - 1; $i++)
        {
            $filename .= $segments[$i] . "/";
        }

        if(count($filename_segments) == 1)
        {
            $filename .= $segments[count($segments) - 1];
        }

        for($i = 0; $i < count($filename_segments) - 1; $i++)
        {
            $filename .= $filename_segments[$i];

            if($i != count($filename_segments) - 2)
            {
                $filename .= ".";
            }
        }

        $temp_destination_check = $filename . "-" . $file_index . $ext;
        if(file_exists($temp_destination_check))
        {
            $file_index++;
        }
        else
        {
            $file_name = $temp_destination_check;
        }
    }

    return $file_name;
}

/**
 * Search recursively for files in a directory relative to
 * jaris cms installation.
 *
 * @param string $path Relative path to jaris installation.
 * @param string $pattern A regular expression to match the file to search.
 * @param callable $callback static function to manage each file found that
 * needs one argument to accept the full path of match found and optional
 * second bool argument to indicate if search should stop. Example:
 * my_callback($full_file_path, &$stop_search).
 * @original search_files
 */
static function search($path, $pattern, $callback)
{
    $directory = opendir($path);

    while(($file = readdir($directory)) !== false)
    {
        $full_path = $path . "/" . $file;

        if(is_file($full_path) && preg_match($pattern, $file))
        {
            $stop_search = false;

            $callback($full_path, $stop_search);

            if($stop_search)
            {
                return false;
            }
        }
        elseif($file != "." && $file != ".." && is_dir($full_path))
        {
            if(!self::search($full_path, $pattern, $callback))
            {
                //if $stop_search was set to true
                //we stop the rest of searches
                return false;
            }
        }
    }

    closedir($directory);

    return true;
}

/**
 * Search recursively for files in a directory relative to
 * jaris cms installation.
 *
 * @param string $path Relative directory path to jaris installation.
 *
 * @return array List of files found.
 * @original get_dir_files
 */
static function getFiles($path)
{
    $files = array();
    $directory = opendir($path);

    while(($file = readdir($directory)) !== false)
    {
        $full_path = $path . "/" . $file;

        if(is_file($full_path))
        {
            $files[] = $full_path;
        }
        elseif($file != "." && $file != ".." && is_dir($full_path))
        {
            $files = array_merge($files, self::getFiles($full_path));
        }
    }

    closedir($directory);

    return $files;
}

/**
 * Same as php mkdir() but adds Operating system check and replaces
 * every / by \ on windows.
 *
 * @param string $directory The directory to create.
 * @param int $mode the permissions granted to the directory.
 * @param bool $recursive Recurse in to the path creating neccesary directories.
 *
 * @return bool true on success false on fail.
 * @original make_directory
 */
static function makeDir($directory, $mode = 0755, $recursive = false)
{
    if("" . strpos(PHP_OS, "WIN") . "" != "")
    {
        $directory = str_replace("/", "\\", $directory);
    }

    return @mkdir($directory, $mode, $recursive);
}

/**
 * Moves a directory and its content by renaming it to another directory even
 * if already exist, mergin the content of the source directory to the target
 * directory and replacing files.
 *
 * @param string $source The dirctory to rename.
 * @param string $target The target path of the source directory.
 *
 * @return bool true on success or false on fail.
 * @original recursive_move_directory
 */
static function recursiveMoveDir($source, $target)
{
    $source_dir = opendir($source);

    while(($item = readdir($source_dir)) !== false)
    {
        $source_full_path = $source . "/" . $item;
        $target_full_path = $target . "/" . $item;

        if($item != "." && $item != "..")
        {
            //Replace any existing file with source one
            if(is_file($source_full_path))
            {
                //Replace existing target file with source file
                if(file_exists($target_full_path))
                {
                    //Remove target file before replacing
                    if(!unlink($target_full_path))
                    {
                        return false;
                    }
                }

                //Move source file to target path
                if(!rename($source_full_path, $target_full_path))
                {
                    return false;
                }
            }
            else if(is_dir($source_full_path))
            {
                //If directory already exist just replace its content
                if(file_exists($target_full_path))
                {
                    self::recursiveMoveDir(
                        $source_full_path, $target_full_path
                    );
                }
                else
                {
                    //If directory doesnt exist just move source directory to target path
                    if(!rename($source_full_path, $target_full_path))
                    {
                        return false;
                    }
                }
            }
        }
    }

    closedir($source_dir);

    return true;
}

/**
 * Copy a directory and its content to another directory replacing any file
 * on the target directory if already exist.
 *
 * @param string $source The dirctory to copy.
 * @param string $target The copy destination.
 *
 * @return bool true on success or false on fail.
 * @original recursive_copy_directory
 */
static function recursiveCopyDir($source, $target)
{
    $source_dir = opendir($source);

    //Check if source directory exists
    if(!$source_dir)
    {
        return false;
    }

    //Create target directory in case it doesnt exist
    if(!file_exists($target))
    {
        self::makeDir($target, 0755, true);
    }

    while(($item = readdir($source_dir)) !== false)
    {
        $source_full_path = $source . "/" . $item;
        $target_full_path = $target . "/" . $item;

        if($item != "." && $item != "..")
        {
            //copy source files
            if(is_file($source_full_path))
            {
                if(!copy($source_full_path, $target_full_path))
                {
                    return false;
                }
            }
            else if(is_dir($source_full_path))
            {
                self::recursiveCopyDir($source_full_path, $target_full_path);
            }
        }
    }

    closedir($source_dir);

    return true;
}

/**
 * Remove a directory that is not empty by deleting all its content.
 *
 * @param string $directory The directory to delete with all its content.
 * @param bool $empty If true removes all directory contents keeping only itself.
 *
 * @return bool True on success or false.
 * @original recursive_remove_directory
 */
static function recursiveRemoveDir($directory, $empty = false)
{
    // if the path has a slash at the end we remove it here
    if(substr($directory, -1) == '/')
    {
        $directory = substr($directory, 0, -1);
    }

    // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory))
    {
        return false;

        // ... if the path is not readable
    }
    elseif(!is_readable($directory))
    {
        return false;
    }
    else
    {
        $handle = opendir($directory);

        while(false !== ($item = readdir($handle)))
        {
            if($item != '.' && $item != '..')
            {
                // we build the new path to delete
                $path = $directory . '/' . $item;

                // if the new path is a directory
                if(is_dir($path))
                {
                    self::recursiveRemoveDir($path);

                    // if the new path is a file
                }
                else
                {
                    if(!unlink($path))
                    {
                        return false;
                    }
                }
            }
        }

        closedir($handle);

        if($empty == false)
        {
            if(!rmdir($directory))
            {
                return false;
            }
        }

        return true;
    }
}

/**
 * Outputs any file that resides on the current server to the client.
 *
 * @param string $path The file on the current server.
 * @param string $name A name for the file when the download is forced.
 * Set to empty so browser uses the name on the url path.
 * @param bool $force_download Even is it is a text file is forced
 * to download on the browser.
 * @param bool $try_compression Checks if zip support is available
 * and compress the file.
 */
static function printFile(
    $path, $name = "file", $force_download = false, $try_compression = false
)
{
    // Do not lock subsequent requests.
    Session::close();

    if("" . stripos($path, ".php") . "" != "")
    {
        Site::setHTTPStatus(403);
        exit;
    }

    if(is_file($path))
    {
        if($try_compression)
        {
            if(class_exists("ZipArchive"))
            {
                if($name == "")
                {
                    $name = end(explode("/", $path));
                }

                $zip_file = self::stripExtension($path) . ".zip";

                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE);

                $zip->addFile($path, "$name");
                $zip->close();

                $path = $zip_file;
                $name = self::stripExtension($name) . ".zip";
            }
        }

    	$file_size  = filesize($path);
    	$file = @fopen($path,"rb");

        if($file)
    	{
            header("X-Powered-By: "); //Remove
    		header("Pragma: public");
            header("Etag: \"" . md5_file($path) . "\"");
            header("Cache-Control: max-age=1209600");
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . 'GMT');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)) . 'GMT');
    		/*header(
                "Cache-Control: public, must-revalidate, post-check=0, pre-check=0"
            );*/

            $file_name_header = "";
            if($name != "")
            {
                $file_name_header .= " filename=\"$name\"";
            }

            if($force_download)
            {
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment;$file_name_header");
            }
            else
            {
                header("Content-Disposition: inline;$file_name_header");
                header('Content-Transfer-Encoding: binary');
            }

            header("Content-Type: " . self::getMimeTypeLocal($path));

    		//check if http_range is sent by browser (or download manager)
    		if(isset($_SERVER['HTTP_RANGE']))
    		{
    			list($size_unit, $range_orig) = explode(
                    '=',
                    $_SERVER['HTTP_RANGE'],
                    2
                );

    			if($size_unit == 'bytes')
    			{
                    //http://www.media-division.com/the-right-way-to-handle-file-downloads-in-php/
    				//multiple ranges could be specified at the same time,
    				//but for simplicity only serve the first range
    				//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
    				list($range, $extra_ranges) = explode(
                        ',', $range_orig, 2
                    );
    			}
    			else
    			{
                    if($try_compression && class_exists("ZipArchive"))
                    {
                        unlink($path);
                    }

    				$range = '';
    				header('HTTP/1.1 416 Requested Range Not Satisfiable');
    				exit;
    			}
    		}
    		else
    		{
    			$range = '';
    		}

    		//figure out download piece from range (if set)
    		list($seek_start, $seek_end) = explode('-', $range, 2);

    		//set start and end based on range (if set), else set defaults
    		//also check for invalid ranges.
    		$seek_end   = (empty($seek_end)) ?
                ($file_size - 1)
                :
                min(abs(intval($seek_end)), ($file_size - 1))
            ;

    		$seek_start =
                (
                    empty($seek_start) || $seek_end < abs(intval($seek_start))
                ) ?
                0
                :
                max(abs(intval($seek_start)), 0)
            ;

    		//Only send partial content header if downloading a piece of the file (IE workaround)
    		if ($seek_start > 0 || $seek_end < ($file_size - 1))
    		{
    			header('HTTP/1.1 206 Partial Content');

    			header(
                    'Content-Range: bytes '
                    . $seek_start
                    . '-'
                    . $seek_end
                    . '/'
                    . $file_size
                );

                header('Content-Length: '.($seek_end - $seek_start + 1));
    		}
    		else
            {
                header("Content-Length: $file_size");
            }

    		header('Accept-Ranges: bytes');

    		set_time_limit(0);
    		fseek($file, $seek_start);

            //Print file to browser
            ob_end_clean();
            flush();

    		while(!feof($file))
    		{
    			print(@fread($file, 1024*8));

    			flush();

    			if(connection_status() != CONNECTION_NORMAL)
    			{
                    if($try_compression && class_exists("ZipArchive"))
                    {
                        unlink($path);
                    }

    				@fclose($file);
    				exit;
    			}
    		}

            if($try_compression && class_exists("ZipArchive"))
            {
                unlink($path);
            }

    		@fclose($file);
    		exit;
    	}
    	else
    	{
            if($try_compression && class_exists("ZipArchive"))
            {
                unlink($path);
            }

    		// file couldn't be opened
    		Site::setHTTPStatus(500);
    		exit;
    	}
    }
    else
    {
    	// file does not exist
    	Site::setHTTPStatus(404);
    	exit;
    }
}

/**
 * Removes the extension from a file name.
 *
 * @param string $filename The name or path of the file.
 * @param string $extension Set to the stripped extension.
 *
 * @return string The file name with the extension stripped out.
 * @original strip_file_extension
 */
static function stripExtension($filename, &$extension = null)
{
    $file_array = explode(".", $filename);

    $extension = $file_array[count($file_array) - 1];

    unset($file_array[count($file_array) - 1]);

    $filename = implode("", $file_array);

    return $filename;
}

/**
 * Gets the mime type of a file on a remote server. If no server
 * is supplied it will try on current server.
 *
 * @param string $path The file on the current server.
 * @param string $server
 * @param int $port
 *
 * @return string Original file mime type or
 * application/octet-stream if not possible to retreive data.
 * @original get_mimetype
 */
static function getMimeType($path, $server="", $port=0)
{
    if(!$server)
        $server = $_SERVER["SERVER_NAME"];

    if(!$port)
        $port = $_SERVER["SERVER_PORT"];

    $fp = fsockopen($server, $port);

    if(!$fp)
    {
        return self::getMimeTypeLocal($path);
    }

    $header_done = false;

    $request = "GET " . $path . " HTTP/1.0\r\n";
    $request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n";
    $request .= "Host: " . $server . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    $return = '';

    fputs($fp, $request);

    $line = fgets($fp, 128);
    $header["status"] = $line;

    while(!feof($fp))
    {
        $line = fgets($fp, 128);
        if($header_done)
        {
            $content .= $line;
        }
        else
        {
            if($line == "\r\n")
            {
                $header_done = true;
            }
            else
            {
                $data = explode(": ", $line);
                $header[$data[0]] = $data[1];
            }
        }
    }

    fclose($fp);

    return $header["Content-Type"];
}

/**
 * Gets the mime type of a file stored on local server. This static function isn't
 * safe to determine user uploaded files mime type.
 *
 * @param string $filename Absolute path to the file.
 *
 * @return string Original file mime type or
 * application/octet-stream if not possible to retreive data.
 * @original get_mimetype_local
 */
static function getMimeTypeLocal($filename)
{

    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $file_parts = explode('.', $filename);

    $ext = strtolower(array_pop($file_parts));

    if(array_key_exists($ext, $mime_types))
    {
        return $mime_types[$ext];
    }
    elseif(function_exists('finfo_open') && file_exists($filename))
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    }
    else
    {
        return 'application/octet-stream';
    }
}

}
