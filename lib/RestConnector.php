<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;
/**
 * RestConnector
 *
 * The RestConnector is the class that contacts the sugar instance with the REST 
 * requests produced by jobs. The connector needs at least the login data stored
 * in the config file (see the config/ directory). The connector expects to be passed
 * an Oauth2 token, but will get a token on its own if one is not passed in. The 
 * connect will generate its own errors and status messages in addition to any errors
 * returned by the REST request.
 */
 
class RestConnector
{
    private $token = false;
    private $refresh_token = false;
    public $requiredConfigFields = array(
        'user_name',
        'password',
        'base_url', // i.e. http://yavin4
        'rest_dir', // i.e. /rest
        'rest_version_dir', // i.e. /v10
        'user_agent_string',
        'client_id',
    );

    public $module;
    public $bean_id;
    public $fields;
    public $post;
    public $term;
    public $user_name;
    public $password;
    public $base_url;
    public $install_path;
    public $user_agent_string;
    public $client_platform = 'base';
    public $client_name = '';
    public $client_id = '';
    public $client_app_version = '';
    public $rest_dir;
    public $rest_version_dir;
    public $max_num = 0;
    public $my_items = 0;
    public $favorites = 0;
    public $linkName;
    public $verbose = false;
    public $configFileName;
    public $order_by = '';
    public $sort_order = 'asc';
    public $qs = array();
    public $id_field = '';
    public $linkedBean_id = '';
    public $prefName = '';
    public $jobClass = '';
    public $filters = array();
    public $expectedHTTPReturnCode = '200';
    
    public $msgs = array();
    public $errors = array();
    public $httpReturn = array();
    
    
    /**
    nested hash $routeMaps - The keys in this hash are the types of requests that 
    REST supports, and the values are an array, consisting of the correct METHOD for
    that type of reqeust, and then of config param names, in the order they
    must be listed in to build the correct route (URL) for that type of request.
    */
    public $routeMaps = array(
    );
    
    public $stdCURLOptions = array(
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSLVERSION => 4,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_VERBOSE => false,
    );
    
    
    
    public function __construct($config)
    {
        if (!is_array($config) || empty($config)) {
            $this->error("Cannot instantiate the RestConnector - \$config isn't a hash or is empty");
            die();
        }
        
        // defaults for httpReturn - in case we can't send a REST request.
        $this->httpReturn['HTTP Return Code'] = '0';
        $this->httpReturn['Content-Length'] = '0';
        
        $setFields = array();
        foreach ($config as $name => $value) {
            $this->$name = $value;
            $setFields[] = $name;
        }
        
        $missingRequiredField = false;
        foreach ($this->requiredConfigFields as $fieldName) {
            if (!in_array($fieldName, $setFields) || empty($this->$fieldName)) {
                $this->error("$fieldName was not set in your job.");
                $missingRequiredField = true;
            }
        }
        
        if ($missingRequiredField === true) {
            $this->error("All required fields were not set in config. Aborting.");
            $this->report();
            die();
        }
        
        $this->generateRouteMaps();
    }
    
    
    /**
     * generateRouteMaps()
     *
     * Creates the routeMaps array. The array maps a route name as the key to a nested
     * array of information necessary to construct that route. The first element in 
     * that array will be the method, followed by the segments of the URL the route
     * uses. Segments that begin with '$' are expected to be replaced by the value 
     * of the named property of this class. I.E., if $this->module = 'Contacts', then
     * '$module' will be replaced by 'Contacts' when the route is constructed.
     *
     * @return void
     */
    public function generateRouteMaps()
    {
        if (!empty($this->routeMaps)) {
            return;
        }
        
        $coreRouteMapPath = \SugarRestHarness\Harness::getAbsolutePath('lib/routeMap.php');
        $routeMap = null;
        require($coreRouteMapPath);
        $this->routeMaps = $routeMap;
        
        $customRouteMapPath = \SugarRestHarness\Harness::getAbsolutePath('custom/lib/routeMap.php');
        if (file_exists($customRouteMapPath)) {
            require($customRouteMapPath);
            $this->routeMaps = array_merge($this->routeMaps, $routeMap);
        }
    }
    
    
    /**
     * verbose()
     *
     * Turns on and off the VERBOSE curl option. Turning verbose on produces a great 
     * deal of helpful details about the state of the request and response.
     *
     * @param bool $state - true to turn on, false to turn off.
     */
    public function verbose($state)
    {
        $this->stdCURLOptions[CURLOPT_VERBOSE] = (bool) $state;
    }
    
    
    /**
     * formatFields()
     *
     * Takes the fields property and concatenates it with ',' and then url encodes it,
     * to produce a delimited list of fields to pass to the request.
     *
     * @param array $fields - an array of field names.
     * @return string - a delimited list of fields.
     */
    public function formatFields($fields)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        
        $fieldsString = '';
        if (!empty($fields)) {
            $fields = implode(',', $fields);
            $fieldsString = urlencode($fields);
        }
        return $fieldsString;
    }
    
    
    /**
     * msg()
     *
     * Records a message for future use. Messages are for status and debugging, as
     * opposed to errors, which are for saying something is wrong.
     *
     * @param string $msg - an error message.
     */
    public function msg($msg)
    {
        $this->msgs[] = $msg;
    }
    
    
    /**
     * error()
     *
     * Records an error message for future use.
     *
     * @param string $msg - an error message.
     */
    public function error($msg)
    {
        $this->errors[] = $msg;
    }
    
    
    /**
     * getURL()
     *
     * Concatenates the base_url, install_path, rest_dir and rest_version_dir into
     * one string, and the appends the passed in $path var to the end of that to 
     * produce the final URL for the REST service.
     
     * returns the url for the rest service we're trying to contact.
     *
     * @param string $route - the route - the last part of the rest path, after the base_url, 
     *  install_path, rest_dir and rest_version_dir.
     * @return string - a full URL for the REST service.
     */
    public function getURL($route)
    {
        $queryString = '';
        
        if (!empty($this->url)) {
            $this->msg("REST Service URL is $this->url");
            return $this->url;
        }
        
        if (empty($route)) {
            $this->error("Route could not be determined for $this->jobClass");
            return '';
        }
        
        $urlPieces = array(
            $this->base_url,
            $this->install_path,
            $this->rest_dir,
            $this->rest_version_dir,
            $route,
        );
        $url = implode('', $urlPieces);
        
        if ($route != '/oauth2/token') {
            $queryString = $this->buildQueryString();
        }
        
        if (!empty($queryString)) {
            $url .= "?$queryString"; 
        }
        $this->msg("REST Service URL is $url");
        return $url;
    }


    /**
     * getRoute()
     *
     * Returns a path for the REST request based on job config values, which are
     * translated to properties of this class. These values
     * may have been overwritten by command line arguments.
     *
     * @return string - a path for a REST request.
     * @throws MissingRouteMap
     * @throws MissingRequiredRouteMapComponents
     */
    public function getRoute()
    {
        if (IsSet($this->route)) {
            return $this->route;
        }
        
        if (IsSet($this->routeMap)) {
            $map = $this->routeMaps[$this->routeMap];
            $method = $map[0];
            $route = '';
            for ($i = 1; $i < count($map); $i++) {
                if (strpos($map[$i], '$') === 0) {
                    $propName = str_replace('$', '', $map[$i]);
                    if (IsSet($this->$propName) && !empty($this->$propName)) {
                        $routeSegment = $this->$propName;
                    } else {
                        throw new \SugarRestHarness\MissingRequiredRouteMapComponents($this->jobClass, $propName, $this->routeMap);
                        $routeSegment = '';
                    }
                } else {
                    $routeSegment = $map[$i];
                }
                $route .= "/$routeSegment";
            }
            
            if (empty($this->errors)) {
                return $route;
            } else {
                // if required compents of the route are missing, you should stop here
                // and not send a request!
                return '';
            }
            
        } else {
            throw new \SugarRestHarness\MissingRouteMap();
        }
    }
    
    
    /**
     * getMethod()
     *
     * Returns the method of the request: GET, POST, PUT or DELETE. Records an error
     * message if the method cannot be determined.
     *
     * @return string - GET|POST|PUT|DELETE
     */
    public function getMethod()
    {
        if (!empty($this->method)) {
            return $this->method;
        }
        
        if (IsSet($this->routeMap) && is_array($this->routeMaps[$this->routeMap])) {
            return $this->routeMaps[$this->routeMap][0];
        }
        
        throw new \SugarRestHarness\NoMethodSet();
    }
    
    
    /**
     * buildQueryString()
     *
     * Builds a query string to append to the URL of a GET request. Takes certain
     * params in config and adds them to an array, with urlencoded values, and then
     * concatenates the array with amperands (&).
     *
     * @return string - a urlencoded query string.
     */
    public function buildQueryString()
    {
        $qsPairs = array();
        foreach ($this->qs as $varName => $value) {
            switch($varName) {
                case 'fields':
                    $value = $this->formatFields($value);
                    break;

                case 'filters':
                    if (is_string($value)) {
                        $filters = explode('&', $value);
                    } else {
                        $filters = $value;
                    }
                    foreach ($filters as $filter) {
                        $qsPairs[] = $filter;
                    }
                    break;
                
                case 'filter_json':
                    $filters = $this->formatFilters($value);
                    foreach ($filters as $filterParams => $filterValue) {
                        $qsPairs[] = "{$filterParams}={$filterValue}";
                    }
                    break;
                    
                case 'order_by':
                    $sort_order = IsSet($this->qs['sort_order']) ? ':' . $this->qs['sort_order'] : '';
                    $value = urlencode("{$value}{$sort_order}");
                    break;
                    
                case 'term':
                case 'q':
                    $value = urlencode($value);
                    $varName = 'q';
                    break;
                    
                default:
                    $value = urlencode($value);
                    break;
            }
            
            // filters has been handled above, and should not be re-added.
            if ($varName == 'filters' || $varName == 'filter_json') {
                continue;
            }

            // if filters are set url encode the filter and its value.
            if (strpos($varName, 'filter[') === 0) {
                $varName = urlencode($varName);
                $value = urlencode($value);
            }
            
            $qsPairs[] = "$varName=$value";
        }
        return implode('&', $qsPairs);
    }
    
    
    /**
     * formatFilters()
     *
     * Takes a JSON string that defines a filter and converts that string into an
     * array of filter/value pairs. The filter/value pairs are url encoded for use
     * in query strings.
     *
     * @param string $filterJsonString - a json filter definition.
     * @return array - pairs of filter/value
     */
    public function formatFilters($filterJsonString)
    {
        $this->filters = array();
        $filterArray = json_decode($filterJsonString, true, 32);
        $this->recursiveFormatFilters($filterArray['filter']);
        $pairs = array();
        foreach ($this->filters as $filterString) {
            list($key, $value) = explode('=', $filterString);
            $key = urlencode($key);
            $value = urlencode($value);
            $pairs[$key] = $value;
        }
        return $pairs;
    }
    
    
    /**
     * recursiveFormatFilters()
     *
     * This method iterates recursively through the data structure described by the json string
     * passed to formatFilters() until it gets to a scalar value in that structure.
     * Then it takes all of the preceding keys that lead to that scalar value and
     * uses them to form a single array key, with the scalar as the value associated 
     * to that key. The key/value pairs are stored in an array.
     *
     * @param array $hash - an associative array.
     * @param string $string - the previously discovered keys from earlier iterations.
     * @return string - The latest array key appened to a list of its parent keys.
     */
    public function recursiveFormatFilters($hash, $string='')
    {
        foreach ($hash as $key => $value) {
            if (is_array($value)) {
                $newString = "{$string}[$key]";
                $newString .= $this->recursiveFormatFilters($value, $newString);
            } else {
                $newString = "{$string}[$key]" . "=" . "$value";
                $this->filters[] = "filter$newString";
            }
        }
        
        return $string;
    }
    
    
    /**
     * getTokenPostData()
     *
     * Collects the data necessary to send in the POST request to the Oauth2 service
     * to ask for an auth token. The data is formatted as a JSON string.
     *
     * @return string - a JSON encoded string of nested objects.
     */
    public function getTokenPostData($grant_type = 'password')
    {
        $data = new \stdClass();
        $data->grant_type = $grant_type;
        $data->client_id = $this->client_id;
        $data->platform = $this->client_platform;
        $data->client_secret = '';

        if ($grant_type == 'password') {
            $data->username = $this->user_name;
            $data->password = $this->password;
        }

        if ($grant_type == 'refresh_token') {
            $data->refresh_token = $this->refresh_token;
        }

        return json_encode($data);
    }
    
    
    /**
     * getCURLHandle()
     *
     * Returns a CURL handle that has all the basic options set on it that apply to 
     * either POST or GET requests to the REST service.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @return resource - a CURL handle.
     */
    public function getCURLHandle($url)
    {
        $this->ch = curl_init($url);
        foreach ($this->stdCURLOptions as $CONST => $value) {
            curl_setopt($this->ch, $CONST, $value);
        }
        
        if (!empty($this->cookies)) {
            $cookies = array();
            foreach ($this->cookies as $cookieName => $cookieValue) {
                $cookies[] = "$cookieName=$cookieValue";
            }
            $cookieString = implode(';', $cookies);
            curl_setopt($this->ch, CURLOPT_COOKIE, $cookieString);
        }
        return $this->ch;
    }
    
    
    /**
     * getCURLHandleForGET()
     *
     * Returns a CURL handle that has all the basic options set on it that apply to 
     * GET requests to the REST service.
     *
     * Takes an optional token argument - it's optional because you may be using this
     * curl handle to request the token in the first place.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $token - an Oauth2 token ID.
     * @return resource - a CURL handle.
     */
    public function getCURLHandleForGET($url, $token)
    {
        $ch = $this->getCURLHandle($url);
        
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token
            ));
        } else {
            $this->error("Tried to send a GET request without auth: $url");
        }
        
        return $ch;
    }
    
    
    /**
     * getCURLHandleForPOST()
     *
     * Returns a CURL handle that has all the basic options set on it that apply to 
     * POST requests to the REST service.
     *
     * Takes an optional token argument - it's optional because you may be using this
     * curl handle to request the token in the first place.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $postData - a JSON-encoded array of name/value pairs.
     * @param string $token (optional) - an Oauth2 token ID.
     * @return resource - a CURL handle.
     */
    public function getCURLHandleForPOST($url, $postData, $token = false)
    {
        $ch = $this->getCURLHandle($url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token,
                "Content-Type: application/json",
            ));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "Content-Type: application/json",
            ));
        }
        
        return $ch;
    }


    /**
     * getCURLHandleForModuleLoadablePackage()
     *
     * Returns a curl handle for uploading a module-loadable package.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $postData - a JSON-encoded array of name/value pairs.
     * @param string $token - an Oauth2 token ID.
     * @return resource - a CURL handle.
     */
    public function getCURLHandleForModuleLoadablePackage($url, $postData, $token)
    {
        $ch = $this->getCURLHandle($url);
        curl_setopt($ch, CURLOPT_POST, true);

        // $postData will be a key => path/to/file pair, with just one pair.
        $postData = json_decode($postData, true);
        $keys = array_keys($postData);
        $fieldName = array_pop($keys);
        $filePath = $postData[$fieldName];

        if (!file_exists($filePath)) {
            throw new \SugarRestHarness\UploadFileNotFound($fieldName, $filePath);
        }

        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token,
            ));
        } else {
            $this->error("Tried to upload a file without auth: $url");
        }

        // this is the magic part - you have to define a new $postData variable, instead of just overwriting
        // the $fieldName value. Otherwise you get the passed-in version of $postData
        $postData = [
            $fieldName => new \CURLFile($filePath, 'application/zip')
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        return $ch;
    }


    /**
     * getCURLHandleForPUT()
     *
     * Returns a CURL handle that has all the basic options set on it that apply to 
     * PUT requests to the REST service.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $putData - a JSON-encoded array of name/value pairs.
     * @param string $token - an Oauth2 token ID.
     * @return resource - a CURL handle.
     */
    public function getCURLHandleForPUT($url, $putData, $token)
    {
        $ch = $this->getCURLHandle($url);
        
        // WOW - you have to use customrequest here, instead of CURLOPT_PUT.
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $putData);
        
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token,
                "Content-Type: application/json",
            ));
        } else {
            $this->error("Tried to send a PUT request without auth: $url");
        }
        return $ch;
    }


    /**
     * getCURLHandleForPatch
     *
     * Returns a CURL Handle that has all the basic options set on it that apply to PUT
     * Requests to the REST Service.
     *
     * This is for the /integrate api endpoint - PATCH is the HTTP verb sugar uses for 'upserts'.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $putData - a JSON-encoded array of name/value pairs.
     * @param string $token - an Oauth2 token ID.
     * @return resource - a CURL handle.
     */
    public function getCURLHandleForPatch($url, $data, $token)
    {
        $ch = $this->getCURLHandle($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token,
                "Content-Type: application/json",
            ));
        } else {
            $this->error("Tried to send a PATCH request without auth: $url");
        }
        return $ch;
    }


    /**
     * getCURLHandleForDelete()
     *
     * Returns a CURL handle that has all the basic options set on it that apply to 
     * DELETE requests to the REST service.
     *
     * @param string $url - the full URL to point to for the REST request.
     * @param string $token - an Oauth2 token ID.
     */
    public function getCURLHandleForDelete($url, $token)
    {
        $ch = $this->getCURLHandle($url);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                "OAuth-Token: " . $token
            ));
        } else {
            $this->error("Tried to send a DELETE request without auth: $url");
        }
        
        return $ch;
    }
    
    
    /**
     * getToken()
     *
     * Gets an Oauth2 token from our REST service based on our config data.
     * Also stores a refresh_token for use in long-running sessions.
     *
     * @return string - an Oauth2 auth token ID.
     */
    public function getToken($grant_type = 'password')
    {
        $token = \SugarRestHarness\Config::getInstance()->getToken();
        if (!empty($token)) {
            return $token;
        }

        $url = $this->getURL('/oauth2/token');
        $this->msg("getting token from $url for {$this->user_name}");
        $data = $this->getTokenPostData($grant_type);
        $ch = $this->getCURLHandleForPOST($url, $data);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        $rawResponse = curl_exec($ch);
        $this->collecthttpReturn($ch);
        $response = json_decode($rawResponse);
        curl_close($ch);
        
        if (!empty($rawResponse) && is_object($response) && property_exists($response, 'access_token')) {
            $this->msg("Received token {$response->access_token}");
            $this->token = $response->access_token;
            $this->refresh_token = $response->refresh_token;
            \SugarRestHarness\Config::getInstance()->setToken($response->access_token);
            \SugarRestHarness\Config::getInstance()->setRefreshToken($response->refresh_token);
            return $response->access_token;
        } else {
            $this->error("Did not receive an access token from OAuth2 request! Bad password?");
            if (is_array($response)) {
                foreach ($response as $index => $msg) {
                    $this->error("$index: $msg");
                }
            } else {
                $this->error($response);
            }
            $errorData = array($this->msgs, $this->errors, $this->httpReturn);
            throw new \SugarRestHarness\LoginFailure($this->user_name, $url, $errorData);
        }
    }


    /**
     * refreshAccessToken()
     *
     * If the access token expires, this method should be called to refresh it using the refresh_token
     * property set in getToken()
     *
     * @return bool
     * @throws Exception
     */
    public function refreshAccessToken()
    {
        $this->clearToken();
        $this->refresh_token = \SugarRestHarness\Config::getInstance()->getRefreshToken();

        if (empty($this->refresh_token)) {
            throw new \SugarRestHarness\Exception("Cannot refresh access token when refresh_token hasn't been set");
        }

        $this->url = '';
        try {
            $this->getToken('refresh_token');
        } catch (\SugarRestHarness\LoginFailure $e) {
            die($e->getFormattedOutput());
        }

        if (!empty($this->token)) {
            return true;
        }
        return false;
    }
    
    
    /**
     * formatPostData()
     *
     * Takes a hash of name/value pairs and json-encodes it for sending via POST/PUT
     *
     * @param array $data - hash of name/value pairs.
     * @return string - a JSON-encoded string.
     */
    public function formatPostData($data)
    {
        $dataString = json_encode($data);
        return $dataString;
    }
    
    
    /**
     * makeRequest()
     *
     * All requests the harness sends are routed through this method. It collects all
     * of the query string data and formats it, gets the route, gets the method, and
     * sends the request. It returns the results of the request as a string.
     *
     * @return mixed - typically a string, or false if there was a cURL error.
     */
    public function makeRequest()
    {
        try {
            $url = $this->getURL($this->getRoute());
        } catch (\SugarRestHarness\Exception $e) {
            throw $e;
        }
        
        try {
            $method = $this->getMethod();
        } catch (\SugarRestHarness\Exception $e) {
            $this->error("Cannot make a request without a method set in config.");
            throw $e;
        }
        
        try {
            return $this->sendRequest($url, $method, $this->post);
        } catch (\SugarRestHarness\Exception $e) {
            throw $e;
        }
        
    }
    
    
    
    /**
     * sendRequest()
     *
     * This method is used for all requests - it will format a data hash as JSON for
     * POST's and PUT's. It will get a token for OAuth for the user specified in 
     * config. Then it will get the kind of cURL handle specified by $type. Then it
     * executes the cURL handle and finally returns the results.
     *
     *
     * @param string $url - the URL of your request.
     * @param string $url - the type of request - GET, POST, PUT, DELETE
     * @param array $data - a hash of name value pairs for the request - these values
     *  will be passed to the application. Optional.
     * @return mixed - whatever value curl_exec() returns, typically a string, or false
     *  on error.
     */
    public function sendRequest($url, $type, $data = false)
    {
        $this->url = $url;
        $this->method = $type;
        if (!$data && !in_array($type, array('GET', 'DELETE'))) {
            $this->error("Cannot set a '$type' message with no data to $url");
            $this->report();
            die();
        } else {
            $postData = $this->formatPostData($data);
        }
        
        if (!$this->token) {
            // if we don't have a token, we need to go get one. But it's possible that
            // $this->url is already set, and if it is then we'll go that url for our
            // token. That probably won't work becasue $this->url will be set to the
            // job's url, not the oauth url. So, stash the url, unset $this->url,
            // then get the token and restore $this->url.
            $url = $this->url;
            unset($this->url);
            $token = $this->getToken();
            $this->url = $url;
        } else {
            $token = $this->token;
        }
        
        if (empty($token)) {
            $this->error('Could not acquire an auth token');
            return false;
        } else {
            $this->msg("token is $token");
        }
        
        switch ($type)
        {
            case 'GET':
                $ch = $this->getCURLHandleForGET($url, $token);
                break;
                
            case 'POST':
                $ch = $this->getCURLHandleForPOST($url, $postData, $token);
                break;
                
            case 'PUT':
                $ch = $this->getCURLHandleForPUT($url, $postData, $token);
                break;
                
            case 'DELETE':
                $ch = $this->getCURLHandleForDELETE($url, $token);
                break;

            case 'PACKAGE':
                $ch = $this->getCURLHandleForModuleLoadablePackage($url, $postData, $token);
                break;

            case 'PATCH':
                $ch = $this->getCURLHandleForPatch($url, $postData, $token);
                break;
        }
        
        if ($ch) {
            $results = curl_exec($ch);
            $this->collecthttpReturn($ch);
            curl_close($ch);
            if (!$this->receivedExpectedHTTPReturnCode()) {
                if ($this->httpReturn['HTTP Return Code'] == '401') {
                    // assume access token has expired, attempt refresh.
                    if ($this->refreshAccessToken()) {
                        return $this->sendRequest($url, $type, $data);
                    } else {
                        throw new \SugarRestHarness\ServerError("Auth failure - assumed access token expired and attempted to refresh access_token, but could not.\n" . $this->httpReturn['HTTP Return Code'], $this->expectedHTTPReturnCode, $results);
                    }
                } else {
                    throw new \SugarRestHarness\ServerError($this->httpReturn['HTTP Return Code'], $this->expectedHTTPReturnCode, $results);
                }
            }
            return $results;
        } else {
            $this->error("Cannot get a curl object from PHP!");
            $this->report();
            return false;
        }
    }
    
    
    /**
     * setExpectedHTTPReturnCode()
     *
     * Sets the expected return code. If the request this connector sends returns
     * an http return code value that is different from what you set here, a
     * ServerError exception will be thrown in the sendRequest() method.
     *
     * The default value is '200'.
     *
     * @param string $code - the expected return code, i.e. 200, 404, 500, etc.
     */
    public function setExpectedHTTPReturnCode($code)
    {
        $this->expectedHTTPReturnCode = $code;
    }
    
    
    public function receivedExpectedHTTPReturnCode()
    {
        if (!isset($this->httpReturn['HTTP Return Code'])) {
            return false;
        }
        
        return $this->httpReturn['HTTP Return Code'] == $this->expectedHTTPReturnCode 
            || $this->httpReturn['HTTP Return Code'] == '200';
            
    }
    
    
    /**
     * collecthttpReturn()
     *
     * Gathers information about our cURL reqeust and stores it for later reference.
     *
     * @param resource - a CURL handle.
     * @return void.
     */
    public function collecthttpReturn($ch)
    {
        $this->httpReturn['URL'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
        if (IsSet($this->post) && !empty($this->post)) {
            $this->httpReturn['POST'] = json_encode($this->post);
        }
        
        $this->httpReturn['HTTP Return Code'] = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->httpReturn['Content-Length'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $this->httpReturn['cURL Error'] = curl_error($ch);
        if (empty($this->httpReturn['cURL Error'])) {
            unset($this->httpReturn['cURL Error']);
        }
        
    }


    public function clearToken()
    {
        $this->token = '';
        \SugarRestHarness\Config::getInstance()->setToken('', true);
    }
    
    
    /**
     * report()
     *
     * Outputs all of the messages and errors recorded by this class.
     *
     * @return void.
     */
    public function report()
    {
        if (!empty($this->msgs)) {
            for ($i = 0; $i < count($this->msgs); $i++) {
                print("\nmsg - {$this->msgs[$i]}");
            }
        }
        
        if (!empty($this->errors)) {
            for ($i = 0; $i < count($this->errors); $i++) {
                print("\nerror - {$this->errors[$i]}");
            }
        }
        
        print("\n");
    }
}
