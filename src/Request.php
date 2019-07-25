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
    public $cookie_file;
    
    /**
     * Determines whether or not requests should follow redirects
     *
     * @param boolean
    **/
    public $follow_redirects = true;
    
    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @param array
    **/
    public $options = array();
    
    /**
     * The referer header to send along with requests
     *
     * @param string
    **/
    public $referer;
    
    /**
     * The user agent to send along with requests
     *
     * @param string
    **/
    public $user_agent;
    
    /**
     * Stores an error string for the last request if one occurred
     *
     * @param string
     * @access protected
    **/
    protected $error = '';
    
    /**
     * Stores resource handle for the current CURL request
     *
     * @param resource
     * @access protected
    **/
    protected $request;
    
    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
    **/
    function __construct() {
        $this->cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
        $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)';
    }
    
    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a Response object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return Response object
    **/
    protected static function delete($url, $vars = array()) {
        $self = new self;         
        return $self->request('DELETE', $url, $vars);
    }
    
    /**
     * Returns the error string of the current request if one occurred
     *
     * @return string
    **/
    protected function error() {
        return $this->error;
    }
    
    /**
     * Sets user agent
     *
     * @return string
    **/
    protected static function user_agent($agent) {
        $self = new self;
        $self->user_agent = $agent;

        return __CLASS__ ;
    }
    
    /**
     * Sets user agent
     *
     * @return string
    **/
    protected static function referer($referer) {
        $self = new self;
        $self->referer = $referer;

        return __CLASS__ ;
    }
    
    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a Response object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return Response
    **/
    protected static function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }

        $self = new self;
        return $self->request('GET', $url);
    }
    
    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a Response object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return Response
    **/
    protected static function head($url, $vars = array()) {
        $self = new self;         
        return $self->request('HEAD', $url, $vars);
    }
    
    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars 
     * @return Response|boolean
    **/
    protected static function post($url, $vars = array()) {
        $self = new self;         
        return $self->request('POST', $url, $vars);
    }
    
    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a Response object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return Response|boolean
    **/
    protected static function put($url, $vars = array()) {
        $self = new self;         
        return $self->request('PUT', $url, $vars);
    }
    
    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a Response object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return Response|boolean
    **/
    protected function request($method, $url, $vars = array()) {
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars)) $vars = http_build_query($vars, '', '&');
        
        $this->set_request_method($method);
        $this->set_request_options($url, $vars);

        if (!empty($this->headers)) {
            curl_setopt($self->request, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($this->request);
        
        if ($response) {
            $response = new Response($response);
        } else {
            //return new Exception(curl_error($this->request), curl_errno($this->request));
            $this->error = curl_errno($this->request).' - '.curl_error($this->request);
        }
        
        curl_close($this->request);
        
        return $response;
    }
    
    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
    **/
    protected static function headers($request_headers = array()) {
        $self = new self;
        $headers = array();
        foreach ($request_headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        $self->headers = $request_headers;

        return __CLASS__ ;
    }
    
    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
    **/
    protected function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
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
    protected function set_request_options($url, $vars) {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        
        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        if ($this->follow_redirects) curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        if ($this->referer) curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }
}
