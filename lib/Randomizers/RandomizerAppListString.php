<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

class RandomizerAppListString extends RandomizerAbstract implements RandomizerInterface
{
    protected static $instance = null;
    public $appListStrings = array();
    
    
    /**
     * getRandomData()
     *
     * Returns a random value from an array in Sugar's global app_list_strings
     * array based on an index. For nested arrays, use a dot-delimited string,
     * i.e. 'state_list_hierarchy.US' to get a random value from all US state
     * names.
     *
     * @param array $params - a hash of parameters. Must include these keys:
     *  key
     * @return string - a random selection from the options of an enum field.
     */
    public function getRandomData($params)
    {
        if (!isset($params['key'])) {
            throw new RandomDataParamMissing(get_class($this), 'key');
        }
        $this->populate();
        $data = $this->parseDataForKey($params['key']);
        $indices = array_keys($data);
        $randomIndex = $indices[rand(0, count($indices) - 1)];
        return $data[$randomIndex];
    }
    
    
    /**
     * parseDataForKey()
     *
     * Parses the data pulled from app_list_strings for the index $fullKey. If
     * $fullKey has dots, they're treated as keys to nested arrays.
     *
     * @param string $fullKey - a dot-delimited string of index keys which are (hopefully)
     *  valid in app_list_strings.
     * @return array - an array of strings derived from the $fullKey
     */
    public function parseDataForKey($fullKey)
    {
        $arrayOfStrings = $this->appListStrings;
        $keys = explode('.', $fullKey);
        foreach ($keys as $key) {
            if (isset($arrayOfStrings[$key])) {
                $arrayOfStrings = $arrayOfStrings[$key];
            } else {
                throw new RandomDataKeyIsInvalid($fullKey, $key);
            }
        }
        
        if (!empty($arrayOfStrings)) {
            if (is_array($arrayOfStrings)) {
                return $arrayOfStrings;
            } else {
                return array($arrayOfStrings);
            }
        } else {
            return array();
        }
    }
    
    /**
     * populate()
     *
     * Takes the data retrieved from the call to sendEnumRestRequest() and stores
     * it in $this->data[$module][$field].
     */
    public function populate()
    {
        if (empty($this->appListStrings)) {
            $this->appListStrings = $this->sendLangRESTRequest();
        }
    }
    
    
    /**
     * sendLangRESTRequest()
     *
     * Sends a REST request to the lang endpoint, and gets the whole contents of
     * the results and returns them as an associative array.
     *
     * @return array - an associative array of nested arrays of strings.
     */
    public function sendLangRESTRequest()
    {
        $config = array(
            'method' => 'GET',
            'route' => '/lang/en_us',
        );
        
        $job = new \SugarRestHarness\Jobs\Generic($config);
        $job->rawResults = $job->connector->makeRequest();
        $results = json_decode($job->rawResults, true);
        return $results['app_list_strings'];
    }
}
