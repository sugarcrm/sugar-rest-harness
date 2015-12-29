<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

require_once('lib/Formatters/FormatterInterface.php');
/**
 * FormatterBase
 *
 * The FormatterBase abstract class provides the basis for all formatters for data
 * collected by jobs, which are stored in the repository. Specific implementations of
 * this class may overwrite any/all of these methods. But the basic idea is that after
 * all of the harness jobs have been run, an implementation of this class will loop
 * through all of the jobs, extact the data it's interested in, format that data, and
 * output it where you want it to go (stdout is typical, anything is possible).
 */
abstract class FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    public $headersAndMethods = array(
        'HTTP Data' => 'formatHTTPReturn',
        'Harness Messages' => 'formatHarnessMessages',
        'Harness Errors' => 'formatHarnessErrors',
        'Exceptions' => 'formatExceptions',
        'Returned Data' => 'formatResults',
        'Expectations' => 'formatExpecationResults',
    );
    
    public $fileExtension = 'txt';
    
    public function __construct($config)
    {
        $this->repository = \SugarRestHarness\ResultsRepository::getInstance();
        $this->config = $config;
    }
    
    
    /**
     * format()
     *
     * Returns all of the formatted data.
     *
     * @see SugarRestHarness\ResultsRepository
     * @return string - formatted data for all jobs run in this session (all of the
     *  jobs in the repository).
     */
    public function format()
    {
        $results = $this->repository->getResults();
        $resultsStrings = array();
        
        foreach ($results as $result) {
            $resultsStrings[] = "\n{$result->id}\n";
            $parts = array();
            foreach ($this->headersAndMethods as $header => $method) {
                $formattedString = trim($this->$method($result));
                if (!empty($formattedString)) {
                    $parts[] = "  ** $header **\n$formattedString\n\n";
                }
            }
            $resultsStrings[] = implode("\n", $parts);
        }
        
        return implode("\n", $resultsStrings);
    }
    
    
    /**
     * formatResults()
     *
     * Formats the data returned from the REST request the job ran.
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatResults(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = var_export($jobObj->results, true);
        
        return $formatted;
    }
    
    
    /**
     * formatExpecationResults()
     *
     * Formats the results of comparing a job's expectations to the actual results.
     * The resulting string will either be "All expectations met" or a list of 
     * expectations that were not met, which includes the name of the property that
     * did not meet expectations along with the exepcted value and the actual value.
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatExpecationResults(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = '';
        if ($jobObj->expectationsWereMet()) {
            return "All expectations met!\n";
        } 
        $formatted = "\nFailed Expectations for Job {$jobObj->id}\n";
        foreach ($jobObj->expectationDeltas as $delta) {
            if ($delta['status'] != '.') {
                $formatted .= "{$delta['msg']}\n";
            }
        }
        
        return trim($formatted);
    }
    
    
    /**
     * formatHarnessMessages()
     *
     * Concatenates the harness messages into a newline delimited string.
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatHarnessMessages(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = implode("\n", $jobObj->connector->msgs);
        return $formatted;
    }
    
    
    /**
     * formatHarnessErrors()
     *
     * Concatenates the harness error messages into a newline delimited string.
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatHarnessErrors(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = implode("\n", $jobObj->connector->errors);
        return $formatted;
    }
    
    
    /**
     * formatHTTPReturn()
     *
     * Formats the harness http return code and message values into newline delimited
     * strings. This generally only includes the return code, content length, url and
     * cURL errors, if any.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatHTTPReturn(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = '';
        foreach ($jobObj->connector->httpReturn as $name => $value) {
            $formatted .= "$name: $value\n";
        }
        return $formatted;
    }
    
    
    /**
     * formatExceptions()
     *
     * Formats the output from any exceptions on this job.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatExceptions(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = '';
        if (count($jobObj->exceptions) == 0) {
            return $formatted;
        }
        
        foreach ($jobObj->exceptions as $e) {
            $formatted .= $e->getFormattedOutput();
        }
        
        return $formatted;
    }
    
    
    /**
     * flushOutput()
     *
     * Output to print directly to stdout immediately after job execution. Most of the
     * methods in this class are expected to run after jobs have completed and are in
     * the repository. This method can take a job object directly, format whatever
     * output the specific formatter requires, and print it immediately after job 
     * execution.
     *
     * NOTE: formatters may reasonably choose to do nothing in this method.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @return void.
     */
    public function flushOutput(\SugarRestHarness\JobAbstract $jobObj)
    {
        $output = "finished job {$jobObj->id}\r";
        $clear = str_repeat(" ", strlen($output) + 50);
        echo "\r$clear\r";
        echo $output;
    }
    
    
    /**
     * getFileExtension()
     *
     * Returns the file extension that should be used if this formatter's output
     * is writtent to a log file. Formatters may specify any extension they like.
     * The default is txt.
     *
     * Extensions should not include the '.' character.
     *
     * @return string - the file extension that should be used when this formatter's
     *  output is written to a file.
     */
    public function getFileExtension()
    {
        if (!empty($this->fileExtension)) {
            return $this->fileExtension;
        }
        return 'txt';
    }
}
