# FGSL rest
Component to make HTTP requests and test RESTful APIs

## How to instance

```php
use Fgsl\Rest\Rest;

$rest = new Rest();
```
### Available methods

* **doGet**

```php
/**
     * Method to make a HTTP GET request
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer
     */
    public function doGet($headers,$url,$expectedCode, $data = [],$verbose=false)
```
* **doPost**

```php
/**
     * Method to make a HTTP POST request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer
     */
    public function doPost($data,$headers,$url,$expectedCode, $verbose=false)
```  
* **doDelete**

```php
/**
     * Method to make a HTTP DELETE request
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer
     * @param $data array
     * @param $verbose boolean
     */
    public function doDelete($headers,$url,$expectedCode, $data = [],$verbose=false)
```
* **doPatch**

```php
/**
     * Method to make a HTTP PATCH request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer
     */
    public function doPatch($data,$headers,$url,$expectedCode, $verbose=false)
```
* **doDelete**

```php
/**
     * Method to make a HTTP PATCH request
     * @param $data array
     * @param $headers array
     * @param $url string
     * @param $expectedCode string | integer
     */
    public function doPatch($data,$headers,$url,$expectedCode, $verbose=false)
```

### Examples

Below we have some examples about how to use the `Rest` class.

We have used a page of the [Brazilian National Observatory](https://www.gov.br/observatorio/pt-br) for GET method.

We have used [Reqres](https://reqres.in/) for POST, PATCH and DELETE methods. 

## Testing a HTTP GET request

```php
public function testGet()
    {
        $rest = new Rest();
        $this->assertTrue(is_object($rest));
        
        @$response = $rest->doGet([],'http://www.horalegalbrasil.mct.on.br/SincronismoPublico.html',200);
        
        $this->assertStringContainsString('Sincronismo', $response);

        @$response = $rest->doGet([],'http://www.horalegalbrasil.mct.on.br/SincronismoPublico.html',500);
        
        $this->assertEquals(1,count($rest->requestErrors));

        $this->assertEquals(2,$rest->requestCounter);
    }
```

## Testing a HTTP POST request

```php
public function testPost()
    {
        $rest = new Rest();

        $data = [
            'name' => 'morpheus',
            'job' => 'leader'
        ];
        
        @$response = $rest->doPost($data, [],'https://reqres.in/api/users',201);
        
        $this->assertStringContainsString('createdAt', $response);
    }
```

## Testing a HTTP PATCH request

```php
public function testPatch()
    {
        $rest = new Rest();

        $data = [
            'name' => 'morpheus',
            'job' => 'zion resident'
        ];
        
        @$response = $rest->doPatch($data, [], 'https://reqres.in/api/users/2', 200);
        
        $this->assertStringContainsString('updatedAt', $response);
    }
```
## Testing a HTTP DELETE request

```php
public function testDelete()
    {
        $rest = new Rest();
        
        @$response = $rest->doDelete([],'https://reqres.in/api/users/2',204);

        $this->assertEquals(0,count($rest->requestErrors));
    }
}
```
