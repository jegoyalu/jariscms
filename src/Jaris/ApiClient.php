<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

/*
Sample Usage:
================================================================================

    $api = new Api(
        "http://localhost/api/ecommerce/inventory",
        "somelongapikeygeneratedjaris",
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

namespace Jaris;


/**
 * Facilitates doing api calls to api's written with jariscms api framework.
 */
class ApiClient
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
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Adds multiple parameters to send on api call.
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Removes previous parameters.
     */
    public function clearParameters()
    {
        $this->parameters = array();
    }

    /**
     * Sets the action to execute on api call.
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Makes an api call by sending the action and parameters previously set.
     * @throws \Exception
     */
    public function sendRequest()
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
                    throw new \Exception(
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
    public function getRawResponse()
    {
        return $this->response;
    }

    /**
     * Gets the array of returned data by last api call.
     * @return array
     */
    public function getResponse()
    {
        return json_decode($this->GetResponseBody(), true);
    }

    /**
     * Gets raw response headers of last api call.
     * @return string
     */
    public function getResponseHeaders()
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[0];
    }

    /**
     * Gets raw response body of last api call.
     * @return string
     */
    public function getResponseBody()
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[1];
    }

    /**
     * Gets a new token in order to make api calls.
     * @throws \Exception
     */
    private function getNewToken()
    {
        $parameters = array(
            "key" => $this->api_key
        );

        $this->DoRequest($parameters);

        $response = $this->GetResponse();

        if(isset($response["error"]))
        {
            throw new \Exception(
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
     * @throws \Exception
     */
    private function doRequest($parameters)
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
            throw new \Exception(
                "Could not connect to host with error: " . $errstr,
                $errno
            );
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