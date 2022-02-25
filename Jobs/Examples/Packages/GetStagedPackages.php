<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class GetStagedPackages extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['method'] = 'GET';
        $this->config['route'] = '/Administration/packages/staged';
        parent::__construct($options);
    }


    public function getDescriptor()
    {
        return "Found " . count($this->results->packages) . " staged packaged";
    }
}