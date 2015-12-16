<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

/**
 * ResultsRepository
 *
 * The ResultsRepository is a singleton class.
 *
 * The results repository is a class used to store Job objects after they have been
 * run. Jobs that have been run are expected to have non-empty results properties and
 * possibly expectations.
 *
 * The repository also has a formatter property. This is any object that implements
 * the FormatterInterface and extends FormatterBase. The formatter will format the
 * results of the jobs for output, storage, whatever.
 */
class ResultsRepository
{
    public $repository = array();
    public $formatter = null;
    
    
    protected function __construct()
    {
    }
    
    
    /**
     * getInstance()
     *
     * Returns the one-and-only instance of this class.
     *
     * @return ResultsRepository - the instance of this Singleton class.
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new ResultsRepository();
        }
        return $instance;
    }
    
    
    /**
     * addResult()
     *
     * Adds a Job object to the repository.
     *
     * @param JobAbstract - a JobAbstract object (presumably after it's been run).
     * @return int - the index for this result in the repository. May be used to 
     *  retrieve the job again from the repository.
     */
    public function addResult(JobAbstract $job)
    {
        $index = count($this->repository);
        $job->id = "{$job->jobClass}_$index";
        $this->repository[] = $job;
        
        if ($this->formatter) {
            $this->formatter->flushOutput($job);
        }
        
        return $index;
    }
    
    
    /**
     * getResult()
     *
     * Gets a job previously stored in the repository by its index number in the
     * repository.
     *
     * @param int $index - the index of the result in the repository.
     * @return JobAbstract - a JobAbstract object.
     */
    public function getResult($index)
    {
        if (IsSet($this->repository[$index])) {
            return $this->repository[$index];
        } else {
            return null;
        }
    }
    
    
    /**
     * getResults()
     *
     * Returns all Job objects stored in repository.
     *
     * @return array - a numerically indexed array of JobAbstract objects.
     */
    public function getResults()
    {
        return $this->repository;
    }
    
    
    /**
     * setFormatter()
     *
     * Adds a formatter object to the repository so that it can produce output for 
     * individual jobs when they're stored (which happens after the job runs).
     *
     * @param FormatterBase $formatter - A formatter object.
     */
    public function setFormatter(\SugarRestHarness\Formatters\FormatterBase $formatter)
    {
        $this->formatter = $formatter;
    }
}
