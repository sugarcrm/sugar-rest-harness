<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UploadAndInstall_V01 extends UploadAndInstallPackages implements \SugarRestHarness\JobInterface
{
    public $zipFiles = [
        'Jobs/Examples/Packages/testZipFiles/install_2_v1.01.zip',
        'Jobs/Examples/Packages/testZipFiles/install_3_v1.01.zip',
    ];
}
