<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;
use SugarRestHarness;

require_once(\SugarRestHarness\Harness::getAbsolutePath("lib/Exceptions.php"));
/**
 * Harness
 *
 * This class is the basis of the SugarRestHarness - it sets up our autoloading of
 * class files, figures out which job(s) to run, logs into the sugar instance, runs
 * the job(s) and returns the formatted output of the job(s) results.
 */

class Harness
{
    public $options = array();
    public $shortOptions = "j:";
    public $longOptions = array();
    public $jobClasses = array();
    public $connector = null;
    
    
    
    public function __construct()
    {
        $this->registerAutoloader();
        $this->config = \SugarRestHarness\Config::getInstance()->getHarnessConfig();
        $this->repo = \SugarRestHarness\ResultsRepository::getInstance();
        $this->connector = new \SugarRestHarness\RestConnector($this->config);
    }
    
    
    private function registerAutoloader()
    {
        spl_autoload_register(array($this, 'autoload'), true, false);
    }
    
    
    /**
     * getAbsolutePath()
     *
     * Returns the absolute path of the passed in file, or if no file is passed in
     * it returns the absolute path of the directory that the SugarRestHarness is
     * installed in. 
     *
     * The idea is that you pass this method a relative path to a file in this application
     * and it returns you the absolute path of that file.
     *
     * @param string $fileName - a file name or a path to append to the end of this
     *  this method's return value.
     * @return string - an absolute path to a file, or the install directory.
     */
    public static function getAbsolutePath($fileName='')
    {
        static $baseInstallPath;
        
        if (!isset($baseInstallPath)) {
            $baseInstallPath = str_replace('/lib', '', pathinfo(__FILE__, PATHINFO_DIRNAME));
        }
            
        if (substr($fileName, 0, 1) != '/') {
            $fileName = '/' . $fileName;
        }
        
        return $baseInstallPath . $fileName;
    }
    
    
    /**
     * autoload()
     *
     * Basic autoloader to load up our job classes and library files. This method only
     * searches in the Job and lib directories. Dies with an error if a class cannot
     * be loaded.
     *
     * @param string $className - the name of the class to load.
     * @return bool - always returns true.
     */
    private function autoload($namespacedClassName)
    {
        $installDir = self::getAbsolutePath('/');
        $installLibDir = self::getAbsolutePath('/lib');
        $namespaceParts = explode('\\', $namespacedClassName);
        $className = array_pop($namespaceParts);
        array_shift($namespaceParts);
        $namespacePath = rtrim(implode('/', $namespaceParts), '/');
        $jobPath = "{$installDir}{$namespacePath}";
        $directoryPath = rtrim($installLibDir . '/' . implode('/', $namespaceParts), '/');
        $customDirectoryPath = str_replace('/lib', '/custom/lib', $directoryPath); 
        
        $paths = array(
            'jobPath' => "{$jobPath}/{$className}.php",
            'classPath' => "{$directoryPath}/{$className}.php",
            'customClassPath' => "{$customDirectoryPath}/{$className}.php",
        );
        
        $foundPath = false;
        foreach ($paths as $pathName => $path) {
            if (is_file($path)) {
                $foundPath = "$pathName, which is '$path'";
                require_once($path);
            }
        }

        if ($foundPath) {
            if (!class_exists("$namespacedClassName") && !interface_exists("$namespacedClassName")) {
                die("Harness::autoload failure - $namespacedClassName not defined in $foundPath\n");
            }
        } else {
            $pathMsg = implode(', ', $paths);
            die("Harness::autoload failure - could not find $pathMsg while trying to instantiate $namespacedClassName\n");
        }
        return true;
    }
    
    
    /**
     * verifyClassFile()
     *
     * Make sure the class file is:
     *  a) A file.
     *  b) A php file.
     *  c) Defines the class we think it defines.
     *  d) Implements the JobInterface.
     *
     * If all of those are true, return true. Otherwise false.
     *
     * @param string $classFilePath - the path to the job file path we want to verify.
     * @return bool - true if path is to a valid job class, false if not.
     */
    public function verifyClassFile($classFilePath)
    {
        if (pathinfo($classFilePath, PATHINFO_EXTENSION) != 'php') {
            throw new \SugarRestHarness\NotAPHPFile($classFilePath);
        }
        
        if (!is_file($classFilePath)) {
            throw new \SugarRestHarness\NotAFile($classFilePath);
        }
        
        $classRelativePath = $this->getRelativeClassPath($classFilePath);
        $className = self::getNamespacedClassName($classRelativePath);
        require_once($classFilePath);
        
        if (!class_exists($className, false)) {
            throw new \SugarRestHarness\MissingJobClass($className, $classFilePath);
        }
        
        $interfaces = class_implements($className, true); // second arg must be true!
        if (!in_array('SugarRestHarness\\JobInterface', $interfaces)) {
            throw new \SugarRestHarness\DoesNotImplementJobInterface($className, $classFilePath);
        }
        
        return true;
    }
    
    
    /**
     * getJobClassList()
     *
     * When the rest harness is invoked, we may be passed a path to a single job file
     * or to a directory of job files and/or sub-directories with job files in them.
     * We want to execute ALL of the job files in directory when we're passed a 
     * directory. So we build a hash of every job file contained in the passed in
     * path. This hash contains one entry when passed a path to a file, and 0 or more
     * entries when passed a directory.
     *
     * @param string $path - the path to a file or directory.
     * @return array - an array of file path names.
     */
    public function getJobClassList($path='')
    {
        if (empty($path)) {
            if (substr($this->config['j'], 0, 1) == '/') {
                // assume absolute path given for job file.
                $absolutePath = $this->config['j'];
            } else {
                // determine path based on assumed job directory.
                $absolutePath = self::getAbsolutePath($this->config['j']);
            }
        } else {
            $absolutePath = $path;
        }
        
        if (is_dir($absolutePath)) {
            if (substr($absolutePath, -1) != '/') {
                $absolutePath .= '/';
            }
            
            $dh = opendir($absolutePath);
            while (false !== ($entry = readdir($dh))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $this->getJobClassList("{$absolutePath}{$entry}");
            }
        } else {
            try {
                if ($this->verifyClassFile($absolutePath)) {
                    $this->jobClasses[$absolutePath] = self::getNamespacedClassName($this->getRelativeClassPath($absolutePath));
                }
            } catch (\SugarRestHarness\Exception $e) {
                $e->output();
                return array();
            }
        }
        
        return $this->jobClasses;
    }
    
    
    /**
     * getRelativeClassPath()
     *
     * Takes the absoute path to a file and returns the relative path by removing
     * the value of getcwd() from the absolute path. We use this to get the namespaced
     * class name because the namespace is based on the directory structure starting
     * from the 'Jobs/' directory.
     *
     * @param string $absolutePath - an absolute path to a job class file.
     * @return string - the relative path to that same file.
     */
    public function getRelativeClassPath($absolutePath)
    {
        $classRelativePath = str_replace(self::getAbsolutePath('/'), '', $absolutePath);
        return $classRelativePath;
    }
    
    
    /**
     * getClassName()
     *
     * Returns the expected class name for a given class file path. The name is expected
     * to be the name of the file without the '.php'.
     *
     * @param string $classFilePath - an absolute or relative file path
     * @return string - the basename from the passed in path.
     */
    static function getClassName($classFilePath)
    {
        return basename($classFilePath, '.php');
    }
    
    
    /**
     * getClassNameSpace()
     *
     * Returns the namespace for the class defined in a job class file. The namespace
     * should match the directory structure. The root namespace is always
     * 'SugarRestHarness', so the namespace for a job in the Contacts directory would
     * be SugarRestHarness\Jobs\Contacts.
     *
     * NOTE: this method must be passed a relative path starting with the 'Jobs'
     * directory. An absolute path here will give you an incorrect namespace.
     *
     * @param string $classFilePath - a relative file path.
     * @return string - the class's namespace.
     */
    static function getClassNameSpace($classFilePath)
    {
        return "SugarRestHarness\\" . str_replace('/', '\\', pathinfo($classFilePath, PATHINFO_DIRNAME));
    }
    
    
    /**
     * getNamespacedClassName()
     *
     * Given a relative path to a job file, returns the namespaced class name for the
     * class expected to be defined by that file.
     *
     * @param string $classFilePath - a relative file path.
     * @return string - the namespaced class name.
     */
    static function getNamespacedClassName($classFilePath)
    {
        $namespace = self::getClassNameSpace($classFilePath);
        $className = self::getClassName($classFilePath);
        return "\\$namespace\\$className";
    }
    
    
    /**
     * formatterFactory()
     *
     * Returns a formatter object, which will handle creating the output for all of
     * the jobs that have been run in this session (such jobs are stored in the 
     * ResultsRepository).
     *
     * @see SugarRestHarness\ResultsRepository
     * @param int $jobsCount - the number of jobs being run - multiple jobs get different
     *  formatters by default.
     * @return \SugarRestHarness\FormatterBase - an object that extends FormatterBase.
     */
    public function formatterFactory($jobsCount)
    {
        if (IsSet($this->config['outputFormat'])) {
            $className = $this->config['outputFormat'];
        } elseif (IsSet($this->config['mode'])) {
            $mode = $this->config['mode'];
            $singleMulti = $jobsCount == 1 ? 'single' : 'multiple';
            if (IsSet($this->config[$mode . 'Formatter'][$singleMulti])) {
                $className = $this->config[$mode . 'Formatter'][$singleMulti];
            } else {
                $className = 'TwoColumn';
            }
        } else {
            $className = 'TwoColumn';
        }
        
        $className = "\SugarRestHarness\Formatters\Formatter{$className}";
        try {
            $formatter = $this->loadFormatterClass($className);
        } catch (Exception $e) {
            // load the default.
            $formatter = $this->loadFormatterClass("\SugarRestHarness\Formatters\FormatterTwoColumn");
            $this->storeException($e);
        }
        return $formatter;
    }
    
    
    /**
     * loadFormatterClass()
     *
     * Loads the specified formatter class. Throws exceptions if the class file
     * cannot be found or if the class isn't defined in the class file.
     *
     * @param string $className - the name of the formatter class you want to load.
     * @return FormatterBase - an object that extends the FormatterBase class.
     * @throws FormatterClassFileNotFound, FormatterClassNotDefined
     */
    public function loadFormatterClass($className)
    {
        $classNameParts = explode('\\', $className);
        $classBaseName = $classNameParts[count($classNameParts) - 1];
        $classFilePath = self::getAbsolutePath("lib/Formatters/{$classBaseName}.php");
        
        if (!file_exists($classFilePath)) {
            throw new \SugarRestHarness\FormatterClassFileNotFound($classFilePath);
        }
        
        require_once($classFilePath);
        
        if (!class_exists($className)) {
            throw new \SugarRestHarness\FormatterClassNotDefined($className, $classFilePath);
        }
        
        $formatter = new $className($this->config);
        return $formatter;
    }
    
    
    /**
     * login()
     *
     * Gets the login token and stores it in config so it will be passed to the 
     * RestConnector later. This way the RestConnector won't need to get a token
     * for each job it runs.
     *
     * @return bool - true if we got a login token, false if not.
     */
    public function login()
    {   
        try {
            $this->config['token'] = $this->connector->getToken();
        } catch (\SugarRestHarness\Exception $e) {
            
            die($e->getFormattedOutput());
        }
        
        return !empty($this->config['token']);
    }
    
    /**
     * exec()
     *
     * Calls the passed in Job's run() method (or spits out an error message if job has
     * not been specified on the command line).
     *
     * @param string $job - a Module name and job name concatenated with an _ i.e.
     *  Contacts_Detail
     */
    public function exec($job = null)
    {
        if (IsSet($this->config['h'])) {
            $help = file_get_contents('lib/help.txt');
            die($help);
        }
        
        if (!IsSet($this->config['j'])) {
            die("Harness::exec failure - Usage: rest.php -j path/to/JobClassFile.php\n");
        }
        
        $this->login();
        
        $jobClasses = $this->getJobClassList();
        
        $formatter = $this->formatterFactory(count($jobClasses));
        
        $this->repo->setFormatter($formatter);
        foreach ($jobClasses as $classFilePath => $namespacedClassName) {
            $this->job = new $namespacedClassName($this->config);
            $this->transferExceptions($this->job);
            $this->job->run();
            
            if (IsSet($this->config['w'])) {
                $writer = new \SugarRestHarness\JobWriter($this->job);
                $writer->createJobFile();
            }
        }
        // save the output to a log file if one is specified.
        $formattedResults = $this->repo->getFormatter()->format();
        $ext = $this->repo->getFormatter()->getFileExtension();
        $this->saveToLogFile($formattedResults, $ext);
        
        return $formattedResults;
    }
    
    
    /**
     * storeException()
     *
     * Stores an exception object in the exceptions array for future referenece
     * by the formatter class.
     *
     * If the application is going to throw an exception and you want a Formatter
     * class to display/format it, it must be passed to a JobAbstract object
     * via this method.
     *
     * @param \SugarRestHarness\Exception $exeption - an exception thrown during
     *  this job.
     */
    public function storeException($exception)
    {
        $this->exceptions[] = $exception;
    }
    
    
    /**
     * transferExceptions()
     *
     * This method copies any exceptions stored in the Harness to the target object
     * passed in as the argument. This is good for displaying exceptions generated
     * by the harness via the usual mechanism in the job classes.
     *
     * @param JobAbstract $target - any object that defines a storeException() method.
     */
    public function transferExceptions($target)
    {
        if (method_exists($target, 'storeException') && !empty($this->exceptions)) {
            foreach ($this->exceptions as $exception) {
                $target->storeException($exception);
            }
        }
    }
    
    
    /**
     * saveToLogFile()
     *
     * Writes its argument to a log file, if a log file name has been specified in
     * the config. The log file name will get the extension specified by the
     * formatter the harness is using.
     *
     * @param string $contents - the stuff you want to write to the log file.
     * @param string $ext - a file extension. Typically set in the formatter class
     *  that generated $contents. Default is 'txt';
     * @return bool - true if there were no errors, false otherwise.
     */
    public function saveToLogFile($contents, $ext = 'txt')
    {
        $writeFileOK = true;
        $contents = trim($contents);
        if (!empty($this->config['log_file'])) {
            $date = date('Y-m-d_H:i:s');
            $logFile = str_replace(".$ext", '', $this->config['log_file']);
            $logFile = "{$logFile}_{$date}.{$ext}";
            
            $logDir = pathinfo($logFile, PATHINFO_DIRNAME);
            
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            $writeFileOK = file_put_contents($logFile, $contents);
        }
        
        if (!$writeFileOK) {
            print("\nFailed to write to log file $logFile. Log was NOT SAVED.\n");
        }
        return $writeFileOK;
    }
}
