<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

/**
 * JobWriter
 *
 * The JobWriter class's purpose is to create a Job class file based on the current
 * config settings of the current job. This allows the user to save a job they have
 * modified with command line options.
 *
 * The JobWriter will only be triggered when the -w argument is passed in.
 *
 * JobFiles will be created in the directory the user passes in with the -j argument,
 * i.e. -j SCJobs/Contacts/Detail.php -w will produce a new file in the SCJobs/Contacts/
 * directory. The new job file will have the same namespace as the original job file.
 *
 * -w can optionally be called with a file name. If present, the JobWriter will name
 * the new file whatever the user passed in, overwriting any existing file. If no
 * name is passed in, the JobWriter will generate a random name.
 */
class JobWriter
{
    public $className = "";
    public $jobNamespace = "";
    public $jobFileName = "";
    public $config = null;
    
    public function __construct(JobAbstract $job)
    {
        $this->job = $job;
        $this->config = $job->config;
    }
    
    
    
    /**
     * createJobFile()
     *
     * Calculates the contents of the new job class file and writes them to a new 
     * file, based on the path passed in with -j.
     *
     * @return bool - true if successfully writes the file, false if otherwise.
     */
    public function createJobFile()
    {
        $contents = $this->createJobFileContents();
        try {
            $this->writeFile($contents);
            return true;
        } catch (\SugarRestHarness\Exception $e) {
            $this->job->storeException($e);
            return false;
        }
    }
    
    
    /**
     * createJobFileContents()
     *
     * Creates the contents of the job class file.
     *
     * @return string - all of the lines needed to create the job class file, as one string.
     */
    public function createJobFileContents()
    {
        $className = $this->determineJobClassName();
        $jobNamespace = $this->determineJobNamespace();
        $indent = '    ';
        $filteredConfig = $this->filterConfig();
        $formattedConfig = str_replace("\n", "\n{$indent}{$indent}{$indent}", var_export($filteredConfig, true));
        $lines = array(
            "<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */",
            "namespace $jobNamespace;",
            "class $className extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface",
            "{",
            "{$indent}public function __construct(\$options)",
            "{$indent}{",
            "{$indent}{$indent}\$this->config = " . $formattedConfig . ';',
            "{$indent}{$indent}parent::__construct(\$options);",
            "{$indent}}",
            "}",
        );
        
        return implode("\n", $lines);
    }
    
    
    /**
     * determineJobNamespace()
     *
     * Figures out the job namespace based on the path passed in by the user in -j.
     *
     * @return string - a namespace.
     */
    public function determineJobNamespace()
    {
        $jobDirPath = $this->determinePath();
        $this->jobNamespace = 'SugarRestHarness' . '\\' . str_replace("/", "\\", $jobDirPath);
        return $this->jobNamespace;
    }
    
    
    /**
     * determineJobClassName()
     *
     * Figures out what name to give the new job class. If a name is passed in,
     * use that name. Otherwise, generate a unique name.
     *
     * @return string - a cla
     */
    public function determineJobClassName()
    {
        if (IsSet($this->config['w']) && is_string($this->config['w'])) {
            $this->className = str_replace('.php', '', $this->config['w'] );
        } else {
            $this->className = $this->config['module'] . $this->getUID();
        }
        return $this->className;
    }
    
    
    /**
     * filterConfig()
     *
     * "Filters" the config for the job by removing any config vars that were set in
     * the base config file (the one used by the harness). We don't need to set those
     * values in the job file, they just clutter up the file, so we filter them out.
     * However, if the value for the job is different than the base settings, we should
     * preserve them.
     *
     * @return array - a hash of config values.
     */
    public function filterConfig()
    {
        $filteredConfig = $this->config;
        $baseConfig = \SugarRestHarness\Config::getInstance()->configFileOptions;
        foreach ($filteredConfig as $index => $value) {
            if (IsSet($baseConfig[$index]) && $baseConfig[$index] == $filteredConfig[$index]) {
                unset($filteredConfig[$index]);
            }
        }
        unset($filteredConfig['token']);
        unset($filteredConfig['jobClass']);
        unset($filteredConfig['j']);
        unset($filteredConfig['w']);
        return $filteredConfig;
    }
    
    
    /**
     * determinePath()
     *
     * Figures out which directory the called job file was in. i.e. -j Jobs/Contacts/Detail.php
     * would return Jobs/Contacts
     *
     * @return string - a path to a directory.
     */
    public function determinePath()
    {
        $jobFilePath = $this->config['j'];
        $jobDirPath = pathinfo($jobFilePath, PATHINFO_DIRNAME);
        return $jobDirPath;
    }
    
    
    /**
     * determineJobFileName()
     *
     * Determines what name to use for the new job class file. It's either the value
     * passed in with -w or the module of the job + a random string.
     */
    public function determineJobFileName()
    {
        if (IsSet($this->config['w']) && is_string($this->config['w'])) {
            $this->jobFileName = $this->config['w'];
            if (pathinfo($this->jobFileName, PATHINFO_EXTENSION) != 'php') {
                $this->jobFileName .= '.php';
            }
        } else {
            $this->jobFileName = $this->determineJobClassName() . '.php';
        }
        
        return $this->jobFileName;
    }
    
    
    /**
     * writeFile()
     *
     * Writes the new job file to it's directory. Takes the class file's contents as
     * its argument.
     *
     * @param string $contents - the contents of the class file.
     * @return bool - true
     */
    public function writeFile($contents)
    {
        $fileName = $this->determineJobFileName();
        $filePath = $this->determinePath() . "/$fileName";
        if (is_dir($filePath)) {
            throw new \SugarRestHarness\CannotWriteToDirectory($filePath);
        }
        
        $fh = fopen($filePath, 'w');
        
        if ($fh === false) {
            throw new \SugarRestHarness\CannotWriteToFile($filePath);
        }
        
        $writeOK = fwrite($fh, $contents);
        if ($writeOK === 0 || $writeOK === false) {
            throw new \SugarRestHarness\WriteToFileFailed($filePath);
        }
        print("\nWrote new Job file: $filePath\n");
        fclose($fh);
        return true;
    }
    
    
    /**
     * getUID()
     *
     * returns a unique ID to make a unique class/file name.
     *
     * @return string
     */
    public function getUID()
    {
        return uniqid();
    }
}

