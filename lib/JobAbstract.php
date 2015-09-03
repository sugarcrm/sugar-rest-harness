<?php
namespace SugarRestHarness;

/**
 * JobAbstract
 *
 * This abstract class provides the basis for all REST requests (jobs) run by the harness.
 * Jobs are expected to set configuration data specific to their REST request (i.e.
 * module, bean_id, fields, post, etc.) in their __construct() methods. JobAbstract 
 * classes merge their configuration data with the other data collected by the config 
 * object, and pass that result to the connector, which makes the actual REST request.
 * The connect passes the results back to the Job object, which stores them in the 
 * repository for later formatting.
 */
abstract class JobAbstract implements JobInterface
{
    public $jobClass = '';
    public $config = array(); /* hash - config values from config file, or from command line options*/
    public $connector = null;
    public $repository = null;
    public $rawResults = '';
    public $results = array();
    public $resultIndex = false;
    public $id = '';
    public $expectationsEngine = null;
    public $expectations = array();
    public $expectationsDeltas = array();
    public $allExpectationsMet = true;
    public $exceptions = array();
    public $randomizer = null;
    
    
    /**
     * __construct()
     *
     * Instantiates the Job object. Merges together all of the config values.
     *
     * Config values have 3 sources (in order of what is overwritten by what):
     * 1) a config file (specified in $this->configFileName)
     * 2) values specified in a Job class file as $this->config['foo'] = 'bar';
     * 3) values specified on the command line, i.e. --foo=bar
     *
     * Config controls most of this application's functionality, from specifying the 
     * the url of the rest server, to listing which fields should be included in the
     * response.
     *
     * @param array $options - configuration options passed from the command line.
     */
    public function __construct($options)
    {
        $this->config = \SugarRestHarness\Config::getInstance()->mergeWithJobConfig($this->config, $options);
        $this->jobClass = get_class($this);
        $this->config['jobClass'] = $this->jobClass;
        $this->connector = new \SugarRestHarness\RestConnector($this->config);
        $this->repository = ResultsRepository::getInstance();
        
        if (!empty($this->config['verbose'])) {
            $this->connector->verbose($this->config['verbose']);
        }
        $this->expectationsEngine = new ExpectationsEngine($this);
        $this->expectations['connector.httpReturn.HTTP Return Code']['equals'] = '200';
        $this->expectations['connector.errors']['count'] = 0;
    }
    
    
    /**
     * storeJob()
     *
     * Stores this job in the ResultsReposity for later formatting.
     *
     * @return int - the index of the result in the repository.
     */
    public function storeJob()
    {
        $this->resultIndex = $this->repository->addResult($this);
        return $this->resultIndex;
    }
    
    
    /**
     * run()
     *
     * Executes the method in the mobile harness as specified by the job (which is a
     * class name passed in on the comand line and corresponds to a class file) and
     * stores the result in the result repository.
     *
     * @see \SugarRestHarness\ResultRepository
     * @return void
     */
    public function run()
    {
        try {
            $this->rawResults = $this->connector->makeRequest();
        } catch (\SugarRestHarness\Exception $e) {
            $this->storeException($e);
        }
        
        $this->results = json_decode($this->rawResults);
        
        if (!is_object($this->results)) {
            $this->results = array();
        }
        $this->expectationsEngine->compareActualToExpected();
        $this->storeJob();
    }
     
    
    /**
     * get()
     *
     * Returns the value of a property on this object based on the passed in name. For
     * nested objects/arrays, you can pass in a '.' delimted list for the name, like 
     * this:
     *
     * $obj->get('data.records.3.id');
     *
     * This would return, for example, 'contact3', assuming that path lead to a valid
     * property/index.
     *
     * @param string $name - name of the property to get.
     * @return mixed - the value of the requested property, or null if the property
     *  does not exist.
     */
    public function get($name)
    {
        $propNameList = explode('.', $name);
        $prop = &$this;
        foreach ($propNameList as $propName) {
            if (is_object($prop)) {
                if (IsSet($prop->$propName)) {
                    $prop = &$prop->$propName;
                } else {
                    return null;
                }
            } elseif (is_array($prop)) {
                if (IsSet($prop[$propName])) {
                    $prop = &$prop[$propName];
                } else {
                    return null;
                }
            }
        }
        return $prop;
    }
    
    
    /**
     * addExpectationDelta()
     *
     * Adds an entry to the expectationDeltas array.
     *
     * @param bool $expectationMet - true for an expectation that matched actual 
     *  results, false otherwise.
     * @param string $msg - a message describing the expectation results.
     */
    public function addExpectationDelta($expectationMet, $msg)
    {
        if (!$expectationMet) {
            $this->allExpectationsMet = false;
        }
        
        $status = $expectationMet == true ? '.' : 'F';
        $this->expectationDeltas[] = array(
            'status' => $status,
            'msg' => $msg,
        );
    }
    
    
    /**
     * expectationsWereMet()
     *
     * Returns true if all expectations were met, false if they weren't.
     *
     * @return bool - true if all expectations were met.
     */
    public function expectationsWereMet()
    {
        return $this->allExpectationsMet;
    }
    
    
    /**
     * storeException()
     *
     * Stores an exception object in the exceptions array for future referenece
     * by the formatter class.
     *
     * If the application is going to throw an exception and you want a Formatter
     * class to display/format it, it must be passed to a JobAbstract object
     * via this method.
     *
     * @param \SugarRestHarness\Exception $exeption - an exception thrown during
     *  this job.
     */
    public function storeException($exception)
    {
        $this->exceptions[] = $exception;
    }
    
    
    /**
     * randomize()
     *
     * Generates a random value and returns it. Random values must be supported by
     * the RandomizerFactory class and the Randomizer classes it uses.
     *
     * The arguments for randomize must include a $type, which will map to a class
     * that extends the RandomizerAbstract class and implements the RandomizerInterface.
     *
     * The arguments can also optionally include additional information a specific
     * randomizer will require. See the docs for specific randomizers to see what
     * additional info they require.
     *
     * @param string $type - The type of random data.
     * @param array $params - a hash of optional additional arguments.
     */
    public function randomize($type, $params = array())
    {
        $randomDataManager = \SugarRestHarness\RandomizerFactory::getInstance();
        try {
            $randomizer = $randomDataManager->loadRandomizer($type);
            return $randomizer->getRandomData($params);
        } catch (\SugarRestHarness\Exception $e) {
            $this->storeException($e);
            return '';
        }
    }
}
