<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

/**
 * ExpectationsEngine
 *
 * This class provides the methods for comparing the expected outcomes of a Job
 * (the 'expectations' property) to the actual data.
 *
 * Expectations should be specified in the job file with the name of the property
 * of the job (dot delimited for nested properties), followed by a comparison
 * operator, followed by an expected value. For example:
 *
 * $this->expectations['results.first_name']['empty'] = false;
 * $this->expectations['results']['count'] = 3;
 *
 * NOTE: JobAbstract defines default expectations for all jobs.
 *
 * This class will take a job as its constructor argument, and then run through
 * all of the expectations in the job and determine which expectations were met.
 *
 * All expectation operator methods (i.e. count, contains, equals, etc.) will return
 * a tuple, like this:
 * (
    boolean true if expectation was met, false if not,
    msg describing expectation
   )
 */
class ExpectationsEngine
{
    protected $job;
    protected $exceptions = array();
    protected $loadedExpectationTesters = array();
    
    
    /**
     * __construct()
     *
     * @param JobAbstract $job - the job to compare expectations to results on.
     */
    public function __construct($job)
    {
        $this->setJob($job);
    }
    
    
    /**
     * setJob()
     *
     * @param JobAbstract $job - the job to compare expectations to results on.
     */
    public function setJob($job)
    {
        $this->job = $job;
    }
    
    
    /**
     * loadExpectationTesterClass()
     *
     * Searches for the correct expectation class file and loads it. Throws an
     * Exception if it can't find the file or if the file doesn't define the
     * necessary class. Returns an instantiation of the required expectation
     * tester class.
     *
     * @param string $className - the name of an expectation class.
     * @return ExpectationAbstract - a class that extends the ExpectationAbstract
     *  class.
     */
    public function loadExpectationTesterClass($className)
    {
        $className = ucfirst($className);
        $classFilePath = "lib/Expectations/{$className}.php";
        
        $namespacedClassName = "\\SugarRestHarness\\Expectations\\$className";
        
        if (!in_array($className, $this->loadedExpectationTesters)) {
            if (!file_exists($classFilePath)) {
                throw new ExpectationClassFileNotFound($classFilePath);
            }
            
            require_once($classFilePath);
            $this->loadedExpectationTesters[] = $className;
        
            if (!class_exists($namespacedClassName)) {
                throw new ExpectationClassNotDefined($className, $classFilePath);
            }
        }
        
        $tester = new $namespacedClassName();
        return $tester;
    }
    
    
    /**
     * compareActualToExpected()
     *
     * Compares the expecations specified in the job with the actual data for that
     * Job. Stores the results on the expectations in the Job object.
     */
    public function compareActualToExpected()
    {
        foreach ($this->job->expectations as $fieldName => $expectations) {
            $actualValue = $this->job->get($fieldName);
            foreach ($expectations as $operator => $expectedValue) {
                try {
                    $expectationTester = $this->loadExpectationTesterClass($operator);
                    list($met, $msg) = $expectationTester->test($fieldName, $actualValue, $expectedValue);
                } catch (Exception $e) {
                    $this->storeException($e);
                    continue;
                }
                // for success or failure, add the expectation results.
                $this->job->addExpectationDelta($met, $msg);
            }
        }
    }
    
    
    /**
     * storeException()
     *
     * Stores an exception object in the exceptions array for future reference.
     *
     * @param Exception $e - an exception object.
     */
    public function storeException($e)
    {
        $this->job->storeException($e);
    }
    
    
    /**
     * getExceptions()
     *
     * Returns the array of all exceptions objects.
     *
     * @return array - array of Exceptions.
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
}
