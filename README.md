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

#### Headers
```php
$get    = Request->headers(array('key' => 'val'))->get($url, array('key1' => 'val1', 'key2' => 'val2'));
```
