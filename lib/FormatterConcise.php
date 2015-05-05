<?php
namespace SugarRestHarness;

/**
 * FormatterConcise
 *
 * This class only outputs 'concise' results: only the return code, errors, and failed
 * expectations.
 */
class FormatterConcise extends FormatterBase implements FormatterInterface
{
    public $headersAndMethods = array(
        'HTTP Data' => 'formatHTTPReturn',
        'Exceptions' => 'formatExceptions',
        'Harness Messages' => 'formatHarnessMessages',
        'Harness Errors' => 'formatHarnessErrors',
        'Expectations' => 'formatExpecationResults',
    );
    /**
     * format()
     *
     * For each job in the repository, it will output the id of the job, the return code,
     * 'Success' if the job succeeded (sent a request and received any response) or 'Failed'.
     * It will also output any error messages or failed expectations.
     *
     * @return string - json string from job(s) results
     */
    public function format()
    {
        $results = $this->repository->getResults();
        $resultsStrings = array();
        foreach ($results as $result) {
            $parts = array();
            $success = $result->connector->httpReturn['HTTP Return Code'] == '200' ? 'Success' : 'Failed';
            $resultsStrings[] = "\n{$result->id} $success";
            foreach ($this->headersAndMethods as $header => $method) {
                $formattedString = trim($this->$method($result));
                if (!empty($formattedString)) {
                    $parts[] = "$formattedString";
                }
            }
            $parts[] = "\n";
            $resultsStrings[] = implode("\n", $parts);
        }
        
        return "\n\n" . implode("\n", $resultsStrings) . "\n";
    }
    
    
    /**
     * formatExpecationResults()
     *
     * Formats the results of comparing a job's expectations to the actual results.
     * If all expectations are met, just returns an empty string so there is no output.
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatExpecationResults(JobAbstract $jobObj)
    {
        $formatted = parent::formatExpecationResults($jobObj);
        if (trim($formatted) == 'All expectations met!') {
            return '';
        }
        return $formatted;
    }
    
    
    
    /**
     * formatHTTPReturn()
     *
     * Formats the harness http return code and message values into newline delimited
     * strings. This generally only includes the return code, content length, url and
     * cURL errors, if any.
     *
     * If the return code is 200, this method only returns the content-length as confirmation
     * of success.
     *
     * @param JobAbstract $jobObj - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatHTTPReturn(JobAbstract $jobObj)
    {
        if ($jobObj->connector->httpReturn['HTTP Return Code'] == '200') {
            return "Content-Length: " . $jobObj->connector->httpReturn['Content-Length'];
        } else {
            return parent::formatHTTPReturn($jobObj);
        }
    }
    
    
    /**
     * formatHarnessMessages()
     *
     * Concatenates the harness messages into a newline delimited string. If there
     * are only 2  harness messages, output is suppressed (assumed successful
     * execution).
     *
     * @param JobAbstract - a JobAbstract object
     * @return string - a formatted string
     */
    public function formatHarnessMessages(JobAbstract $jobObj) {
        if (count($jobObj->connector->errors) == 2) {
            return parent::formatHarnessMessages($jobObj);
        } else {
            return '';
        }
    }
}
