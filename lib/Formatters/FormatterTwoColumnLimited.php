<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

/**
 * FormatterTwoColumnLimited
 *
 * This class formats the data returned by a REST request into two columns, name: value,
 * and prints each pair on a line. Nested arrays are indented. The fields are limited
 * to whatever field names are specified in $this->config['fields'].
 */
class FormatterTwoColumnLimited extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    public $alwaysDisplay = array('records', 'next_offset');
    
    /**
     * format()
     *
     * Returns only the data and the class name for each job that was run. The data
     * will be 'two column' format: 
     *  name: value
     *  name: value
     * ...
     *
     * Only outputs data for fields listed in the 'fields' config property.
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
        $this->setFields($jobObj);
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
    public function recurseData($data, $indent='', $okToDisplay=false)
    {
        $formatted = '';
        
        foreach ($data as $name => $value) {
            if (!$this->isInFields($name) && !$okToDisplay) {
                continue;
            }
            
            if (is_scalar($value)) {
                $formatted .= "{$indent}$name: $value\n";
            } else {
                $formatted .= "{$indent}$name: \n" . $this->recurseData((array) $value, "  $indent", true);
            }
        }
        
        return $formatted;
    }
    
    
    /**
     * setFields()
     *
     * Figures out if any fields were specified, and records those fields to the fields
     * property of this formatter.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @return mixed - array if fields are specified in the job, false otherwise.
     */
    public function setFields(\SugarRestHarness\JobAbstract $jobObj)
    {
        if (IsSet($this->fields)) {
            return $this->fields;
        }
        
        $this->fields = false;
        if (IsSet($jobObj->config['fields'])) {
            $this->fields = $jobObj->config['fields'];
        }
        return $this->fields;
    }
    
    
    /**
     * isInFields()
     *
     * Returns true if a) the passed in field name is specified in the Job's 'fields'
     * config property, b) no fields were specified (show everything), c) the field
     * name is something we always show (like 'records' for list views). Otherwise
     * returns false.
     *
     * @param string $fieldName - the name of field
     * @return bool 
     */
    public function isInFields($fieldName)
    {
        // if no fields are specified, return everything.
        if ($this->fields === false) {
            return true;
        }
        
        if (in_array($fieldName, $this->alwaysDisplay)) {
            return true;
        }
        
        if (in_array($fieldName, $this->fields)) {
            return true;
        }
        return false;
    }
}
