<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UninstallAndDeleteAll extends \SugarRestHarness\JobSeries
{
    public $installedPackages = [];
    public $stagedPackages = [];

    public function run()
    {
        $this->getInstalledPackages();
        $this->uninstallAllPackages();
        $this->getStagedPackages();
        $this->deleteAllPackages();
    }

    public function deleteAllPackages()
    {
        foreach ($this->stagedPackages as $stagedPackageObj) {
            $this->processOptions(['package_unfile_hash' => $stagedPackageObj->unFile]);
            $this->runJob('Jobs/Examples/Packages/DeletePackage.php');
        }
    }

    public function uninstallAllPackages()
    {
        foreach ($this->installedPackages as $package) {
            $this->processOptions(['package_id' => $package->id]);
            $this->runJob('Jobs/Examples/Packages/UninstallPackage.php');
        }
    }

    public function getInstalledPackages()
    {
        $this->processOptions(['outputFormat' => 'Concise']);
        $installedPackagesJob = $this->runJob('Jobs/Examples/Packages/GetInstalledPackages.php');
        $this->installedPackages = $installedPackagesJob->results->packages;
    }


    public function getStagedPackages()
    {
        $this->processOptions(['outputFormat' => 'Concise']);
        $stagedPackagesJob = $this->runJob('Jobs/Examples/Packages/GetStagedPackages.php');
        $this->stagedPackages = $stagedPackagesJob->results->packages;
    }
}