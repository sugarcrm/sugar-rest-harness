<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

class RandomizerEnum extends RandomizerAbstract implements RandomizerInterface
{
    protected static $instance = null;
    
    /**
     * getRandomData()
     *
     * Returns a random value from the list of options for an enum field for a 
     * given module.
     *
     * @param array $params - a hash of parameters. Must include these keys:
     *  module
     *  field
     * @return string - a random selection from the options of an enum field.
     */
    public function getRandomData($params)
    {
        if (!isset($params['module'])) {
            throw new RandomDataParamMissing(get_class($this), 'module');
        }
        
        if (!isset($params['field'])) {
            throw new RandomDataParamMissing(get_class($this), 'field');
        }
        
        $module = $params['module'];
        $field = $params['field'];
        
        if (empty($this->data) || empty($this->data[$module]) || empty($this->data[$module][$field])) {
            $this->populate($module, $field);
        }
        
        if (empty($this->data[$module][$field])) {
            throw new RandomDataNoEnumFieldData($module, $field);
        }
        
        return $this->data[$module][$field][rand(0, (count($this->data[$module][$field]) - 1))];
    }
    
    
    /**
     * populate()
     *
     * Takes the data retrieved from the call to sendEnumRestRequest() and stores
     * it in $this->data[$module][$field].
     *
     * @param string $module - a sugar module name.
     * @param string $field - a field name that is valid for $module.
     */
    public function populate($module, $field)
    {
        if (!isset($this->data[$module])) {
            $this->data[$module] = array($field => array());
        }
        
        if (!isset($this->data[$module][$field])) {
            $this->data[$module][$field] = array();
        }
        
        $fieldOptions = $this->sendEnumRestRequest($module, $field);
        $this->data[$module][$field] = array_keys($fieldOptions);
    }
    
    
    /**
     * sendEnumRestRequest()
     *
     * Sends a REST request using the harness to the ENUM endpoint, which requires
     * a module name and a field name. Formats those results as an associative 
     * array and returns the array.
     *
     * @param string $module - a sugar module name.
     * @param string $field - a field name that is valid for $module.
     * @return array - an array of strings.
     */
    public function sendEnumRestRequest($module, $field)
    {
        $config = array(
            'method' => 'GET',
            'routeMap' => 'getEnumValues',
            'module' => $module,
            'field' => $field,
        );
        $job = new \SugarRestHarness\Jobs\Generic($config);
        $job->rawResults = $job->connector->makeRequest();
        $results = json_decode($job->rawResults, true);
        return $results;
    }
}
