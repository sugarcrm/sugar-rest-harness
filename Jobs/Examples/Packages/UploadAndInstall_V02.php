<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UploadAndInstall_V02 extends UploadAndInstallPackages implements \SugarRestHarness\JobInterface
{
    public $zipFiles = [
        'Jobs/Examples/Packages/testZipFiles/install_2_v1.02.zip',
        'Jobs/Examples/Packages/testZipFiles/install_3_v1.02.zip',
    ];
}
