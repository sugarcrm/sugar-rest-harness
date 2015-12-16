<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

class RandomizerNumber extends RandomizerAbstract implements RandomizerInterface
{
    public $min = 0;
    public $max = 10000;
    
    /**
     * getRandomData()
     *
     * Just returns a random number. You can set 'min' and 'max' in $params.
     *
     * @param array $params - a hash of optional parameters. May contain 'min' (int)
     *  or 'max' (int).
     * @return int - a random number.
     */
    public function getRandomData($params = array())
    {
        if (isset($params['min'])) {
            $this->min = (int)$params['min'];
        }
        
        if (isset($params['max'])) {
            $this->max = (int)$params['max'];
        }
        
        return rand($this->min, $this->max);
    }    
}
