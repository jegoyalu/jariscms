<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Script to manage file uploads.
 */

//Register autoloader
require 'src/Autoloader.php';
Jaris\Autoloader::register();

//Include backward compatible functions if include dir exists
if(file_exists("include/forms.php"))
{
    require 'src/DeprecatedFunctions.php';
}

//Initialize settings.
Jaris\Site::init();

//Starts the main session for the user
session_start();

//Initialize error handler
Jaris\System::initiateErrorCatchSystem();

//Check if cms is run for the first time and run the installer
Jaris\System::checkIfNotInstalled();

//Check if site status is online to continue
Jaris\Site::checkIfOffline();

if(Jaris\Forms::canUpload())
{
    error_reporting(E_ALL | E_STRICT);

    $upload_path = str_replace(
        "data.php",
        "uploads/",
        Jaris\Users::getPath(
            Jaris\Authentication::currentUser(),
            Jaris\Authentication::currentUserGroup()
        )
    );

    if(!is_dir($upload_path))
    {
        Jaris\FileSystem::makeDir($upload_path, 0755, true);
    }

    $upload_handler = new UploadHandler(
        array(
            'script_url' => Jaris\Uri::url("upload.php"),
            "upload_dir" => $upload_path,
            'delete_type' => 'POST'
        )
    );
}