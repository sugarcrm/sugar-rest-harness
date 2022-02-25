<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

use SugarRestHarness\UploadFileNotFound;
use SugarRestHarness\UploadFileNotSpecified;

class UploadPackage extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['method'] = 'PACKAGE';
        $this->config['route'] = '/Administration/packages';

        if (!isset($options['post']['upgrade_zip']) || empty($options['post']['upgrade_zip'])) {
            $this->storeException(new UploadFileNotSpecified('upgrade_zip'));
        }

        if (!file_exists($options['post']['upgrade_zip'])) {
            $this->storeException(new UploadFileNotFound('upgrade_zip', $options['post']['upgrade_zip']));
        }

        $this->config['descriptor'] = $options['post']['upgrade_zip'];
        parent::__construct($options);
    }


    public function getDescriptor()
    {
        if (!isset($this->config['descriptor'])) {
            $this->config['descriptor'] = "a file, but no descriptor provided in config";
        }
        return "Uploaded {$this->config['descriptor']}";
    }
}