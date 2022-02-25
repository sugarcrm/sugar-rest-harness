<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class DeletePackage extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $unFileHash = $options['package_unfile_hash'] ?? '';
        $this->config['method'] = 'DELETE';
        $this->config['route'] = "/Administration/packages/$unFileHash";
        parent::__construct($options);
    }


    public function getDescriptor()
    {
        if (!isset($this->config['descriptor'])) {
            $this->config['descriptor'] = $this->config['package_unfile_hash'];
        } else {
            $this->config['descriptor'] .= "({$this->config['package_unfile_hash']})";
        }
        return "Deleted package {$this->config['descriptor']}";
    }
}
