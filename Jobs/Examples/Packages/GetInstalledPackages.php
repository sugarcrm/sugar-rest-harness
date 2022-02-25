<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class GetInstalledPackages extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['method'] = 'GET';
        $this->config['route'] = '/Administration/packages/installed';
        parent::__construct($options);
    }


    public function getDescriptor()
    {
        return "Found " . count((array) $this->results->packages) . " installed packages";
    }
}