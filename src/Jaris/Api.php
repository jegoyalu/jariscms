<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Facilities to write api pages.
 */
class Api
{

/**
 * Receives parameters: $permissions
 * @var string
 */
    const SIGNAL_API_GET_PERMISSIONS_LIST = "hook_api_get_permissions_list";

    /**
     * An array that defines the api behaviour.
     * @var array
     */
    private static $spec;

    /**
     * A list of variables to send on the response.
     * @var array
     */
    private static $response;

    /**
     * @var int
     */
    const ERROR_INVALID_KEY = 10;
    /**
     * @var int
     */
    const ERROR_INVALID_TOKEN = 20;

    /**
     * @var int
     */
    const ERROR_EXPIRED_TOKEN = 30;

    /**
     * @var int
     */
    const ERROR_REMOTE_ACCESS_DENIED = 40;

    /**
     * @var int
     */
    const ERROR_REQUIRED_ACTION = 50;

    /**
     * @var int
     */
    const ERROR_REQUIRED_PARAMETER = 60;

    /**
     * @var int
     */
    const ERROR_REQUIRED_SUBPARAMETER = 70;

    /**
     * @var int
     */
    const ERROR_INVALID_ACTION = 80;

    /**
     * @var int
     */
    const ERROR_ACTION_DENIED = 90;

    /**
     * This functions should be the first piece called when implementing a
     * api. It Does preliminar key validation and sends a token to client in order
     * to prevent ip spoofing. The token should be used on subsequent calls to
     * send api calls. A client using the api should first send the api key.
     *
     * http://site/api?key=XXXX
     *
     * The api will repond with a token in json format which expires in 5 minutes.
     *
     * {"token": "XXXX"}
     *
     * Finally, the client must make any api calls using that token.
     *
     * http://site/api?token=XXXX&action=add&value=14
     *
     * @param $spec Array that describes how the api works. Eg:
     * array(
     *     "action" => array(
     *         "description"=>"",
     *         "parameters" => array("name"=>"description")
     *         "response"=>array(),
     *     ),
     *     etc...
     * )
     */
    public static function init(array $spec=[]): void
    {
        self::$spec = $spec;

        self::$response = [];

        ApiKey::createDatabase();

        if (isset($_REQUEST["key"])) {
            $key = trim($_REQUEST["key"]);

            $data = ApiKey::getData($key);

            // Check if api key is valid
            if (!$data) {
                self::sendSystemErrorResponse(self::ERROR_INVALID_KEY);
            }

            // Check if ip validation is required
            if (trim($data["ip_host"]) != "") {
                $ip_host_list = explode(",", $data["ip_host"]);
                $valid_ip = false;

                foreach ($ip_host_list as $ip_host) {
                    $ip_host = trim($ip_host);

                    if (!filter_var($ip_host, FILTER_VALIDATE_IP)) {
                        $ip_address = gethostbyname($ip_host);

                        if ($ip_address != $ip_host) {
                            $ip_host = $ip_address;
                        } else {
                            continue;
                        }
                    }

                    if ($ip_host == $_SERVER["REMOTE_ADDR"]) {
                        $valid_ip = true;
                        break;
                    }
                }

                if (!$valid_ip) {
                    self::sendSystemErrorResponse(
                    self::ERROR_REMOTE_ACCESS_DENIED
                );
                }
            }

            // If help action was given print full api spec and exit
            if (self::getAction() == "help") {
                self::describe();
            }

            // Generate a new unique token
            $token = Users::generatePassword(128);
            $token_expires = time() + (60 * 5); // Expires in 5 minutes

            while (self::isValidToken($token) === 1) {
                $token = Users::generatePassword(128);
                $token_expires = time() + (60 * 5); // Expires in 5 minutes
            }

            // Update token
            $db = Sql::open("api_keys");
            $key_escaped = str_replace("'", "''", $key);

            $update = "update api_keys set "
            . "token='$token',"
            . "token_expires='$token_expires' "
            . "where key='$key_escaped'"
        ;

            Sql::query($update, $db);

            Sql::close($db);

            self::addResponse("token", $token);
            self::sendResponse();
        } elseif (isset($_REQUEST["token"])) {
            $token = trim($_REQUEST["token"]);

            $token_valid = self::isValidToken($token);

            if ($token_valid === -1) {
                self::sendSystemErrorResponse(self::ERROR_INVALID_TOKEN);
            } elseif ($token_valid === -2) {
                //self::sendSystemErrorResponse(self::ERROR_EXPIRED_TOKEN);
            } elseif ($token_valid === 1) {
                // Extending the token expiration time hits performance of
            // subsequent requests a bit.
            //self::extendToken($token);
            }

            if (self::getAction() == "") {
                self::sendSystemErrorResponse(self::ERROR_REQUIRED_ACTION);
            }
        } else {
            self::describe();
        }

        self::validateCall();
    }

    /**
     * Generates documentation for the api being accessed by using the api spec
     * set on Api::init().
     */
    public static function describe(): void
    {
        $system_spec = [
        "help" => [
            "description" => "Display the full api specification.",
            "parameters" => [
                "key"=>"A valid api key.",
            ],
            "parameters_required" => [
                "key"
            ]
        ]
    ];

        $spec = [];
        $spec_empty = true;

        if (
        self::$spec
        &&
        self::getAction() == "help"
        &&
        isset($_REQUEST["key"])
    ) {
            $spec = array_merge($system_spec, self::$spec);
            $spec_empty = false;
        } else {
            $spec =& $system_spec;
        }

        header('Content-Type: text/html; charset=utf-8', true);

        print "<html>";
        print "<head>";
        print "<title>Api Spec</title>";
        print "<style>"
        . "body{"
        . "max-width: 1200px;"
        . "margin: 20px auto 20px auto;"
        . "padding: 7px;"
        . "}"
        . "body *{"
        . "font-family: verdana, sans-serif;"
        . "}"
        . "p{"
        . "text-align: justify;"
        . "}"
        . "h1{"
        . "color: #265682;"
        . "}"
        . "h2{"
        . "color: #676767;"
        . "}"
        . "h3{"
        . "background-color: #747474; color: #fff; padding: 7px; display: inline-block; margin: 25px 0 10px 0;"
        . "}"
        . "table{"
        . "width: 100%;"
        . "}"
        . "table, td{"
        . "border: solid 1px #5692c9;"
        . "border-collapse: collapse;"
        . "border-spacing: 0;"
        . "vertical-align: top;"
        . "padding: 5px;"
        . "}"
        . "thead{"
        . "background-color: #5692c9;"
        . "text-align: center;"
        . "color: #fff;"
        . "font-weight: bold;"
        . "}"
        . "td.required{"
        . "text-align: center;"
        . "}"
        . "pre{"
        . "overflow: auto;"
        . "border: dashed 1px #5692c9;"
        . "padding: 15px;"
        . "background-color: #dbe7f3;"
        . "font-family: monospace;"
        . "font-size: 13px;"
        . "}"
        . "</style>"
    ;
        print "</head>";
        print "<body>";

        // List available API pages, both core and modules.
        if ($spec_empty && self::getAction() == "help" && isset($_REQUEST["key"])) {
            header("Cache-control: private");

            print "<h1>" . "List of API's" . "</h1>";

            print "<p>"
            . "Here is a list of all available api's that can be used with "
            . "the right api key permissions."
            . "</p>"
        ;

            print "<h3>Core</h3>";

            print '<table>';
            print "<thead>";
            print "<tr>";
            print "<td>" . "Title" . "</td>";
            print "<td>" . "Path" . "</td>";
            print "</tr>";
            print "</thead>";

            print "<tbody>";

            FileSystem::search(
            "system/pages/api",
            "/.*\.php$/",
            function ($page_path) {
                print "<tr>";

                $page_data = Data::parse(
                    $page_path
                );

                print "<td>"
                    . $page_data[0]["title"]
                    . "</td>"
                ;

                $uri = str_replace(
                    ["system/pages/", "-", ".php"],
                    ["", "/", ""],
                    $page_path
                );
                $url = Uri::url(
                    $uri,
                    [
                        "key" => $_REQUEST["key"],
                        "action" => "help"
                    ]
                );
                print "<td>"
                    . "<a href=\"$url\">"
                    . $uri
                    . "</a>"
                    . "</td>"
                ;

                print "</tr>";
            }
        );

            print "</tbody>";

            print "</table>";

            $modules = Modules::getInstalled();

            $modules_title = false;

            foreach ($modules as $module_name) {
                $pages = Site::dataDir() . "modules/$module_name/pages.php";

                if (!file_exists($pages)) {
                    continue;
                }

                $pages_list = Data::parse($pages);
                $pages_api = [];
                foreach ($pages_list as $pages_data) {
                    if (
                    strpos($pages_data["new_uri"], "api/") === 0
                    ||
                    strpos($pages_data["new_uri"], "/api/") !== false
                    ||
                    strpos($pages_data["new_uri"], "/api") !== false
                ) {
                        $pages_api[] = $pages_data["new_uri"];
                    }
                }

                if (count($pages_api) <= 0) {
                    continue;
                }

                if (!$modules_title) {
                    print "<h2>" . "List of Module API's" . "</h2>";

                    print "<p>"
                    . "Here is a list of all available api interfaces for "
                    . "each installed module on the system."
                    . "</p>"
                ;
                    $modules_title = true;
                }

                $module = Modules::get($module_name);

                print "<h3>".$module["name"]."</h3>";

                print '<table>';
                print "<thead>";
                print "<tr>";
                print "<td>" . "Title" . "</td>";
                print "<td>" . "Path" . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";
                foreach ($pages_api as $page) {
                    print "<tr>";

                    $page_path = Pages::getPath($page) . "/data.php";

                    $page_data = Data::parse(
                    $page_path
                );

                    print "<td>"
                    . $page_data[0]["title"]
                    . "</td>"
                ;

                    $url = Uri::url(
                    $page,
                    [
                        "key" => $_REQUEST["key"],
                        "action" => "help"
                    ]
                );
                    print "<td>"
                    . "<a href=\"$url\">"
                    . $page
                    . "</a>"
                    . "</td>"
                ;

                    print "</tr>";
                }
                print "</tbody>";
                print "</table>";
            }

            print "</body>";
            print "</html>";
            exit;
        }

        if (self::getAction() == "help" && isset($_REQUEST["key"])) {
            print "<h1>" . "Api Specification" . "</h1>";

            print "<p>";
            print "Brief description explaining the api spec.";
            print "</p>";

            print "<h2>" . "Authentication" . "</h2>";

            print "<p>";
            print "In order to use the api you must first make a key request."
            . " "
            . "The api will response in json format with a valid token."
        ;
            print "</p>";

            print "<h4>" . "Example:" . "</h4>";

            print "<pre>";
            print "token_json = do_request('".Uri::url(Uri::get(), ["key"=>"XXXX"])."');\n";
            print "token_data = json_decode(token_json);\n";
            print "token = token_data['token'];";
            print "\n\n";
            print "// The json response would look something like:\n";
            print "// {'token':'XXXX'}";
            print "</pre>";

            print "<p>";
            print "The token will be valid for 5 minutes after authentication."
            . " "
            . "Each subsequent api call must be made with the token."
        ;
            print "</p>";

            $url_token = Uri::url(Uri::get(), ["token"=>"XXXX", "action"=>"update", "value"=>1]);

            print "<h4>" . "Example:" . "</h4>";

            print "<pre>";
            print "response = do_request('".$url_token."');";
            print "</pre>";

            print "<p>";
            print "After five minutes you will need to re-authenticate using your api key."
            . " "
            . "The api will return an error message in any case of failure."
            . " "
            . "Make sure to check for error reponse codes and messages on your code."
        ;
            print "</p>";

            print "<h4>" . "Example:" . "</h4>";

            print "<pre>";
            print "response = json_decode(do_request('".$url_token."'));\n\n";
            print "if(isset(response['error']){\n";
            print "    print response['error']['code'];\n";
            print "    print response['error']['message'];\n";
            print "}\n";
            print "\n\n";
            print "// The json error response would look something like:\n";
            print "// {'error':{ 'code':10, 'message':'Invalid api key.' }}";
            print "</pre>";

            print "<h2>" . "Actions" . "</h2>";

            print "<p>";
            print "An api may support various set of actions."
            . " "
            . "Each action may contain its own set of parameters."
            . " "
            . "An action is performed by setting the action parameter."
        ;

            print "<h4>" . "Example:" . "</h4>";

            print "<pre>";
            print "// In this example the action is 'update' and the parameter\n";
            print "// 'value' is being given to the action and set to 1\n";
            print "response = json_decode(do_request('".$url_token."'));";
            print "</pre>";
        } else {
            print "<h1>" . "Partial Api Specification" . "</h1>";

            print "<p>";
            print "Provide your api key to display the full api spec.";
            print "</p>";

            print '<form action="'.Uri::url(Uri::get()).'" method="POST">';
            print '<input type="hidden" name="action" value="help" />';
            print '<input type="text" name="key" placeholder="'."paste your api key...".'" />';
            print '<input type="submit" value="'."View".'" />';
            print '</form>';
        }

        print "<h2>" . "List of available actions" . "</h2>";

        print "<p>";
        print "What follows is a description of the actions you can perform."
        . " "
        . "Also the parameters for each action."
    ;
        print "</p>";

        $permissions_list = self::getPermissionsList();

        foreach ($spec as $action=>$action_spec) {
            print "<h3>" . $action . "</h3>";

            // Display description and required permissions
            if (isset($action_spec["description"]) || isset($action_spec["permissions"])) {
                print "<p>";
            }

            if (isset($action_spec["description"])) {
                print $action_spec["description"];
            }

            if (isset($action_spec["permissions"])) {
                print " <strong>Required permissions:</strong> ";

                if (!is_array($action_spec["permissions"])) {
                    print $permissions_list[$action_spec["permissions"]];

                    foreach ($permissions_list as $section=>$permissions) {
                        if (isset($permissions[$action_spec["permissions"]])) {
                            print $permissions[$action_spec["permissions"]];
                            break;
                        }
                    }
                } else {
                    $permissions_html = "";
                    foreach ($action_spec["permissions"] as $permission) {
                        foreach ($permissions_list as $section=>$permissions) {
                            if (isset($permissions[$permission])) {
                                $permissions_html .= $permissions[$permission] . ", ";
                                break;
                            }
                        }
                    }
                    $permissions_html = rtrim($permissions_html, ", ");

                    print $permissions_html;
                }
            }

            if (isset($action_spec["description"]) || isset($action_spec["permissions"])) {
                print "</p>";
            }

            // Display parameters
            if (isset($action_spec["parameters"])) {
                print "<h4>" . "Parameters" . "</h4>";

                print '<table>';
                print "<thead>";
                print "<tr>";
                print "<td>" . "Parameter" . "</td>";
                print "<td>" . "Description" . "</td>";
                print "<td>" . "Required" . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";

                foreach ($action_spec["parameters"] as $parameter=>$parameter_data) {
                    print "<tr>";

                    print "<td>" . $parameter . "</td>";

                    if (!is_array($parameter_data)) {
                        print "<td>" . $parameter_data . "</td>";
                    } else {
                        print "<td>";
                        print "<p>" . $parameter_data["description"] . "</p>";

                        if (isset($parameter_data["elements"])) {
                            print "<strong>" . "Structure" . "</strong><hr />";

                            print "<pre>";
                            print json_encode(
                            $parameter_data["elements"],
                            JSON_PRETTY_PRINT
                        );
                            print "</pre>";
                        }

                        if (isset($parameter_data["elements_required"])) {
                            print '<table>';
                            print "<thead>";
                            print "<tr>";
                            print "<td>" . "Parameter" . "</td>";
                            print "<td>" . "Required" . "</td>";
                            print "</tr>";
                            print "</thead>";

                            print "<tbody>";

                            foreach ($parameter_data["elements"] as $element=>$element_description) {
                                print "<tr>";

                                print "<td>" . $element . "</td>";

                                print '<td class="required">';
                                if (in_array($element, $parameter_data["elements_required"])) {
                                    print "yes";
                                } else {
                                    $param_found = false;

                                    foreach ($parameter_data["elements_required"] as $params_list) {
                                        $params_list = array_map(
                                        'trim',
                                        explode(",", $params_list)
                                    );

                                        if (in_array($element, $params_list)) {
                                            $param_found = true;
                                            break;
                                        }
                                    }

                                    if ($param_found) {
                                        print "yes ";

                                        unset($params_list[array_search($element, $params_list)]);

                                        print "(if not set ";
                                        print implode(" or ", $params_list);
                                        print ")";
                                    } else {
                                        print "no";
                                    }
                                }

                                print "</td>";

                                print "</tr>";
                            }

                            print "</tbody>";
                            print "</table>";
                        }

                        print "</td>";
                    }

                    if (isset($action_spec["parameters_required"])) {
                        print '<td class="required">';
                        if (in_array($parameter, $action_spec["parameters_required"])) {
                            print "yes";
                        } else {
                            $param_found = false;

                            foreach ($action_spec["parameters_required"] as $params_list) {
                                $params_list = array_map(
                                'trim',
                                explode(",", $params_list)
                            );

                                if (in_array($parameter, $params_list)) {
                                    $param_found = true;
                                    break;
                                }
                            }

                            if ($param_found) {
                                print "yes ";

                                unset($params_list[array_search($parameter, $params_list)]);

                                print "(if not set ";
                                print implode(" or ", $params_list);
                                print ")";
                            } else {
                                print "no";
                            }
                        }

                        print "</td>";
                    } else {
                        print '<td class="required">' . "no" . "</td>";
                    }

                    print "</tr>";
                }

                print "</tbody>";

                print "</table>";
            }

            // Display response
            if (isset($action_spec["response"])) {
                print "<h4>" . "Response" . "</h4>";

                print '<table>';
                print "<thead>";
                print "<tr>";
                print "<td>" . "Parameter" . "</td>";
                print "<td>" . "Description" . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";

                foreach ($action_spec["response"] as $parameter=>$parameter_data) {
                    print "<tr>";

                    print "<td>" . $parameter . "</td>";

                    if (!is_array($parameter_data)) {
                        print "<td>" . $parameter_data . "</td>";
                    } else {
                        print "<td>";
                        print "<p>" . $parameter_data["description"] . "</p>";

                        if (isset($parameter_data["elements"])) {
                            print "<strong>" . "Structure" . "</strong><hr />";

                            print "<pre>";
                            print json_encode(
                            $parameter_data["elements"],
                            JSON_PRETTY_PRINT
                        );
                            print "</pre>";
                        }

                        print "</td>";
                    }

                    print "</tr>";
                }

                print "</tbody>";

                print "</table>";
            }

            // Display error messages returned by action
            if (isset($action_spec["errors"])) {
                print "<h4>" . "Errors" . "</h4>";

                print '<table>';
                print "<thead>";
                print "<tr>";
                print "<td>" . "Code" . "</td>";
                print "<td>" . "Message" . "</td>";
                print "</tr>";
                print "</thead>";

                print "<tbody>";

                foreach ($action_spec["errors"] as $error_code=>$error_message) {
                    print "<tr>";

                    print "<td>" . $error_code . "</td>";
                    print "<td>" . $error_message . "</td>";

                    print "</tr>";
                }

                print "</tbody>";

                print "</table>";
            }
        }

        print "</body>";
        print "</html>";

        exit;
    }

    /**
     * Validates an api call by verifying that all required parameters are
     * set for the current action.
     */
    public static function validateCall(): void
    {
        $action = self::getAction();

        if (!isset(self::$spec[$action])) {
            self::sendSystemErrorResponse(self::ERROR_INVALID_ACTION);
        }

        $action_spec = self::$spec[$action];

        if (isset($action_spec["permissions"])) {
            $key = self::getCurrentKey();

            if (!ApiKey::hasPermission($key, $action_spec["permissions"])) {
                self::sendSystemErrorResponse(self::ERROR_ACTION_DENIED);
            }
        }

        if (!isset($action_spec["parameters"])) {
            return;
        }

        foreach ($action_spec["parameters"] as $parameter=>$parameter_data) {
            $param_required = false;

            if (isset($action_spec["parameters_required"])) {
                if (
                in_array($parameter, $action_spec["parameters_required"])
            ) {
                    if (!isset($_REQUEST[$parameter])) {
                        $param_required = true;
                    }
                } else {
                    // Check if parameter is required only if other parameter
                    // is missing.

                    $param_found = false;

                    foreach ($action_spec["parameters_required"] as $params_list) {
                        $params_list = array_map(
                        'trim',
                        explode(",", $params_list)
                    );

                        if (in_array($parameter, $params_list)) {
                            $param_found = true;
                            break;
                        }
                    }

                    if ($param_found) {
                        unset($params_list[array_search($parameter, $params_list)]);

                        $param_optional_set = false;

                        foreach ($params_list as $param_optional) {
                            if (isset($_REQUEST[$param_optional])) {
                                $param_optional_set = true;
                                break;
                            }
                        }

                        if (!$param_optional_set && !isset($_REQUEST[$parameter])) {
                            $param_required = true;
                        }
                    }
                }
            }

            if ($param_required) {
                self::sendSystemErrorResponse(self::ERROR_REQUIRED_PARAMETER);
            }
        }
    }

    /**
     * Gets the current action executed by the request.
     *
     * @return string
     */
    public static function getAction(): string
    {
        if (isset($_REQUEST["action"])) {
            $action = trim($_REQUEST["action"]);

            if ($action != "") {
                return $action;
            }
        }

        return "";
    }

    /**
     * Gets the current api key. This public static function should
     * be used after a successfull call to Api::init().
     *
     * @staticvar null $api_current_key
     *
     * @return string
     */
    public static function getCurrentKey(): string
    {
        static $api_current_key = "";

        if ($api_current_key == "") {
            $key_data = ApiKey::getDataByToken(trim($_REQUEST["token"]));

            $api_current_key = $key_data["key"];
        }

        return $api_current_key;
    }

    /**
     * Adds a parameter which is send on the api response in json format.
     *
     * @param string $parameter Name of parameter
     * @param mixed $value Value of parameter.
     */
    public static function addResponse(string $parameter, $value): void
    {
        self::$response[$parameter] = $value;
    }

    /**
     * Finalizes the client request and sends a response of all parameters
     * added with Api::addResponse() in json_format.
     */
    public static function sendResponse(): void
    {
        global $time_start;

        self::$response["reponse_time"] = ceil(
        (microtime(true) - $time_start) * 1000
    ) . "ms";

        print json_encode(self::$response);

        exit;
    }

    /**
     * Sends an error response and finalize the request.
     *
     * @param int $code The code should be a number greater than 1000
     * @param string $message
     * @param int $http_status A valid http header status code
     */
    public static function sendErrorResponse(
    int $code,
    string $message,
    int $http_status=400
): void {
        global $time_start;

        Site::setHTTPStatus($http_status);

        $error = [
        "error" => [
            "code" => $code,
            "message" => $message
        ],
        "response_time" => ceil(
            (microtime(true) - $time_start) * 1000
        ) . "ms"
    ];

        $error = array_merge($error, self::$response);

        print json_encode($error);

        exit;
    }

    /**
     * Sends one of the predefined system errors and finalizes the request.
     *
     * @param int $code
     */
    public static function sendSystemErrorResponse(int $code): void
    {
        switch ($code) {
        case 10:
            self::sendErrorResponse(
                10,
                "Invalid api key.",
                401
            );
            // no break
        case 20:
            self::sendErrorResponse(
                20,
                "Invalid token.",
                401
            );
            // no break
        case 30:
            self::sendErrorResponse(
                30,
                "Token has expired.",
                401
            );
            // no break
        case 40:
            self::sendErrorResponse(
                40,
                "Access denied to remote address.",
                403
            );
            // no break
        case 50:
            self::sendErrorResponse(
                50,
                "No action provided.",
                400
            );
            // no break
        case 60:
            self::sendErrorResponse(
                60,
                "Required parameter is missing.",
                400
            );
            // no break
        case 70:
            self::sendErrorResponse(
                70,
                "A required parameter subelement is missing.",
                400
            );
            // no break
        case 80:
            self::sendErrorResponse(
                80,
                "Invalid action provided.",
                400
            );
            // no break
        case 90:
            self::sendErrorResponse(
                90,
                "Action not allowed for the api key.",
                403
            );
            // no break
        default:
            self::sendErrorResponse(
                1000,
                "Unknown error."
            );
    }
    }

    /**
     * Check if a token is valid.
     *
     * @param string $token
     *
     * @return int Returns 1 if valid, -2 if expired and -1 if invalid.
     */
    public static function isValidToken(string $token): int
    {
        $data = ApiKey::getDataByToken($token);

        if ($data) {
            if ($data["token_expires"] < time()) {
                return -2;
            } else {
                return 1;
            }
        }

        return -1;
    }

    /**
     * Extends the expiration time of a token.
     *
     * @param string $token
     */
    public static function extendToken(string $token): void
    {
        $db = Sql::open("api_keys");

        $token = str_replace("'", "''", $token);
        $token_expires = time() + (60 * 5);

        $update = "update api_keys set "
        . "token_expires='$token_expires' "
        . "where token='$token'"
    ;

        Sql::query($update, $db);

        Sql::close($db);
    }

    /**
     * Gzips and base64 encode any given data.
     * @param mixed $data
     * @return mixed
     */
    public static function compressData($data)
    {
        return base64_encode(gzcompress($data));
    }

    /**
     * Try to ungzip a base64 encoded given data, if fails return original data.
     * @param mixed $data
     * @return mixed
     */
    public static function uncompressData($data)
    {
        if ($new_data = base64_decode($data)) {
            if ($new_data = gzuncompress($new_data)) {
                return $new_data;
            }
        }

        return $data;
    }

    /**
     * Decodes a json encoded request parameter into an associative array.
     * @param string $name Name of request parameter.
     * @return array|null Null if failed.
     */
    public static function decodeParam($name)
    {
        return json_decode($_REQUEST[$name], true);
    }

    /**
     * Decodes a given json string into an associative array.
     * @param mixed $data
     * @return
     */
    public static function decodeData($data)
    {
        return json_decode($data, true);
    }

    /**
     * Get the list of permissions available for api access.
     *
     * @return array
     */
    public static function getPermissionsList(): array
    {
        static $permissions = [];

        $permissions[t("Core")] = [
        // Page permissions
        "add_page_core" => t("Add Page"),
        "edit_page_core" => t("Edit Page"),
        "delete_page_core" => t("Delete Page"),
        "get_page_core" => t("Get Page"),

        // Page images permissions
        "add_page_image_core" => t("Add Page Images"),
        "edit_page_image_core" => t("Edit Page Images"),
        "delete_page_image_core" => t("Delete Page Image"),
        "get_page_image_core" => t("Get Page Images"),

        // Type permissions
        "add_type_core" => t("Add Content Type"),
        "edit_type_core" => t("Edit Content Type"),
        "delete_type_core" => t("Delete Content Type"),
        "get_type_core" => t("Get Content Type"),

        // Category permissions
        "add_category_core" => t("Add Category"),
        "edit_category_core" => t("Edit Category"),
        "delete_category_core" => t("Delete Category"),
        "get_category_core" => t("Get Category"),

        // Subcategory permissions
        "add_subcategory_core" => t("Add Subcategory"),
        "edit_subcategory_core" => t("Edit Subcategory"),
        "delete_subcategory_core" => t("Delete Subcategory"),
        "get_subcategory_core" => t("Get Subcategory"),

         // User permissions
        "add_user_core" => t("Add User"),
        "edit_user_core" => t("Edit User"),
        "delete_user_core" => t("Delete User"),
        "get_user_core" => t("Get User"),
    ];

        //Call api_get_permissions_list hook before returning the permissions
        Modules::hook("hook_api_get_permissions_list", $permissions);

        ksort($permissions);

        return $permissions;
    }
}
