<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

/**
 * FormatterRaw
 *
 * This class only outputs the raw json text returned by the REST request for each job.
 */
class FormatterRaw extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    public $fileExtension = 'json';
    /**
     * format()
     *
     * For each job in the repository, it will output the id of the job and the raw
     * results, unformatted JSON, which is what the REST request returns.
     *
     * @return string - json string from job(s) results
     */
    public function format()
    {
        $results = $this->repository->getResults();
        $resultsStrings = array();
        foreach ($results as $result) {
            $resultsStrings[] = "\n{$result->id}\n";
            if (IsSet($this->config['pretty']) && $this->config['pretty']) {
                $resultsStrings[] = json_encode(json_decode($result->rawResults), JSON_PRETTY_PRINT);
            } else {
                $resultsStrings[] = $result->rawResults;
            }
        }
        
        return implode("\n", $resultsStrings);
    }
}
