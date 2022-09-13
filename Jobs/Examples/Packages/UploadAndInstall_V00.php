<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UploadAndInstall_V00 extends UploadAndInstallPackages implements \SugarRestHarness\JobInterface
{
    public $zipFiles = [
        'Jobs/Examples/Packages/testZipFiles/install_1_v1.00.zip',
        //'Jobs/Examples/Packages/testZipFiles/install_2_v1.00.zip',
        //'Jobs/Examples/Packages/testZipFiles/install_3_v1.00.zip',
    ];
}
