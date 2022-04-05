<?php

namespace SugarRestHarness\Formatters;

class FormatterPackage extends FormatterBase implements FormatterInterface
{
    public function format()
    {
        $allJobsSucceeded = true;
        foreach ($this->repository->getResults() as $job) {
            if ($job->connector->httpReturn['HTTP Return Code'] != '200') {
                $allJobsSucceeded = false;
            }
        }

        if ($allJobsSucceeded) {
            return "\nAll requests finished successfully";
        }

        return "\nSome requests failed - check results above";
    }

    public function formatResults(\SugarRestHarness\JobAbstract $jobObj)
    {
        return '';
    }

    public function flushOutput(\SugarRestHarness\JobAbstract $jobObj)
    {
        if (count($jobObj->exceptions) > 0) {
            $exceptions = $this->formatExceptions($jobObj);
        }

        if (method_exists($jobObj, 'getDescriptor')) {
            $descriptor = $jobObj->getDescriptor();
        }

        if ($jobObj->connector->httpReturn['HTTP Return Code'] == '200') {
            $output = get_class($jobObj) .  " $descriptor HTTP 200";
        } else {
            $output = implode("\n", [
                get_class($jobObj) .  " $descriptor FAILED HTTP " . $jobObj->connector->httpReturn['HTTP Return Code'],
                $exceptions,
            ]);
        }
        echo "\n$output";
    }

    public function formatHTTPReturn(\SugarRestHarness\JobAbstract $jobObj)
    {
        return '';
    }


    public function formatHarnessMessages(\SugarRestHarness\JobAbstract $jobObj)
    {
        return '';
    }


    public function formatHarnessErrors(\SugarRestHarness\JobAbstract $jobObj)
    {
        if (count($jobObj->connector->errors) > 0) {
            return parent::formatHarnessErrors($jobObj);
        }
        return '';
    }


    public function formatExpecationResults(\SugarRestHarness\JobAbstract $jobObj)
    {
        return '';
    }


    public function formatExceptions(\SugarRestHarness\JobAbstract $jobObj)
    {
        $formatted = '';
        if (count($jobObj->exceptions) == 0) {
            return $formatted;
        }

        foreach ($jobObj->exceptions as $e) {
            $formatted .= $e->getMessage();
        }

        return $formatted;
    }
}
