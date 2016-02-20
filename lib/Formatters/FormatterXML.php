<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

/**
 * FormatterXML
 *
 * Formats job output in XML format. 
 */
class FormatterXML extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    public $fileExtension = 'xml';
    
    /**
     * format()
     *
     * Formats the results of all the jobs that were run as XML. Creates a root xml
     * element, and then appends attributes and children to that root element for
     * every job that was run. Then converts that xml element into a xml string, and
     * returns the string.
     *
     * @return string - job class and data formatted
     */
    public function format()
    {
        $header = '<?xml version="1.0" encoding="UTF-8"?><Jobs/>';
        $jobsNode = new \SimpleXMLElement($header);
        $jobs = $this->repository->getResults();
        $jobsNode->addAttribute('count', count($jobs));
        $jobsNode->addAttribute('j', $this->config['j']);
        foreach ($jobs as $jobObj) {
            $jobXMLNode = $jobsNode->addChild('job');
            $this->formatJob($jobXMLNode, $jobObj);
        }
        
        $formattedJobs = $jobsNode->asXML();
        return "\r\n\n$formattedJobs";
    }
    
    
    /**
     * formatJob()
     *
     * Takes a SimpleXMLElement object as its first argument, and appends attributes
     * and child elements describing the state of the job after completion to that
     * passed in xml node.
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatJob($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        $jobXMLNode = $this->formatID($jobXMLNode, $jobObj);
        $jobXMLNode = $this->formatFileName($jobXMLNode, $jobObj);
        $jobXMLNode = $this->formatJobHTTPData($jobXMLNode, $jobObj);
        $jobXMLNode = $this->formatExceptions($jobXMLNode, $jobObj);
        $jobXMLNode = $this->formatJobExpectations($jobXMLNode, $jobObj);
        return $jobXMLNode;
    }
    
    
    /**
     * formatExceptions()
     *
     * Adds child nodes for any exceptions that occurred during this job's execution.
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatExceptions($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        if (!empty($jobObj->exceptions)) {
            $exceptionsNode = $jobXMLNode->addChild('exceptions');
            foreach ($jobObj->exceptions as $e) {
                $exceptionNode = $exceptionsNode->addChild('exception', $e->getMessage());
                $exceptionNode->addAttribute('class', get_class($e));
                $exceptionNode->addAttribute('file', $e->getFile());
                $exceptionNode->addAttribute('line', $e->getLine());
            }
        }
        return $jobXMLNode;
    }
    
    
    /**
     * formatID()
     *
     * Adds the id attribute to the passed in xml node, and assigns it the value of
     * the job object's id.
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatID($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        $jobXMLNode->addAttribute('id', $jobObj->id);
        return $jobXMLNode;
    }
    
    
    /**
     * formatFileName()
     *
     * Adds the file attribute to the passed in xml node, and assigns it the value of
     * the job's file path based on its namespaced class name (namespaces MUST map
     * to physical directories).
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatFileName($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        $classParts = explode('\\', $jobObj->jobClass);
        $rootNamespace = array_shift($classParts);
        $jobFilePath = implode('/', $classParts) . '.php';
        $jobXMLNode->addAttribute('file', $jobFilePath);
        return $jobXMLNode;
    }
    
    
    /**
     * formatJobHTTPData()
     *
     * Add the http node to the passed-in xml node, and populates that http node with
     * data about the http url and return code, and content length.
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatJobHTTPData($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        $httpNode = $jobXMLNode->addChild('http');
        $httpNode->addAttribute('HTTP_Return_Code', $jobObj->connector->httpReturn['HTTP Return Code']);
        $httpNode->addAttribute('Content-Length', $jobObj->connector->httpReturn['Content-Length']);
        
        if (!empty($jobObj->connector->method) && !empty($jobObj->connector->url)) {
            $url = "{$jobObj->connector->method} {$jobObj->connector->url}";
        } else {
            $url = '';
        }
        
        $httpNode->addAttribute('URL', $url);
        return $jobXMLNode;
    }
    
    
    /**
     * formatJobExpectations()
     *
     * Adds the expectations node to the passed-in xml node, and populates it with
     * child nodes if any expectations were not met.
     *
     * @param SimpleXMLElement $jobXMLNode - an xml node that represents one job.
     * @param JobAbstract $jobObj - a completed job.
     * @return SimpleXMLElement - the xml node with children and attributes added.
     */
    public function formatJobExpectations($jobXMLNode, \SugarRestHarness\JobAbstract $jobObj)
    {
        $expectationsNode = $jobXMLNode->addChild('expectations');
        $expectationsNode->addAttribute('count', count($jobObj->expectationDeltas));
        if ($jobObj->expectationsWereMet()) {
            return $jobXMLNode;
        }
        $failuresNode = $expectationsNode->addChild('failures');
        foreach ($jobObj->expectationDeltas as $delta) {
            if ($delta['status'] != '.') {
                $failuresNode->addChild('failure', $delta['msg']);
            }
        }
        return $jobXMLNode;
    }
}
