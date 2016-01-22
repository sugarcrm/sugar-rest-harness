<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

class RandomizerRelatedBean extends RandomizerAbstract implements RandomizerInterface
{
    public $data = array();
    
    
    /**
     * getRandomData()
     *
     * Returns the ID of a random bean that is related to a specified bean of the
     * type of specified module. For example, if you specify module = 'Accounts'
     * and bean_id = 'some-account-id' and linkName = 'Contacts', you'll get a 
     * randomly selected contact id from among all of the contacts related to 
     * that account.
     *
     * NOTE: this method will NOT select from all possible beans! It will select
     * only from the beans the user you're logging in as can access.
     *
     * @param array $params - a hash of paramters. Must include module, bean_id 
     *  and linkName. May include 'field'.
     * @return mixed - If you specify more than one field, you get an array with
     *  the field names as keys. If you specify no field, or only one, you get
     *  a scalar value for that field.
     */
    public function getRandomData($params)
    {
        if (!isset($params['module']) || empty($params['module'])) {
            throw new \SugarRestHarness\RandomDataParamMissing(get_class($this), 'module');
        }
        
        if (!isset($params['bean_id']) || empty($params['bean_id'])) {
            throw new \SugarRestHarness\RandomDataParamMissing(get_class($this), 'bean_id');
        }
        
        if (!isset($params['linkName']) || empty($params['linkName'])) {
            throw new \SugarRestHarness\RandomDataParamMissing(get_class($this), 'linkName');
        }
        
        if (isset($params['field'])) {
            if (is_string($params['field'])) {
                $field = array($params['field']);
            } else {
                $field = $params['field'];
            }
            $this->field = $field;
        } else {
            $this->field = array('id');
        }
        
        $beanList = $this->populate($params['module'], $params['bean_id'], $params['linkName']);
        
        if (empty($beanList)) {
            return '';
        }
        
        $randomBean = $beanList[rand(0, (count($beanList) - 1))];
        
        $randomData = array();
        foreach ($this->field as $fieldName) {
            if (isset($randomBean[$fieldName])) {
                $randomData[$fieldName] = $randomBean[$fieldName];
            }
        }
        
        if (count($this->field) == 1) {
            $randomData = $randomData[$this->field[0]];
        }
        return $randomData;
    }
    
    
    /**
     * populate()
     *
     * Checks to see if we've already retrieved beans for the passed in module
     * name, beanID and linkName. If we have, we just return that data. If we haven't,
     * retrieve and then store the result, and finally return it.
     *
     * @param string $moduleName - the name of a sugar module.
     * @param string $beanID - the ID of a bean.
     * @param string $linkName - the name of a link on $moduleName.
     * @return array - an array of nested sugar bean data.
     */
    public function populate($moduleName, $beanID, $linkName)
    {
        if (empty($this->data[$moduleName])) {
            $this->data[$moduleName] = array();
        }
        
        if (empty($this->data[$moduleName][$beanID])) {
            $this->data[$moduleName][$beanID] = array();
        }
        
        if (empty($this->data[$moduleName][$beanID][$linkName])) {
            $this->data[$moduleName][$beanID][$linkName] = $this->sendListRequest($moduleName, $beanID, $linkName);
        }
        
        return $this->data[$moduleName][$beanID][$linkName];
    }
    
    
    /**
     * sendListRequest()
     *
     * Sends a REST request using the harness itself to a list related beans endpoint. This
     * endpoint should return a JSON array with a 'records' field, which will be
     * a nested array of bean data. This method returns that 'records' array.
     *
     * @param string $moduleName - the name of a sugar module.
     * @param string $beanID - the ID of a bean.
     * @param string $linkName - the name of a link on $moduleName.
     * @return array - an array of nested sugar bean data.
     */
    public function sendListRequest($module, $beanID, $linkName)
    {
        $config = array(
            'method' => 'GET',
            'module' => $module,
            'routeMap' => 'filterRelated',
            'bean_id' => $beanID,
            'linkName' => $linkName,
        );
        $job = new \SugarRestHarness\Jobs\Generic($config);
        $job->rawResults = $job->connector->makeRequest();
        $results = json_decode($job->rawResults, true);
        return $results['records'];
    }
}
    