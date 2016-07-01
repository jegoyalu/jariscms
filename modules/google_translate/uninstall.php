<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Jaris CMS module uninstall file
 *
 * Stores the uninstall script for ecommerce module.
 */

function google_translate_uninstall()
{
    //Remove shopping cart block
    Jaris\Blocks::deleteByField("block_name", "google_translate_block");
}

?>