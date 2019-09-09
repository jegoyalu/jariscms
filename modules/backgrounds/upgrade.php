<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module upgrade file
 *
 * Stores the upgrade script for backgrounds module.
 */

function backgrounds_upgrade()
{
    if (is_dir("files/backgrounds")) {
        $target = rtrim(Jaris\Files::getDir("backgrounds"), "/");

        if (!is_dir($target)) {
            Jaris\FileSystem::makeDir($target, 0755, true);
        }

        Jaris\FileSystem::recursiveMoveDir(
            "files/backgrounds",
            $target
        );
    }
}
