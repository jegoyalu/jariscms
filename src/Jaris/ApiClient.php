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
     *
     * @param string $api_url
     * @param string $api_key
     * @param bool $enable_ssl
     */
    public function __construct(
        string $api_url, string $api_key, bool $enable_ssl=true
    )
    {
        $this->api_url = $api_url;
        $this->api_key = $api_key;
        $this->enable_ssl = $enable_ssl;
        $this->parameters = array();
        $this->token = "";
    }

    /**
     * Add a single parameter to send on api call
     *
     * @param string $name
     * @param string $value
     */
    public function addParameter(string $name, string $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Adds multiple parameters to send on api call.
     *
     * @param array $parameters
     */
    public function addParameters(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Removes previous parameters.
     */
    public function clearParameters(): void
    {
        $this->parameters = array();
    }

    /**
     * Sets the action to execute on api call.
     *
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Makes an api call by sending the action and parameters previously set.
     * Removes all previously set parameters when the response is received.
     *
     * @throws \Exception
     */
    public function sendRequest(): void
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

        $this->doRequest($parameters);

        $response = array();
        try
        {
            $response = $this->getResponse();
        }
        catch(\Exception $e)
        {
            throw new \Exception(
                $e->getMessage(),
                $e->getCode()
            );
        }

        $this->clearParameters();

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
     *
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->response;
    }

    /**
     * Gets the array of returned data by last api call.
     *
     * @return array
     */
    public function getResponse(): array
    {
        $response = json_decode($this->getResponseBody(), true);

        if(!is_array($response))
        {
            throw new \Exception(
                "Internal server error",
                500
            );
        }

        return $response;
    }

    /**
     * Gets raw response headers of last api call.
     *
     * @return string
     */
    public function getResponseHeaders(): string
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[0];
    }

    /**
     * Gets raw response body of last api call.
     *
     * @return string
     */
    public function getResponseBody(): string
    {
        $response = explode("\r\n\r\n", $this->response);

        return $response[1];
    }

    /**
     * Gets a new token in order to make api calls.
     *
     * @throws \Exception
     */
    private function getNewToken()
    {
        $parameters = array(
            "key" => $this->api_key
        );

        $this->doRequest($parameters);

        $response = $this->getResponse();

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
     *
     * @param array $parameters
     *
     * @return string
     *
     * @throws \Exception
     */
    private function doRequest(array $parameters): string
    {
        $url_info = parse_url($this->api_url);

        $values = array();

        foreach($parameters as $key=>$value)
        {
            if(is_array($value))
                $value = json_encode($value);

            $values[] = "$key=".urlencode($value);
        }

        $data_string = implode("&",$values);

        if(empty($url_info["port"]))
        {
            if(!$this->enable_ssl)
            {
                $url_info["port"] = 80;
            }
            else
            {
                $url_info["port"] = 443;
            }
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
        if($new_data = base64_decode($data))
        {
            if($new_data = gzuncompress($new_data))
            {
                return $new_data;
            }
        }

        return $data;
    }
}