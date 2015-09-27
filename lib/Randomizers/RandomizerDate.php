<?php
namespace SugarRestHarness\Randomizers;

class RandomizerDate extends RandomizerAbstract implements RandomizerInterface
{
    public $future = true;
    public $range_in_days = 100;
    public $multiplier = 1;
    public $format = "Y-m-d";
    
    
    /**
     * getRandomData()
     *
     * Returns a random date. The date can be in the future or the past, and can
     * be any number of days ahead or behind now. The format can also be set. See
     * $params description.
     *
     * @param array $params - a hash of parameters to set for this method. Supported
     *  params are:
     *      bool future - boolean true for a future date, boolean false for a past date. 
                Default is true.
     *      int range_in_days - maximum number of days in the future/past to make the 
     *          random date. Default is 100.
     *      string format - a PHP date() format. Default is 'Y-m-d'.
     * @return string - a random date as a string.
     */
    public function getRandomData($params = array())
    {
        if (isset($params['future'])) {
            $this->future = (bool)$params['future'];
        }
        
        if ($this->future == false) {
            $this->multiplier = -1;
        }
        
        if (isset($params['range_in_days'])) {
            $this->range_in_days = (int)$params['range_in_days'];
        }
        
        if (isset($params['format'])) {
            $this->format = $params['format'];
        }
        
        $now = time();
        $dayStep = 86400;
        $seconds = (rand(1, $this->range_in_days) * $this->multiplier) * $dayStep;
        $randomTimestamp = $now + $seconds;
        return date($this->format, $randomTimestamp);
    }
}
