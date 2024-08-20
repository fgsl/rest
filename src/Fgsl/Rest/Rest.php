<?php
/**
 * Fgsl REST
 *
 * @author    Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @link      https://github.com/fgsl/rest for the canonical source repository
 * @copyright Copyright (c) 2023 FGSL (http://www.fgsl.eti.br)
 * @license   https://www.gnu.org/licenses/agpl.txt GNU AFFERO GENERAL PUBLIC LICENSE
 */
namespace Fgsl\Rest;

use Fgsl\Http\Http;
/**
* Class with methods to make HTTP REST requests
**/
class Rest {
    public array $requestErrors = [];
    public int $requestCounter = 0;
    protected string $baseUrl = '';
    public array $dataErrors = [];
    public array $methodErrors = [];
    protected $method = 'GET';

    /**
     * Method to make a HTTP GET request
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer | array
     * @param $data array optional way to send arguments
     * @param $verbose boolean
     */
    public function doGet(array $headers,string $url,$expectedCode,array $data = [],bool $verbose=false){
        if ($verbose) { echo str_repeat('=',80) . "\n"; }
        if (count($data) > 0){
            $url .= '?';
            foreach($data as $key => $value){
                $url .= "$key=$value&";
            }
            $url = substr($url,0,-1);
        }
        $data = [];
        $response = $this->tryRequest($url,$headers,$data, $verbose);

        $this->assertResponse($expectedCode,$response,$verbose,$data);
        return $response;
    }

    /**
     * Method to make a HTTP POST request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer | array
     * @param $verbose boolean
     */
    public function doPost(array $data,array $headers,string $url,$expectedCode,bool $verbose=false) {
        $json = false;
        if ($verbose) { echo str_repeat('=', 80) . "\n"; }
        $fields = '';
        foreach($data as $key => $value){
            if (is_object($value)){
                $json = true;
                $value = json_encode($value);
            }
            $fields .= "$key=$value&";
        }
        $fields = substr($fields,0,-1);

        $data = [ CURLOPT_POST => true, CURLOPT_POSTFIELDS => $fields ];
        if ($json){
            $data[CURLOPT_HTTPHEADER] = ['Content-Type:application/json'];
        }

        $response = $this->tryRequest($url,$headers,$data, $verbose);

        $this->assertResponse($expectedCode,$response,$verbose,$data);
        
        return $response;
    }

    /**
     * Method to make a HTTP DELETE request
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer | array
     * @param $data array
     * @param $verbose boolean
     */
    public function doDelete(array $headers,string $url,$expectedCode,array $data = [],bool $verbose=false){
        if ($verbose) { echo str_repeat('=',80) . "\n"; }
        if (count($data) > 0){
            $url .= '?';
            foreach($data as $key => $value){
                $url .= "$key=$value&";
            }
            $url = substr($url,0,-1);
        }

        $data = [ CURLOPT_CUSTOMREQUEST => 'DELETE'];

        $response = $this->tryRequest($url,$headers,$data, $verbose,$data);

        $this->assertResponse($expectedCode,$response,$verbose,$data);

        return $response;
    }

    /**
     * Method to make a HTTP PATCH request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer | array
     * @param $verbose boolean
     */
    public function doPatch(array $data,array $headers,string $url,$expectedCode, bool $verbose=false) {
        $json = false;
        if ($verbose) { echo str_repeat('=', 80) . "\n"; }
        $fields = '';
        foreach($data as $key => $value){
            if (is_object($value)){
                $json = true;
                $value = json_encode($value);
            }
            $fields .= "$key=$value&";
        }
        $fields = substr($fields,0,-1);

        $data = [ CURLOPT_CUSTOMREQUEST => 'PATCH',CURLOPT_POSTFIELDS => $fields ];

        $response = $this->tryRequest($url,$headers,$data, $verbose);

        $this->assertResponse($expectedCode,$response,$verbose,$data);

        return $response;
    }

    /**
     * Method to make a HTTP PUT request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer | array
     * @param $verbose boolean
     */
    public function doPut(array $data,array $headers,string $url,$expectedCode, bool $verbose=false) {
        if ($verbose) { echo str_repeat('=', 80) . "\n"; }
        $fields = '';
        foreach($data as $key => $value){
            $fields .= "$key=$value&";
        }
        $fields = substr($fields,0,-1);

        $data = [ CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $fields ];

        $response = $this->tryRequest($url,$headers,$data, $verbose);

        $this->assertResponse($expectedCode,$response,$verbose,$data);
        
        return $response;
    }


    /**
     * @param $url string
     * @param $headers array
     * @param $data array
     * @param $verbose boolean
     */
    protected function tryRequest(string $url,array $headers,array $data,bool $verbose): string
    {
        $this->setMethod($data);

        try {
            $end = strpos($url,'?') === false ? strlen($url) : strpos($url,'?');
            $this->baseUrl = substr($url,0,$end);
            $this->requestCounter++;
            if ($verbose) { echo "Requesting {$this->baseUrl} via HTTP {$this->method}\n"; }
            $response = Http::curl($url, $headers, true, $data);
        }
        catch (\Exception $error) {
            if ($verbose) { echo "Error!{$error->getMessage()}\n"; }
            echo "method {$this->method} url $url baseUrl $url\n";
            $this->requestErrors[$this->baseUrl] = Http::getLastResponseCode();
            $this->methodErrors[$this->baseUrl] = $this->method;
            $this->dataErrors[$this->baseUrl] = $this->printData($data);
            return '';
        }
        return $response;
    }

    /**
     * @param $expectedCode integer | string | array
     * @param $response string
     * @param $verbose boolean
     * @param $data array
     */
    protected function assertResponse($expectedCode,string $response,bool $verbose,array $data)
    {

        if ($this->isResponseCodeExpectable($expectedCode)) {
            if ($verbose) { echo "Response Status OK for {$this->baseUrl}\n"; }
        }
        else {
            if ($verbose) { echo "Expected $expectedCode Received " . Http::getLastResponseCode() . "\n"; }
            if ($verbose) { echo isset($response) ? "$response\n" : ''; }
            $this->requestErrors[$this->baseUrl] = Http::getLastResponseCode();
            $this->dataErrors[$this->baseUrl] = $this->printData($data);
            $this->methodErrors[$this->baseUrl] = $this->method;
        }
        if ($verbose) { echo str_repeat('=', 80) . "\n"; }
    }

    protected function isResponseCodeExpectable($expectedCode)
    {
        if (is_array($expectedCode)){
            foreach($expectedCode as $code){
                if (Http::getLastResponseCode() == $code) {
                    return true;
                }
            }
            return false;
        }
        return Http::getLastResponseCode() == $expectedCode;
    }

    private function setMethod(array $data)
    {
        $this->method = 'GET';
        if (isset($data[CURLOPT_POST])){
            $this->method = 'POST';
        }
        if (isset($data[CURLOPT_CUSTOMREQUEST])){
            $this->method = $data[CURLOPT_CUSTOMREQUEST];
        }
    }

    private function printData(array $data)
    {
        $text = "\n";
        foreach ($data as $key => $value){
            if (is_object($value)){
                $value = json_encode($value);
            }
            if (is_array($value)){
                $value = implode(',',$value);
            }
            $text .= "$key: $value\n";
        }
        return $text;
    }
}
