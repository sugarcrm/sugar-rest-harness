<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

/**
 * FormatterTwoColumn
 *
 * This class formats the data returned by a REST request into two columns, name: value,
 * and prints each pair on a line. Nested arrays are indented.
 */
class FormatterTwoColumn extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    
    /**
     * format()
     *
     * Returns only the data and the class name for each job that was run. The data
     * will be 'two column' format: 
     *  name: value
     *  name: value
     * ...
     *
     * @return string - job class and data formatted
     */
    public function format()
    {
        $formatted = '';
        $jobsData = array();
        $results = $this->repository->getResults();
        foreach ($results as $job) {
            $jobsData[] = "\n{$job->id}\n";
            $jobsData[] = $this->formatHTTPReturn($job);
            $jobsData[] = $this->formatResults($job, '  ');
            if (!$job->expectationsWereMet()) {
                $jobsData[] = $this->formatExpecationResults($job);
            }
            $jobsData[] = $this->formatExceptions($job);
        }
        return implode("\n", $jobsData);
    }
    
    
    /**
     * formatResults()
     *
     * Formats the data returned from the REST request the job ran.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @param string $indent - expected whitespace to indent the lines by.
     * @return string - a formatted string
     */
    public function formatResults(\SugarRestHarness\JobAbstract $jobObj, $indent='')
    {
        $formatted = $this->recurseData($jobObj->results, $indent);
        return $formatted;
    }
    
    
    /**
     * recurseData()
     *
     * Recursively interates through the data on the result object and formats it as
     * strings. This is necessary as the data may contain N number of nested arrays
     * of arbitrary depth.
     *
     * @param array $data - an associative array of name/vaue pairs.
     * @param string indent - expected whitespace to indent the lines by.
     * @return string - formatted data array as a string.
     */
    public function recurseData($data, $indent='')
    {
        $formatted = '';
        
        foreach ($data as $name => $value) {
            if (is_scalar($value)) {
                $formatted .= "{$indent}$name: $value\n";
            } else {
                $formatted .= "{$indent}$name: \n" . $this->recurseData((array) $value, "  $indent");
            }
        }
        
        return $formatted;
    }
}
