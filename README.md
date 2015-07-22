# Web Request
[![Build Status](https://travis-ci.org/byjg/webrequest.svg?branch=master)](https://travis-ci.org/byjg/webrequest)
[![Build Status](https://drone.io/github.com/byjg/webrequest/status.png)](https://drone.io/github.com/byjg/webrequest/latest)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7cfbd581-fdb6-405d-be0a-afee0f70d30c/mini.png)](https://insight.sensiolabs.com/projects/7cfbd581-fdb6-405d-be0a-afee0f70d30c)

## Description

A lightweight and highly customized CURL wrapper for making RESt calls and a wrapper for call dynamically SOAP requests.
Just one class and no dependencies. 

## Examples

### Basic Usage

```php
$webRequest = new WebRequest('http://www.example.com/page');
$result = $webRequest->get();
//$result = $webRequest->post();
//$result = $webRequest->delete();
//$result = $webRequest->put();
```

### Passing arguments

```php
$webRequest = new WebRequest('http://www.example.com/page');
$result = $webRequest->get(['param'=>'value']);
//$result = $webRequest->post(['param'=>'value']);
//$result = $webRequest->delete(['param'=>'value']);
//$result = $webRequest->put(['param'=>'value']);
```

### Passing a string payload (JSON)

```php
$webRequest = new WebRequest('http://www.example.com/page');
$result = $webRequest->postPayload('{teste: "value"}', 'application/json');
//$result = $webRequest->putPayload('{teste: "value"}', 'application/json');
//$result = $webRequest->deletePayload('{teste: "value"}', 'application/json');
```

### Setting Custom CURL PARAMETER

```php
$webRequest = new WebRequest('http://www.example.com/page');
$webRequest->setCurlOption(CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
$result = $webRequest->get();
```

### Calling Soap Classes

```php
$webRequest = new WebRequest('http://www.example.com/soap');
$resutl = $webRequest->soapCall('soapMethod', ['arg1' => 'value']);
```


## Install

Just type: `composer install "byjg/webrequest=1.0.0"`

## Running Tests

### Starting the server

```php
cd tests
php -S localhost:8080 -t server & 
```

**Note:** It is more assertive create a webserver with the server folder instead to use the PHP built-in webserver.

### Running the integration tests

```php
cd tests
phpunit --bootstrap bootstrap.php src/WebRequestTest.php 
```

### Stopping the server

```php
killall -9 php
```
