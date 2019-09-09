<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module uninstall file
 *
 * Stores the uninstall script for polls module.
 */

function polls_uninstall()
{
    //Delete recent polls block
    Jaris\Blocks::deleteByField("poll_block", "1");
}
