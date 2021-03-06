<?php

namespace BuckarooPayment\Components\JsonApi;

use Exception;
use BuckarooPayment\Components\JsonApi\HmacHeader;
use BuckarooPayment\Components\JsonApi\Payload\Request;
use BuckarooPayment\Components\JsonApi\Payload\Response;
use BuckarooPayment\Components\Helpers;

class Api
{
    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';

    protected $validMethods = [
        self::METHOD_GET,
        self::METHOD_POST,
    ];

    /**
     * @var BuckarooPayment\Components\JsonApi\HmacHeader
     */
    protected $hmac;

    /**
     * @var BuckarooPayment\Components\JsonApi\SoftwareHeader
     */
    protected $software;

    /**
     * @var BuckarooPayment\Components\JsonApi\CultureHeader
     */
    protected $culture;

    public function __construct(HmacHeader $hmac, SoftwareHeader $software, CultureHeader $culture)
    {
        $this->hmac = $hmac;
        $this->software = $software;
        $this->culture = $culture;
        // , $channel = "Web"
    }

    protected function initCurl()
    {
        //Initializes the curl instance
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Shopware');

        return $curl;
    }

    protected function getHeaders($curl, $url, $data, $method)
    {
        return [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            $this->hmac->getHeader($url, $data, $method),
            $this->software->getHeader(),
            $this->culture->getHeader()
        ];
    }

    protected function call($url, $method = self::METHOD_GET, Request $data = null, $responseClass = 'BuckarooPayment\Components\JsonApi\Payload\Response')
    {
        if( !in_array($method, $this->validMethods) )
        {
            throw new Exception('Invalid HTTP-Methode: ' . $method);
        }

        if( !$data ) $data = new Request;

        $curl = $this->initCurl();

        $json = json_encode($data, JSON_PRETTY_PRINT);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

        // all headers have to be set at once
        $headers = $this->getHeaders($curl, $url, $json, $method);
        $headers = array_merge($headers, $data->getHeaders());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // echo "headers: \n";
        // echo '<pre>';
        // print_r($headers);
        // print_r($data);

        // get extra info about the request
        // curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        // curl_setopt($curl, CURLINFO_PRIVATE, true);
        // curl_setopt($curl, CURLOPT_HEADER, true);

        // get response headers
        $responseHeaders = [];
        $this->getCurlHeaders($curl, $responseHeaders);

        // GET/POST
        $result = curl_exec($curl);

        $curlInfo = curl_getinfo($curl);

        // echo "responseHeaders: \n";
        // echo "<pre>";
        // echo $this->hmac->getHeader($url, $json, $method);
        // print_r($responseHeaders);
        // print_r($result);
        // exit;

        // check for curl errors
        if( $result === false )
        {
            throw new Exception('Buckaroo API curl error: ' . curl_error($curl));
        }

        $decodedResult = json_decode($result, true);

        // check for json_decode errors
        if ($decodedResult === null )
        {
            $jsonErrors = [
                JSON_ERROR_NONE => 'No error occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached',
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded',
                JSON_ERROR_SYNTAX => 'Syntaxerror',
            ];

            throw new Exception('Buckaroo API json error: ' . (!empty($jsonErrors[json_last_error()]) ? $jsonErrors[json_last_error()] : 'JSON decode error') . ": " . print_r($result, true) );
        }

        curl_close($curl);

        $response = new $responseClass($decodedResult, $curlInfo, $responseHeaders);

        return $response;
    }

    public function get($url, $responseClass = 'BuckarooPayment\Components\JsonApi\Payload\Response')
    {
        return $this->call($url, self::METHOD_GET, $responseClass);
    }

    public function post($url, Request $data = null, $responseClass = 'BuckarooPayment\Components\JsonApi\Payload\Response')
    {
        return $this->call($url, self::METHOD_POST, $data, $responseClass);
    }

    protected function getCurlHeaders($curl, &$headers)
    {
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            $length = strlen($header);
            $header = explode(':', $header, 2);

            if (count($header) < 2) // ignore invalid headers
            {
                return $length;
            }

            $name = strtolower(trim($header[0]));

            if( !array_key_exists($name, $headers) )
            {
                $headers[$name] = [ trim($header[1]) ];
            }
            else
            {
                $headers[$name][] = trim($header[1]);
            }

            return $length;
        });
    }
}
