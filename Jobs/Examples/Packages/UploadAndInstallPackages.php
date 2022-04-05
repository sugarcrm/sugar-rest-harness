<?php

namespace SugarRestHarness\Jobs\Examples\Packages;

class UploadAndInstallPackages extends \SugarRestHarness\JobSeries
{
    public $packages = [];
    public $packagesToUpload = [];
    public $packagesToInstall = [];
    public $packagesToDelete = [];
    public $packagesToUninstall = [];
    public $installedPackages = [];
    public $stagedPackages = [];
    public $zipTmpDir = "zip";
    public $zipFiles = [
        'Jobs/Examples/Packages/testZipFiles/install1.zip',
        'Jobs/Examples/Packages/testZipFiles/install2.zip',
        'Jobs/Examples/Packages/testZipFiles/install3.zip',
        'Jobs/Examples/Packages/testZipFiles/install4.zip',
        'Jobs/Examples/Packages/testZipFiles/install5.zip',
    ];

    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function run()
    {
        // unzip each package file to parse the manifest file, which will contain the package name
        // and package version we want to install.
        $this->parseZipFiles();
        $this->getInstalledPackages();
        $this->getStagedPackages();
        $this->queuePackages();
        $this->uninstallCurrentVersions();
        $this->uploadPackages();
        $this->installPackages();
    }


    public function getInstalledPackages()
    {
        $installedPackagesJob = $this->runJob('Jobs/Examples/Packages/GetInstalledPackages.php');
        $this->installedPackages = $installedPackagesJob->results->packages;
    }


    public function getStagedPackages()
    {
        $stagedPackagesJob = $this->runJob('Jobs/Examples/Packages/GetStagedPackages.php');
        $this->stagedPackages = $stagedPackagesJob->results->packages;
    }


    public function queuePackages()
    {
        foreach ($this->packages as $packageName => $packageData) {
            $this->queuePackageForUploadOrInstall($packageName, $packageData);
            $this->queuePackagesForUninstall($packageName, $packageData);
        }
    }


    public function queuePackageForUploadOrInstall($packageName, $packageData)
    {
        $packageInstallHash = $this->packageIsStaged($packageName, $packageData);
        if ($packageInstallHash) {
            $this->packagesToInstall[$packageName] = $packageInstallHash;
        } else {
            if (!$this->packageIsInstalled($packageName, $packageData)) {
                $this->packagesToUpload[] = $packageName;
            }
        }
    }


    public function packageIsStaged($packageName, $packageData)
    {
        $packageIsStaged = false;
        foreach ($this->stagedPackages as $stagedPackageObj) {
            if ($stagedPackageObj->name == $packageName) {
                if ($stagedPackageObj->version == $packageData['version']) {
                    $packageIsStaged = $stagedPackageObj->file_install;
                }
                break;
            }
        }
        return $packageIsStaged;
    }


    public function packageIsInstalled($packageName, $packageData)
    {
        $packageIsInstalled = false;
        foreach ($this->installedPackages as $installedPackageObj) {
            if ($installedPackageObj->name == $packageName) {
                if ($installedPackageObj->version == $packageData['version']) {
                    $packageIsInstalled = true;
                    break;
                }
            }
        }
        return $packageIsInstalled;
    }


    public function queuePackagesForUninstall($packageName, $packageData) {
        $this->packagesToUninstall = array_merge(
            $this->packagesToUninstall,
            $this->getOtherPackageVersionsToUninstall($packageName, $packageData)
        );
    }

    public function getOtherPackageVersionsToUninstall($packageName, $packageData)
    {
        $differentVersionIsInstalled = [];
        foreach ($this->installedPackages as $installedPackageObj) {
            if ($installedPackageObj->name == $packageName) {
                if ($installedPackageObj->version != $packageData['version']) {
                    // un-install current version.
                    $differentVersionIsInstalled[] = $installedPackageObj->id;
                }
            }
        }
        return $differentVersionIsInstalled;
    }


    public function uninstallCurrentVersions()
    {
        foreach ($this->packagesToUninstall as $packageID) {
            $this->processOptions(['package_id' => $packageID]);
            $this->runJob('Jobs/Examples/Packages/UninstallPackage.php');
        }
    }


    public function uploadPackages()
    {
        foreach ($this->packages as $packageName => $packageData) {
            if (!in_array($packageName, $this->packagesToUpload)) {
                continue;
            }
            $this->processOptions(['post' => ['upgrade_zip' => $packageData['file_path']]]);
            $uploadJob = $this->runJob('Jobs/Examples/Packages/UploadPackage.php');

            if (!$uploadJob || !empty($uploadJob->exceptions)) {
                continue;
            }
            $this->packagesToInstall[$packageName] = $uploadJob->results->file_install;
        }
    }


    public function installPackages()
    {
        foreach ($this->packages as $packageName => $packageData) {
            if (!isset($this->packagesToInstall[$packageName])) {
                continue;
            }

            $this->processOptions(
                [
                    'package_file_hash' => $this->packagesToInstall[$packageName],
                    'package_name' => $packageName,
                ]
            );
            $this->runJob('Jobs/Examples/Packages/InstallPackage.php');
        }
    }


    public function parseZipFiles()
    {
        if (!is_dir($this->zipTmpDir)) {
            mkdir($this->zipTmpDir);
        }

        foreach ($this->zipFiles as $filePath) {
            $fileName = pathinfo($filePath, PATHINFO_BASENAME);
            copy($filePath, "zip/$fileName");
            chdir($this->zipTmpDir);
            exec("unzip $fileName", $output, $result);
            require("manifest.php");

            if (isset($manifest)) {
                $this->packages[$manifest['name']] = [
                    'version' => $manifest['version'],
                    'file_path' => $filePath,
                ];
            }

            chdir("../");
            if ($this->cleanOutZipDir($this->zipTmpDir)) {
            }
            unset($manifest);
        }
        rmdir($this->zipTmpDir);
    }


    public function cleanOutZipDir($path)
    {
        $iterator = new \DirectoryIterator($path);
        foreach ( $iterator as $fileinfo ) {
            if($fileinfo->isDot()) {
                continue;
            }

            if($fileinfo->isDir()){
                if($this->cleanOutZipDir($fileinfo->getPathname()))
                    @rmdir($fileinfo->getPathname());
            }

            if($fileinfo->isFile()){
                @unlink($fileinfo->getPathname());
            }
        }
        return true;
    }

}
