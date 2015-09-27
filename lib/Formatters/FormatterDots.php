<?php
namespace SugarRestHarness\Formatters;

/**
 * FormatterDots
 *
 * This class bases its output on the expecationsDeltas of each job. For every expectation
 * that was met, it outputs a '.', and for every failed expectation it outputs 'F'
 * (users of phpunit should find this format familiar). After the dots and F's, a 
 * report of all failed expectations, grouped by job, will be output to the screen.
 */
class FormatterDots extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    
    /**
     * format()
     *
     * Returns only dots (.) for every expectation was that was met for each job run,
     * and returns a 'F' for every expectation that wasn not met.
     *
     * @return string - job class and data formatted
     */
    public function format()
    {
        $formatted = "\n";
        $results = $this->repository->getResults();
        $count = 0;
        $allJobExpectationsWereMet = true;
        foreach ($results as $job) {
            if (!$job->expectationsWereMet()) {
                $allJobExpectationsWereMet = false;
            }
        }
        
        reset($results);
        
        if ($allJobExpectationsWereMet) {
            $formatted .= "All expectations were met!";
        } else {
            foreach ($results as $job) {
                reset($job->expectationDeltas);
                $formatted .= $this->formatExceptions($job);
                $formatted .= $this->formatExpecationResults($job);
            }
        }
        
        return $formatted;
    }
    
    
    /**
     * flushOutput()
     *
     * prints out the '.' and 'F' strings for each expecation for the passed in job.
     *
     * @param JobAbstract - a JobAbstract object
     * @return void
     */
    public function flushOutput(\SugarRestHarness\JobAbstract $jobObj)
    {
        foreach ($jobObj->expectationDeltas as $delta) {
            print("{$delta['status']}");
        }
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
            return $formatted;
        } 
        
        $formatted = "\nFailed Expectations for Job {$jobObj->id}\n";
        foreach ($jobObj->expectationDeltas as $delta) {
            if ($delta['status'] != '.') {
                $formatted .= "{$delta['msg']}\n";
            }
        }
        
        return $formatted;
    }
    
}
