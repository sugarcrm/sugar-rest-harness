<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

/**
 * JobSeries
 *
 * This abstract class allows you to run multiple jobs in one call (a "series" of jobs)
 * within the same scope. The idea is that your specific implementation of JobSeries
 * will define a run() method. In that method, you can run jobs with the runJob()
 * method, store those results, and then pass specific values from that job's results
 * to another job. Or loop over one job's results, and run another job for every result
 * from the first job. You can perform any operations you like in run(). Here is a 
 * simple example:
 *
 * $results = $this->runJob('Jobs/Contacts/Search.php');
 * foreach ($results->results->records as $record) {
 *     $options = array(
 *        'bean_id' => $record->id,
 *        'post' => array(
 *            'title' => 'Skillful Coder',
 *         ),
 *     );
 *     $this->processOptions($options);
 *     $this->runJob('Jobs/Contacts/Update.php');
 * }
 * 
 */
abstract class JobSeries implements JobInterface
{
    public $results = array();
    public $options = array();
    public $config = null;
    public $expectations = array();
    
    /**
     * __construct()
     *
     * @param array $options - command line options
     */
    public function __construct($options)
    {
        $this->config = \SugarRestHarness\Config::getInstance()->getHarnessConfig();
        $this->processOptions($options);
    }
    
    
    /**
     * processOptions()
     *
     * Command line options are passed in to this class like they would be for a 
     * normal, single job. However, this class will pass any cli options it receives
     * to every job it runs.
     *
     * @param array $options - config options to be passed to all jobs run in this
     *  series.
     * @return void.
     */
    public function processOptions(array $options)
    {
        $this->options = \SugarRestHarness\Config::getInstance()->mergeWithJobConfig($this->config, $options);
    }
    
    
    /**
     * setOptions()
     *
     * Sets a config option to be passed to all jobs this series will run. This allows
     * you to set 'command line options' programatically, adding options that can
     * be based on the results of a previous job or some other calculation.
     *
     * @param string $configName - the name of the config option you want to set.
     * @param mixed $value - the value to want to set.
     * @return void.
     */
    public function setOption($configName, $value)
    {
        $optionNameParts = explode('.', $configName);
        $option = &$this->options;
        foreach ($optionNameParts as $optName) {
            if (!IsSet($option[$optName])) {
                $option[$optName] = array();
            }
            $option = &$option[$optName];
        }
        $option = $value;
    }
    
    
    /**
     * clearOptions()
     * 
     * Resets the options array to be only the original values from the harness, effectively
     * clearing away any job-specific options.
     */
    public function clearOptions()
    {
        $this->options = \SugarRestHarness\Config::getInstance()->getHarnessConfig();
    }
    
    
    /**
     * setExpectation()
     *
     * Sets an expectation for the next job to be run. Expectations will be reset after
     * every job.
     *
     * @param string $expectationName - the name the expectation, which should
     *  be a dot-delimited path to a property of this job object, i.e. results.first_name,
     *  or results.records.0.description, or connector.errors
     * @param string $operator - the expectation operator. @see lib/Exptations for
     *  valid operators.
     * @param mixed $expectedValue - what you expect the property named by $expectationName
     *  to be set to.
     */
    public function setExpectation($expectationName, $operator, $expectedValue)
    {
        $this->expectations[$expectationName][$operator] = $expectedValue;
    }
    
    
    /**
     * setExpectedHTTPReturnCode()
     *
     * Sets the expected return code on the job to be run. If the request that job's connector
     * sends returns an http return code value that is different from what you set here, a
     * ServerError exception will be thrown in the connector's sendRequest() method.
     *
     * The default value is '200'.
     *
     * Like any other expectation, this one will be reset after every job the job
     * series executes.
     *
     * @param string $code - the expected return code, i.e. 200, 404, 500, etc.
     */
    public function setExpectedHTTPReturnCode($code)
    {
        $this->expectations['connector.httpReturn.HTTP Return Code']['equals'] = $code;
    }
    
    
    /**
     * clearExpectatations()
     *
     * Resets the expectations for th
     */
    public function clearExpectatations()
    {
        $this->expectations = array();
    }
    
    
    /**
     * transferExpectations()
     *
     * Transfers any expectations set on this job series from the job series to the
     * job passed into this method. Then the expectations on this series are reset,
     * so expectations will not be passed to subsequent jobs.
     *
     * @param JobAbstract $jobObject - a job to set expectations on.
     */
    public function transferExpectations(&$jobObject)
    {
        foreach ($this->expectations as $expectationName => $operatorExpectedValuePair) {
            foreach ($operatorExpectedValuePair as $operator => $expectedValue) {
                $jobObject->setExpectation($expectationName, $operator, $expectedValue);
            }
        }
        $this->clearExpectatations();
    }
    
    
    /**
     * runJob()
     *
     * This function will instantiate the passed-in $jobClassName and call its run()
     * method, and store the results in the $results array.
     *
     * @param string $jobClassFilePath - The path to a job class file, relative to 
     *  the Jobs directory.
     * @return array - a hash of the results of the job.
     */
    public function runJob($jobClassFilePath)
    {
        $jobClassName = \SugarRestHarness\Harness::getNamespacedClassName($jobClassFilePath);
        $job = new $jobClassName($this->options);
        $this->transferExpectations($job);
        $results = $job->run();
        $this->results[] = $results;
        return $job;
    }
    
    
    /**
     * run()
     *
     * Must be overridden by specific implemtations of this class. In specific implementations,
     * you can run a job, collect its results, and pass those results to another job,
     * or run jobs in a loop based on results from one job - or anything else that
     * meets your requirements.
     */
    public function run()
    {
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
