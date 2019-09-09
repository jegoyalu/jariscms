<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module include file
 *
 * @note File with functions to manage attachments
 */

function church_accounting_attachments_add($file, $month, $year)
{
    $path = Jaris\Site::dataDir() . "church_accounting/$year/$month";

    if (!is_dir($path)) {
        Jaris\FileSystem::makeDir($path, 0755, true);
    }

    $destination = $path . "/" . $file["name"];

    $filename = Jaris\FileSystem::move($file["tmp_name"], $destination);

    chmod($path . "/$filename", 0755);

    return "$year/$month/" . $filename;
}

function church_accounting_attachments_delete($file)
{
    $path = Jaris\Site::dataDir() . "church_accounting/$file";

    unlink($path);
}
