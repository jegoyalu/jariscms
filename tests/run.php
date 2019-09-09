<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 */

if (php_sapi_name() != "cli") {
    exit;
}

chdir(__DIR__ . "/../");

if (!is_dir("sites/default/data/users")) {
    print "You must first make a valid jariscms installation.";
    exit;
}

// Add testing account with administrative priviliges.
test_create_testing_account();

// Create output directory.
if (!is_dir("tests/out")) {
    mkdir("tests/out");
}

// Create output directory.
if (!is_dir("tests/out/pages_core")) {
    mkdir("tests/out/pages_core");
}

// Deletes test.log
if (file_exists("tests/out/test.log")) {
    unlink("tests/out/test.log");
}

// Run index-test.php for each system page
print "System pages test.\n";
print "==============================================\n";

file_put_contents(
    "tests/out/test.log",
    "System pages test.\n"
        . "==============================================\n",
    FILE_APPEND
);

test_search_files(
    "system/pages",
    "/.*\.php/",
    function ($path) {
        $path = str_replace(
            ["system/pages/", ".php"],
            "",
            $path
        );

        test_execute_page($path);
    }
);

// Remove testing user account
test_delete_testing_account();

// If errors save them to log file.
test_save_errors();

//////////////////////////////////////////////////////////////////////////////
// Functions used on this test script launcher,
//////////////////////////////////////////////////////////////////////////////

/**
 * Add testing account with administrative priviliges.
 */
function test_create_testing_account()
{
    if (!is_dir("sites/default/data/users/administrator/t/te/test")) {
        // Create testing user account
        mkdir("sites/default/data/users/administrator/t/te/test", 0755, true);
    }

    file_put_contents(
        "sites/default/data/users/administrator/t/te/test/data.php",
        "
<?php exit; ?>

row: 0

        field: name
                Test
        field;

        field: email
                test@localhost
        field;

        field: password
                test
        field;

        field: status
                1
        field;

        field: group
                administrator
        field;

row;
"
    );
}

/**
 * Delete testing account.
 */
function test_delete_testing_account()
{
    unlink("sites/default/data/users/administrator/t/te/test/data.php");
    rmdir("sites/default/data/users/administrator/t/te/test");
    @rmdir("sites/default/data/users/administrator/t/te");
    @rmdir("sites/default/data/users/administrator/t");
}

function test_execute_page($path)
{
    global $test_errors;

    if (!is_array($test_errors)) {
        $test_errors = [];
    }

    static $count = 1;

    $output = `php tests/index-test.php "p=$path" 2>&1`;

    $output_parts = explode("\n-HTML-\n", $output);

    $is_error = false;

    $output = "";

    if (trim($output_parts[0]) == "" && isset($output_parts[1])) {
        print "\033[32m(passed) \033[0m";
        $output .= "(passed) ";
    } elseif (trim($output_parts[0]) != "") {
        print "\033[31m(has errors) \033[0m";
        $output .= "(has errors) ";
        $is_error = true;
    } elseif (!isset($output_parts[1])) {
        print "\033[33m(no output warning) \033[0m";
        $output .= "(no output warning) ";
    }

    print "Test $count: $path";
    $output .= "Test $count: $path";

    if ($is_error) {
        $errors = explode("\n", $output_parts[0]);

        foreach ($errors as $error) {
            if (trim($error) == "") {
                continue;
            }

            if (strpos($error, "eval()") === false) {
                print "\n    " . $error;
            }

            $output .= "\n    " . $error;

            if (trim($error) != "") {
                if (!isset($test_errors[$error])) {
                    $test_errors[$error] = 1;
                } else {
                    $test_errors[$error]++;
                }
            }
        }
    }

    // Save html output.
    if (isset($output_parts[1])) {
        file_put_contents(
            "tests/out/pages_core/"
                . str_replace("/", "-", $path)
                . ".html",
            $output_parts[1]
        );
    }

    print "\n";
    $output .= "\n";

    file_put_contents("tests/out/test.log", $output, FILE_APPEND);

    $count++;
}

function test_save_errors()
{
    global $test_errors;

    if (!is_array($test_errors) || count($test_errors) < 1) {
        return;
    }

    $eval_log = false;
    $error_log = false;

    // Empty error logs
    file_put_contents("tests/out/errors_eval.log", "");
    file_put_contents("tests/out/errors.log", "");

    arsort($test_errors);

    foreach ($test_errors as $error=>$value) {
        if (strpos($error, "eval()") !== false) {
            file_put_contents(
                "tests/out/errors_eval.log",
                $value . " - " . $error . "\n",
                FILE_APPEND
            );

            $eval_log = true;
        } else {
            file_put_contents(
                "tests/out/errors.log",
                $value . " - " . $error . "\n",
                FILE_APPEND
            );

            $error_log = true;
        }
    }

    if ($error_log) {
        print "\nCheck tests/out/errors.log for a unified errors report.\n";

        file_put_contents(
            "tests/out/test.log",
            "\nCheck tests/out/errors.log for a unified errors report.\n",
            FILE_APPEND
        );
    }


    if ($eval_log) {
        print "\nCheck tests/out/errors_eval.log for a unified eval() errors report.\n";

        file_put_contents(
            "tests/out/test.log",
            "\nCheck tests/out/errors_eval.log for a unified eval() errors report.\n",
            FILE_APPEND
        );
    }
}

/**
 * Search for files in a directory.
 *
 * @param string $path Relative path to jaris installation.
 * @param string $pattern A regular expression to match the file to search.
 * @param string $callback Function to manage each file found that
 * needs one argument to accept the full path of match found and optional
 * second bool argument to indicate if search should stop. Example:
 * my_callback($full_file_path, &$stop_search).
 */
function test_search_files($path, $pattern, $callback)
{
    $directory = opendir($path);

    while (($file = readdir($directory)) !== false) {
        $full_path = $path . "/" . $file;

        if (is_file($full_path) && preg_match($pattern, $file)) {
            $stop_search = false;

            $callback($full_path, $stop_search);

            if ($stop_search) {
                return false;
            }
        } elseif ($file != "." && $file != ".." && is_dir($full_path)) {
            if (!test_search_files($full_path, $pattern, $callback)) {
                //if $stop_search was set to true
                //we stop the rest of searches
                return false;
            }
        }
    }

    closedir($directory);

    return true;
}
