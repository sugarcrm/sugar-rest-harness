<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

use SugarRestHarness\MissingPackageFileHash;
use SugarRestHarness\PackageInstallFailed;

class InstallPackage extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $fileHash = $options['package_file_hash'] ?? '';
        $this->config['method'] = 'GET';
        $this->config['route'] = "/Administration/packages/$fileHash/install";
        parent::__construct($options);
    }


    public function storeException($exception)
    {
        parent::storeException(new PackageInstallFailed($this->config['package_name'], $exception));
        parent::storeException($exception);
    }


    public function getDescriptor()
    {
        if (!isset($this->config['package_name'])) {
            $this->config['descriptor'] = $this->config['package_file_hash'];
        } else {
            $this->config['descriptor'] .= "{$this->config['package_name']} ({$this->config['package_file_hash']})";
        }
        return "Installed {$this->config['descriptor']}";
    }
}
