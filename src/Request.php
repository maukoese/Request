<?php

namespace Osen;
/**
 * A basic CURL wrapper
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
**/
class Request {
    
    /**
     * The file to read and write cookies to for requests
     *
     * @param string
    **/
    public static $cookie_file;
    
    /**
     * Determines whether or not requests should follow redirects
     *
     * @param boolean
    **/
    public static $follow_redirects = true;
    
    /**
     * An associative array of headers to send along with requests
     *
     * @param array
    **/
    public static $headers = array();
    
    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @param array
    **/
    public static $options = array();
    
    /**
     * The referer header to send along with requests
     *
     * @param string
    **/
    public static $referer;
    
    /**
     * The user agent to send along with requests
     *
     * @param string
    **/
    public static $user_agent;
    
    /**
     * Stores an error string for the last request if one occurred
     *
     * @param string
     * @access protected
    **/
    protected static $error = '';
    
    /**
     * Stores resource handle for the current CURL request
     *
     * @param resource
     * @access protected
    **/
    protected static $request;
    
    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
    **/
    function __construct() {
        self::$cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
        self::$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)';
    }
    
    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse object
    **/
    function delete($url, $vars = array()) {
        return self::request('DELETE', $url, $vars);
    }
    
    /**
     * Returns the error string of the current request if one occurred
     *
     * @return string
    **/
    function error() {
        return self::$error;
    }
    
    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse
    **/
    function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return self::request('GET', $url);
    }
    
    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
    **/
    function head($url, $vars = array()) {
        return self::request('HEAD', $url, $vars);
    }
    
    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function post($url, $vars = array()) {
        return self::request('POST', $url, $vars);
    }
    
    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function put($url, $vars = array()) {
        return self::request('PUT', $url, $vars);
    }
    
    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
    **/
    function request($method, $url, $vars = array()) {
        self::$error = '';
        self::$request = curl_init();
        if (is_array($vars)) $vars = http_build_query($vars, '', '&');
        
        self::$set_request_method($method);
        self::$set_request_options($url, $vars);
        self::$set_request_headers();
        
        $response = curl_exec(self::$request);
        
        if ($response) {
            $response = new CurlResponse($response);
        } else {
            self::$error = curl_errno(self::$request).' - '.curl_error(self::$request);
        }
        
        curl_close(self::$request);
        
        return $response;
    }
    
    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
    **/
    protected static function set_request_headers() {
        $headers = array();
        foreach (self::$headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        curl_setopt(self::$request, CURLOPT_HTTPHEADER, $headers);
    }
    
    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
    **/
    protected static function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt(self::$request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt(self::$request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt(self::$request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt(self::$request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
    
    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
    **/
    protected static function set_request_options($url, $vars) {
        curl_setopt(self::$request, CURLOPT_URL, $url);
        if (!empty($vars)) curl_setopt(self::$request, CURLOPT_POSTFIELDS, $vars);
        
        # Set some default CURL options
        curl_setopt(self::$request, CURLOPT_HEADER, true);
        curl_setopt(self::$request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$request, CURLOPT_USERAGENT, self::$user_agent);
        if (self::$cookie_file) {
            curl_setopt(self::$request, CURLOPT_COOKIEFILE, self::$cookie_file);
            curl_setopt(self::$request, CURLOPT_COOKIEJAR, self::$cookie_file);
        }
        if (self::$follow_redirects) curl_setopt(self::$request, CURLOPT_FOLLOWLOCATION, true);
        if (self::$referer) curl_setopt(self::$request, CURLOPT_REFERER, self::$referer);
        
        # Set any custom CURL options
        foreach (self::$options as $option => $value) {
            curl_setopt(self::$request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }
}
