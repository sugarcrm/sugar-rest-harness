<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UninstallPackage extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $packageID = $options['package_id'] ?? '';
        $this->config['method'] = 'GET';
        $this->config['route'] = "/Administration/packages/$packageID/uninstall";
        parent::__construct($options);
    }


    public function getDescriptor()
    {
        if (!isset($this->config['descriptor'])) {
            $this->config['descriptor'] = $this->config['package_id'];
        } else {
            $this->config['descriptor'] .= "({$this->config['package_id']})";
        }
        return "Uninstalled {$this->config['descriptor']}";
    }
}