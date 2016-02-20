<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

/**
 * Config
 *
 * This class collects all config data for various other classes, and returns that
 * data as an associative array. There are 2 or 3 sources of config data:
 * 1) A file in the config directory (only one file can be used at a time).
 * 2) The config property of a JobAbstract class.
 * 3) Options specified on the command line.
 *
 * These sources overwrite each other in that order. For the logging into the sugar
 * instance, job-specific configuration data isn't useful or available at that point
 * in execution, so it's not included. 
 * For Jobs, obviously the specific config data is necessary.
 * So, there are two different methods for collecting config data: getHarnessConfig()
 * and getJobConfig().
 */
class Config
{
    /** @var string name of the default config file located in config/ */
    public $defaultConfigFileName = 'job.basic.config.php';
    
    /** @var string short commmand line options */
    public $shortOptions = "w:hj:";
    
    /** @var array long command line options i.e. --long_option=somevalue */
    public $longOptions = array();
    
    /** @var array a combination of the short-form and long-form command line options, 
        i.e. -j path/to/job.php --bean_id=aBeanID
    */
    public $commandLineOptions = array();
    
    /** @var array is all of the options set in the config file. */
    public $configFileOptions = array();
    
    /** @var array all options in the job's config array. */
    public $jobConfigParams = array();
    
    
    /**
     * __construct()
     *
     * This is a singleton class, so the constructor is protected.
     */
    protected function __construct()
    {
        $this->installPath = \SugarRestHarness\Harness::getAbsolutePath();
    }
    
    
    /**
     * getInstance()
     *
     * Returns the singleton instance of this class.
     *
     * @return \SugarRestHarness\Config
     */
    public static function getInstance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new \SugarRestHarness\Config();
        }
        return $inst;
    }
    
    
    /**
     * getJobConfigParam()
     *
     * Returns a value from the config params set in the current job class file.
     *
     * @param string $name - the name of the job config param you want to retrieve.
     * @param mixed $default - a default value to return if no value is set.
     * @return mixed - the value stored or $default
     */
    public function getJobConfigParam($name, $default)
    {
        if (IsSet($this->jobConfigParams[$name])) {
            return $this->jobConfigParams[$name];
        } else {
            return $default;
        }
    }
    
    
    /**
     * getCommandLineOption()
     *
     * returns a value from commandLineOptions.
     *
     * @param string $name - the name of the command line option you want to retrieve.
     * @param mixed $default - a default value to return if no value is set.
     * @return mixed - the value stored or $default
     */
    public function getCommandLineOption($name, $default='')
    {
        if (IsSet($this->commandLineOptions[$name])) {
            return $this->commandLineOptions[$name];
        } else {
            return $default;
        }
    }
    
    
    
    /**
     * getConfigFileOption()
     *
     * returns a value from config file options.
     *
     * @param string $name - the name of the config file option you want to retrieve.
     * @param mixed $default - a default value to return if no value is set.
     * @return mixed - the value stored or $default
     */
    public function getConfigFileOption($name, $default='')
    {
        if (IsSet($this->configFileOptions[$name])) {
            return $this->configFileOptions[$name];
        } else {
            return $default;
        }
    }
    
    
    /**
     * importConfigFile()
     *
     * importConfig imports the contents of the config file specified in $this->configFileName
     * into $this->configFileOptions. The config file
     * essentially fills in anything required by the application that you don't
     * specify in your job class or on the command line.
     *
     * @param string $configFileName - the name of a file int he config directory.
     * @return array - a hash of config file properties.
     */
    public function importConfigFile($configFileName = '')
    {
        if (!empty($this->configFileOptions)) {
            return $this->configFileOptions;
        }
        
        if (empty($configFileName)) {
            $configFileName = $this->defaultConfigFileName;
        }
        
        $configFiles = array(
            // always load the default - if the specified name exists in /config, it
            // will overwrite the default file.
            'default' => "{$this->installPath}/config/{$this->defaultConfigFileName}",
            'specified' => "{$this->installPath}/config/$configFileName",
            'specified_custom' => "{$this->installPath}/custom/config/$configFileName",
            );
        
        $filesFound = array();
        $filesMissing = array();
        foreach ($configFiles as $name => $filePath) {
            if (file_exists($filePath)) {
                $filesFound[$name] = array($filePath);
                require_once($filePath); // must define $config.
                foreach ($config as $name => $value) {
                    $this->configFileOptions[$name] = $value;
                }
            } else {
                $filesMissing[$name] = $filePath;
            }
        }
        
        $requiredConfigFilesLoaded = true;
        
        if (count($filesFound) == 0) {
            $requiredConfigFilesLoaded = false;
        }
        
        if ($configFileName != $this->defaultConfigFileName) {
            if (IsSet($filesMissing['specified']) && IsSet($filesMissing['specified_custom'])) {
                $requiredConfigFilesLoaded = false;
            }
        }
        
        if (!$requiredConfigFilesLoaded) {
            die("Config::importConfig failure - $configFileName does not exist! Searched these locations:\n" . implode("\n", $filesMissing));
        }
        
        if (empty($config)) {
            die("Config::importConfig failure - $configFilePath does not define a \$config array.");
        }
        
        return $this->configFileOptions;
    }
    
    
    
    /**
     * processArgv()
     *
     * Collects all of the options set on the command line and packs them up into a
     * hash suitable for being passed as config options to any other class in the
     * SugarRestHarness application.
     *
     * @return hash - associative array of name => value pairs, comprised of the 
     *  command line options.
     */
    public function processArgv()
    {
        global $argv;
        
        if (!empty($this->commandLineOptions)) {
            return $this->commandLineOptions;
        }
        
        $this->commandLineOptions = getopt($this->shortOptions, $this->longOptions);
        foreach ($argv as $optPair) {
            $option = &$this->commandLineOptions;
            
            if (strpos($optPair, '=') !== false) {
                list($optName, $optValue) = explode('=', $optPair, 2);
            } elseif (strpos($optPair, ' ') !== false) {
                list($optName, $optValue) = explode(' ', $optPair);
            } elseif ($optPair == '-w' && !IsSet($this->commandLineOptions['w'])) {
                $optName = 'w';
                $optValue = false;
            } else {
                continue;
            }
            $optName = str_replace('-', '', $optName);
            
            if (empty($optName)) {continue;}
            
            // special handling for post
            if ($optName == 'post') {
                $option[$optName] = $this->decodeJSONPost($optValue);
                continue;
            }
            
            // special handling for query string (qs)
            if ($optName == 'qs') {
                $option[$optionName] = $this->buildQSArrayFromString($optValue);
                continue;
            }
            
            // special handling for route
            if ($optName == 'route' && strpos($optValue, '?') > 0) {
                list($route, $qs) = explode('?', $optValue);
                $option[$optName] = $route;
                $option['qs'] = $this->buildQSArrayFromString($qs);
                continue;
            }
            
            $optLevels = explode('.', $optName);
            foreach ($optLevels as $level) {
                if (!IsSet($option[$level])) {
                    $option[$level] = array();
                }
                $option = &$option[$level];
            }
            
            if (strpos($optValue, ',') !== false) {
                $optValue = explode(',', $optValue);
            }
            
            $option = $optValue;
        }
        return $this->commandLineOptions;
    }
    
    
    /**
     * decodeJSONPost()
     *
     * If post is passed in via command line as a JSON string, we want to decode that
     * into a hash. Otherwise, we want to keep the raw string passed in.
     *
     * NOTE: the string must be decoded to produce an associative array, NOT
     * standard class objects, to be consistent with the expectations that all
     * job config data is associative arrays.
     *
     * @param string $postString - a (possibly) JSON-encoded string.
     * @return mixed - either the variable described the the JSON or whatever POST was
     *  set to on the command line.
     */
    public function decodeJSONPost($postString)
    {
        $post = json_decode($postString, true);
        if ($post != null) {
            return $post;
        } else {
            return $postString;
        }
    }
    
    
    /**
     * buildQSArrayFromString()
     *
     * Takes a url-encoded query string and turns it into a hash, suitable for
     * our $this->qs array.
     *
     * @param string $qsString - a query string.
     * @return array - a hash of name/value pairs from the string.
     */
    public function buildQSArrayFromString($qsString)
    {
        $qs = array();
        if (strpos($qsString, '?') === 0) {
            $qsString = ltrim($qsString, '?');
        }
        $pairs = explode('&', $qsString);
        foreach ($pairs as $pair) {
            list($name, $value) = explode('=', $pair);
            $qs[$name] = urldecode($value);
        }
        return $qs;
    }
    
    
    /**
     * getJobConfig()
     *
     * The Job config is the amalgamation of the config file, the job's config properties,
     * and the command line options. The job overwrites the config file, the command
     * line options overwrite the job. This function will assemble those pieces with 
     * those rules.
     *
     * @param array $jobConfigHash - the config property from a Job object.
     * @return array - a hash of name/value pairs from the config file, job file and 
     *  command line arguments.
     */
    public function getJobConfig($jobConfigHash)
    {
        $this->jobConfigParams = $jobConfigHash;
        
        // special handling for JSON Post. config['post'] should be a hash.
        if (IsSet($this->jobConfigParams['post']) && is_string($this->jobConfigParams['post'])) {
            $this->jobConfigParams['post'] = $this->decodeJSONPost($this->jobConfigParams['post']);
        }
        
        $this->processArgv();
        $this->importConfigFile($this->getConfigFileName());
        $configParams = $this->merge($this->configFileOptions, $jobConfigHash);
        $configParams = $this->merge($configParams, $this->commandLineOptions);
        return $configParams;
    }
    
    
    /**
     * mergeWithJobConfig()
     *
     * Merges a job's config array with additional configs, such as what might be
     * passed in by the JobSeries class. The second array passed in will overwrite
     * the first.
     *
     * @param $jobConfigArray - a hash of name/value pairs (expects JobAbstract->config)
     * @param $additionalConfig - a hash of name/value pairs
     * @return array - a hash of name/value pairs
     */
    public function mergeWithJobConfig($jobConfigHash, $additionalConfig)
    {
        $jobConfigHash = $this->getJobConfig($jobConfigHash);
        $mergedConfig = $this->merge($jobConfigHash, $additionalConfig);
        return $mergedConfig;
    }
    
    
    /**
     * merge()
     *
     * Recusively merges two hashes, favoring the second array passed in over the 
     * first, and returns the merged result. This means if:
     *
     * $first = array('post' => array('first_name' => 'Harold', 'last_name' => 'Smith');
     * $second = array('post' => array('first_name' => 'John', 'email' => 'me@example.com');
     * $merged = $this->merge($first, $second);
     * 
     * $merged becomes array(
     *   'first_name' => 'John',
     *   'last_name' => 'Smith',
     *   'email' => 'me@example.com')
     *
     * NOTE: this is different behavior than array_merge() or array_merge_recursive().
     *
     * @param $baseArray - a hash of name/value pairs
     * @param $overwriterArray - a hash of name/value pairs
     * @return array - a hash of name/value pairs
     */
    public function merge($baseArray, $overwriterArray)
    {
        $returnArray = $baseArray;
        foreach ($overwriterArray as $index => $value) {
            if (!IsSet($baseArray[$index])) {
                $returnArray[$index] = $value;
            } else {
                if (is_array($baseArray[$index]) && is_array($overwriterArray[$index]) && $this->isHash($overwriterArray[$index])) {
                    $returnArray[$index] = $this->merge($baseArray[$index], $overwriterArray[$index]);
                } else {
                    $returnArray[$index] = $overwriterArray[$index];
                }
            }
        }
        return $returnArray;
    }
    
    
    public function isHash($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    
    /**
     * getHarnessConfig()
     *
     * The Harness class that launches the SugarRestHarness application doesn't want
     * or need to know anything about the job configuration values. But it does need
     * to know about what's in the config file, and what options were passed in
     * on the command line. So this method returns only those two sources of data for
     * use by the Harness.
     *
     * @return array - a hash of name/value pairs from the config file and command 
     *  line arguments.
     */
    public function getHarnessConfig()
    {
        $this->processArgv();
        $this->importConfigFile($this->getConfigFileName());
        $configParams = array();
        $configParams = array_merge($this->configFileOptions, $this->commandLineOptions);
        return $configParams;
    }
    
    
    /**
     * getConfigFileName()
     *
     * Returns the name of the config file. If the configFileName command line option
     * has been set, that value will be returned. Otherwise, the default will be returned.
     *
     * @return string - the name of the config file.
     */
    public function getConfigFileName()
    {
        if (!IsSet($this->commandLineOptions['configFileName'])) {
            $configFileName = $this->defaultConfigFileName;
        } else {
            $configFileName = $this->commandLineOptions['configFileName'];
        }
        return $configFileName;
    }
}
