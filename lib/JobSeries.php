<?php
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
        require_once($jobClassFilePath);
        $jobClassName = \SugarRestHarness\Harness::getNamespacedClassName($jobClassFilePath);
        $job = new $jobClassName($this->options);
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
}
