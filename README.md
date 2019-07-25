# Request
A simple in-house PHP cURL wrapper.

## Installation
```bash
composer require osenco/request
```

### Usage
```php
use Osen\Request;

$get    = Request::get($url, array('key1' => 'val1', 'key2' => 'val2'));
$post   = Request::post($url, array('key1' => 'val1', 'key2' => 'val2'));
$put    = Request::put($url, array('key1' => 'val1', 'key2' => 'val2'));
$delete = Request::delete($url, array('key1' => 'val1', 'key2' => 'val2'));
```

#### Request Headers
```php
$get    = Request::headers(array('key' => 'val'))->get($url, array('key1' => 'val1', 'key2' => 'val2'));
```

### Response
A normal CURL request will return the headers and the body in one response string. This class parses the two and places them into separate properties.

```php
$response = $curl->get('google.com');
echo $response->body; # Contains everything in the response except for the headers
print_r($response->headers); # Associative array containing the response headers
```

The Response class defines the magic `__toString()` method which will return the response body, so `$response` is the same as `$response->body`

### Further Configuration
You can configure the referer or user-agent quite easily using the chainable `referer` and `user-agent` methods.
```php
$get    = Request::headers(array('key' => 'val'))->referer('https://yoursite.tld')->user_agent('UA')->get($url, array('key1' => 'val1', 'key2' => 'val2'));
```
