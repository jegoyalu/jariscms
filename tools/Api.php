<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the LICENSE.txt file for version and details or visit
 * https://opensource.org/licenses/GPL-3.0.
 *
 * Class to facilitate api calls written with jariscms api framework.
 */

/*
Sample Usage:
================================================================================

$api = new Api(
    "http://localhost/api/ecommerce/inventory",
    "1ZGzJXlBLxOBUep7yThhFSNspRznu1OlKJ3M0rKwM35xPFjsC1XUTSlMMWX11yK8",
    false
);

$api->SetAction("update_single");

$api->AddParameters(
    array(
        "uri"=>"product/test",
        "price" => "80.85"
    )
);

$api->SendRequest();

print $api->GetResponseBody();

$api->AddParameters(
    array(
        "item_number"=>"AA1245",
        "price" => "80.85"
    )
);

$api->SendRequest();

print $api->GetResponseBody();

================================================================================
*/


/**
 * Faciliates the usage of jariscms api.
 */
class Api
{
    /**
     * Full url of the api.
     * @var string
     */
    public $api_url;

    /**
     * A valid api key.
     * @var string
     */
    public $api_key;

    /**
     * Indicates if api requests should be made using a secure connection.
     * @var bool
     */
    public $enable_ssl;

    /**
     * List of parameters to send on request.
     * @var array
     */
    public $parameters;

    /**
     * Raw response of last api call.
     * @see SendRequest()
     * @var string
     */
    public $response;

    /**
     * A token used to be able to process api calls.
     * @var string
     */
    public $token;

    /**
     * Action to execute on api call.
     * @var string
     */
    public $action;

    /**
     * Constructor.
     * @param string $api_url
     * @param string $api_key
     * @param bool $enable_ssl
     */
    public function __construct($api_url, $api_key, $enable_ssl=true)
    {
        $this->api_url = $api_url;
        $this->api_key = $api_key;
        $this->enable_ssl = $enable_ssl;
        $this->parameters = array();
        $this->token = "";
    }

    /**
     * Add a single parameter to send on api call.
     * @param string $name
     * @param string $value
     */
    public function AddParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Adds multiple parameters to send on api call.
     * @param array $parameters
     */
    public function AddParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Removes previous parameters.
     */
    public function ClearParameters()
    {
        $this->parameters = array();
    }

    /**
     * Sets the action to execute on api call.
     * @param string $action
     */
    public function SetAction($action)
    {
        $this->action = $action;
    }

    /**
     * Makes an api call by sending the action and parameters previously set.
     * @throws Exception
     */
    public function SendRequest()
    {
        if($this->token == "")
        {
            $this->GetNewToken();
        }

        $parameters = array(
            "token" => $this->token,
            "action" => $this->action
        );

        $parameters = array_merge($parameters, $this->parameters);

        $this->DoRequest($parameters);

        $response = $this->GetResponse();

        if(isset($response["error"]))
        {
            switch($response["error"]["code"])
            {
                case "20":
                case "30":
                    $this->GetNewToken();
                    $this->SendRequest();
                    break;

                default:
                    throw new Exception(
                        $response["error"]["message"],
                        $response["error"]["code"]
                    );
            }
        }
    }

    /**
     * Gets the raw response of last api call.
     * @return string
     */
    public function GetRawResponse()
    {
        return $this->response;
    }

    /**
     * Gets the array of returned data by last api call.
     * @return array
     */
    public function GetResponse()
    {
        return json_decode($this->GetResponseBody(), true);
    }

    /**
     * Gets raw response headers of last api call.
     * @return string
     */
    public function GetResponseHeaders()
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[0];
    }

    /**
     * Gets raw response body of last api call.
     * @return string
     */
    public function GetResponseBody()
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[1];
    }

    /**
     * Gets a new token in order to make api calls.
     * @throws Exception
     */
    private function GetNewToken()
    {
        $parameters = array(
            "key" => $this->api_key
        );

        $this->DoRequest($parameters);

        $response = $this->GetResponse();

        if(isset($response["error"]))
        {
            throw new Exception(
                $response["error"]["message"],
                $response["error"]["code"]
            );
        }
        else
        {
            $this->token = $response["token"];
        }
    }

    /**
     * Make a post request.
     * @param array $parameters
     * @return string
     * @throws Exception
     */
    private function DoRequest($parameters)
    {
        $url_info = parse_url($this->api_url);

        foreach($parameters as $key=>$value)
        {
            if(is_array($value))
                $value = json_encode($value);

            $values[] = "$key=".urlencode($value);
        }

        $data_string = implode("&",$values);

        if(!$this->enable_ssl)
        {
            $url_info["port"] = 80;
        }
        else
        {
            $url_info["port"] = 443;
        }

        $request = "";
        $request .= "POST ".$url_info["path"]." HTTP/1.1\r\n";
        $request .= "Host: ".$url_info["host"]."\r\n";
        $request .= "Content-type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-length: ".strlen($data_string)."\r\n";
        $request .= "Connection: close\r\n";
        $request .= "\r\n";
        $request .= $data_string."\r\n";

        $url = $this->enable_ssl ?
            "ssl://" . $url_info["host"] : $url_info["host"]
        ;

        $errno = 0;
        $errstr = "";

        $fp = fsockopen($url, $url_info["port"], $errno, $errstr, 120);

        if(!$fp)
        {
            throw new Exception("Could not connect to host.", $errstr, $errno);
        }

        fputs($fp, $request);

        $result = "";
        while(!feof($fp))
        {
            $result .= fgets($fp, 128);
        }

        fclose($fp);

        $this->response = $result;

        return $result;
    }
}