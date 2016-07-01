<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Database file that stores the errors clear page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Code line of Error") ?>
    field;

    field: content
    <?php
        Jaris\Authentication::protectedPage(array("edit_settings"));

        $path = "";
        $lines = array();

        if(isset($_REQUEST["page"]))
        {
            $path = Jaris\Pages::getPath($_REQUEST["page"]) . "/data.php";

            $page_data = Jaris\Pages::get($_REQUEST["page"]);

            $lines = explode("\n", $page_data["content"]);
        }
        elseif(isset($_REQUEST["include"]))
        {
            $path = $_REQUEST["include"];

            $lines = explode("\n", file_get_contents($path));
        }
        else
        {
            Jaris\Uri::go("admin/settings/errors");
        }
    ?>
    <style>
        .error-log-code-lines
        {
            overflow: scroll;
            width: 98%;
        }

        .error-log-code-lines p
        {
            font-weight: bold;
        }

        .error-log-code-lines .line-error
        {
            background-color: #ffb9b9;
        }

        .error-log-code-lines .number
        {
            font-weight: bold;
            margin-right: 5px;
            border-right: solid 1px #d3d3d3;
            min-width: 30px;
        }

        .error-log-code-lines .number, .error-log-code-lines .code
        {
            padding: 3px;
        }
    </style>

    <div class="error-log-code-lines">
        <h2><?php print t("File:")?></h2>
        <p><?php print $path ?></p>

        <table>
        <?php foreach($lines as $pos=>$line){ ?>
        <tr class="line <?php if($pos+1 == $_REQUEST["line"]){print "line-error";} ?>">
            <td class="number">
                <a name="l<?php print $pos+1 ?>"></a>
                <?php print $pos+1 ?>
            </td>

            <td class="code">
                <?php print str_replace(" ", "&nbsp;", htmlspecialchars($line)) ?>
            </td>
        </tr>
        <?php } ?>
        </table>
    </div>
    field;

    field: is_system
        1
    field;
row;
