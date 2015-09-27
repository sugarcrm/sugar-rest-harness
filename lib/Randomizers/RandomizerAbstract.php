<?php
namespace SugarRestHarness\Randomizers;

require_once('Jobs/Generic.php');

/**
 * RandomizerAbstract
 *
 * This class defines the basic behavior for all randomizers. Randomizer's return
 * a random piece of data from a set of possible valid values. What the set contains
 * is up to each specific randomizer to define, as is how to specifically select
 * and format the random data.
 *
 * All randomizers must extend this class and implement the RandomizerInterface.
 */
abstract class RandomizerAbstract implements RandomizerInterface
{
    private $list = array();
    protected static $instances = array();
    
    protected function __construct()
    {
    }
    

    /**
     * Returns the singleton for this class.
     *
     * @return RandomizerFactory
     */    
    public static function getInstance()
    {
        $className = get_called_class();
        if (empty(self::$instances[$className])) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className]; 
    }
    
    
    /**
     * Returns whatever random data a specific randomizer should return.
     *
     * @param array - an array of additional data a specific randomizer may need
     *  to use while selecting random data. Specific randomizers may ignore this
     *  arg, or may require it, or may consider it optional. Consult the specific
     *  randomizer class to see what it expects for params.
     * @return mixed - whatever the specific randomizer class returns.
     */
    public function getRandomData($params)
    {
        if (empty($this->list)) {
            return '';
        }
        $index = rand(0, (count($this->list) - 1));
        return $this->list[$index];
    }
    
    
    private function __clone()
    {
    }
    
    private function __wakeup()
    {
    }
}