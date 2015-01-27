<?php
namespace SugarRestHarness;
use SugarRestHarness;

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
        $this->connector = new RestConnector($this->config);
    }
    
    
    private function registerAutoloader()
    {
        spl_autoload_register(array($this, 'autoload'), true, false);
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
    private function autoload($className)
    {
        $installLibDir = pathinfo(__FILE__, PATHINFO_DIRNAME);
        $namespaceParts = explode('\\', $className);
        $className = array_pop($namespaceParts);
        
        $classPath = "$installLibDir/$className.php";
        
        if (is_file($classPath)) {
            require_once($classPath);
            if (!class_exists("\\SugarRestHarness\\$className") && !interface_exists("\\SugarRestHarness\\$className")) {
                die("Harness::autoload failure - $className not defined in $classPath\n");
            }
        } else {
            $bt = debug_backtrace();
            foreach ($bt as $t) {
                if (!IsSet($t['file']) || empty($t['file'])) {$t['file'] = '';}
                if (!IsSet($t['line']) || empty($t['line'])) {$t['line'] = '';}
                if (!IsSet($t['class']) || empty($t['class'])) {$t['class'] = '';}
                if (!IsSet($t['function']) || empty($t['function'])) {$t['function'] = '';}
                print("\n{$t['file']}:{$t['line']} {$t['class']}->{$t['function']}(" . var_export($t['args'], true) . ")");
            }
            die("Harness::autoload failure - $classPath not found while trying to instantiate $className\n");
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
            print("'$classFilePath' does not have a .php extension\n");
            return false;
        }
        
        if (!is_file($classFilePath)) {
            print("'$classFilePath' is not a file!\n");
            return false;
        }
        
        $classRelativePath = $this->getRelativeClassPath($classFilePath);
        $className = self::getNamespacedClassName($classRelativePath);
        require_once($classFilePath);
        
        if (!class_exists($className)) {
            print("'$className' is not a defined class!\n");
            return false;
        }
        
        $interfaces = class_implements($className, true); // second arg must be true!
        if (!in_array('SugarRestHarness\\JobInterface', $interfaces)) {
            print("'$className' does not implement SugarRestHarness\JobInterface\n");
            return false;
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
                $jobDir = IsSet($this->config['jobDir']) ? $this->config['jobDir'] : getcwd();
                $absolutePath = "{$jobDir}/{$this->config['j']}";
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
            if ($this->verifyClassFile($absolutePath)) {
                $this->jobClasses[$absolutePath] = self::getNamespacedClassName($this->getRelativeClassPath($absolutePath));
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
        $classRelativePath = str_replace($this->config['jobs_dir'] . '/', '', $absolutePath);
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
        
        $className = "\SugarRestHarness\Formatter{$className}";
        if (class_exists($className)) {
            return new $className($this->config);
        } else {
            die("There is no formatter class '$className' in lib/\n");
        }
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
        $this->config['token'] = $this->connector->getToken();
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
        
        if (!$this->login()) {
            $this->connector->report();
            die("Harness::login failure - could not login as {$this->config['userid']} on {$this->config['base_url']}/{$this->config['install_path']}\n");
        }
        
        $jobClasses = $this->getJobClassList();
        $formatter = $this->formatterFactory(count($jobClasses));
        ResultsRepository::getInstance()->setFormatter($formatter);
        foreach ($jobClasses as $classFilePath => $namespacedClassName) {
            $this->job = new $namespacedClassName($this->config);
            $this->job->run();
        }
        
        return $formatter->format();
    }
}
