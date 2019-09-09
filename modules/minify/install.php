<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module install file.
 */

function minify_install()
{
    if (!is_dir(Jaris\Files::getDir("minify"))) {
        Jaris\FileSystem::makeDir(
            Jaris\Files::getDir("minify"),
            0755,
            true
        );
    }
}
