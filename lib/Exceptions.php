<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness;

class Exception extends \Exception 
{
    public function __construct($msg='', $msgArgs=array(), $code=-1, $previousException=null)
    {
        if (!empty($msg)) {
            $this->msg = $msg;
        }
        
        if (!empty($code)) {
            $this->code = $code;
        }
        
        if (!empty($msgArgs)) {
            $this->msgArgs = $msgArgs;
        }
        
        foreach ($msgArgs as $name => $value) {
            $this->$name = $value;
        }
        
        parent::__construct($this->msg, $this->code, $previousException);
    }
    
    
    public function getFormattedOutput()
    {
        $msg = array($this->getMessage());
        $file = $this->getFile();
        $line = $this->getLine();
        $type = get_class($this);
        $msg[] = "Thrown in $file:$line";
        $msg[] = $this->getTraceAsString();
        return "\n****************\nEXCEPTION: $type\n\n" . implode("\n", $msg) . "\n****************\n\n\n";
    }
    
    
    public function output()
    {
        print($this->getFormattedOutput());
    }
    
    
}

class MissingRouteMap extends \SugarRestHarness\Exception
{
    public $msg = "Your job does not define a url, route or a routeMap config property. Please add one of those to your job.";
}


class MissingRequiredConfigAttributes extends \SugarRestHarness\Exception
{
    public $msg = "Your job does not define all of the required fields. Please fill in all required fields.";
    // concatenate error messages in $msgArgs
}


class MissingRequiredRouteMapComponents extends \SugarRestHarness\Exception
{
    public function __construct($jobClass, $missingPropertyName, $routeMap) 
    {
        $msg = "Job $jobClass does not specify $missingPropertyName, which is required when using a '$routeMap' route map.";
        $msg .= "\nNo Request sent!";
        parent::__construct($msg);
    }
}

class NoMethodSet extends \SugarRestHarness\Exception
{
    public $msg = "Your config doesn't set a method (GET|POST|PUT|DELETE). You must either set a valid routeMap or a route and a method in your job config.";
}


class ServerError extends \SugarRestHarness\Exception
{
    public $msgMap = array(
        '500' => 'Better check the logs.',
        '401' => 'There\'s a problem with your authorization token.',
        '403' => 'The user you are logged in as isn\'t allowed to perform the actions in the current job',
        '404' => 'Could not find the record you requested',
        '409' => 'Edit Conflict - Somebody else tried to edit this record',
        '412' => 'Metadata exception',
        '413' => 'Request is too large',
        '422' => 'Request is missing required parameters, or has invalid parameters. The following problems were reported:',
        '424' => 'Request has invalid parameters',
        '433' => 'Client version is too low/out of date',
        '503' => 'Server is in Mainenance mode',
    );
    
    public function __construct($httpReturnCode, $expectedReturnCode, $errors)
    {
        $msg = "Server returned a $httpReturnCode error, expected a $expectedReturnCode";
        if (IsSet($this->msgMap[$httpReturnCode])) {
            $msg .= " - {$this->msgMap[$httpReturnCode]}";
        }
        
        $errors = json_decode($errors, true);
        if ($errors && IsSet($errors['error_message'])) {
            if (is_array($errors['error_message'])) {
                foreach ($errors['error_message'] as $fieldName => $errorMessages) {
                    if (is_array($errorMessages)) {
                        foreach ($errorMessages as $errorMessage) {
                            $msg .= "\n\t$fieldName - $errorMessage";
                        }
                    } else {
                        $msg .= "\n\t$fieldName - $errorMessages";
                    }
                }
            } else {
                $msg .= "\n\t" . $errors['error_message'];
            }       
        }
        parent::__construct($msg);
    }
}


class MissingJobClass extends \SugarRestHarness\Exception
{
    public function __construct($className, $classFilePath)
    {
        $msg = "$classFilePath is expected to define '$className', but it does not.";
        $msg .= "\nPlease check $classFilePath and confirm it correctly defines $className, including the namespace.";
        parent::__construct($msg);
    }
}


class NotAPHPFile extends \SugarRestHarness\Exception
{
    public function __construct($classFilePath)
    {
        $msg = "This file, '$classFilePath', doesn't have a '.php' extension and may not be a PHP file.";
        $msg .= "\nPlease define job classes in '.php' files.";
        parent::__construct($msg);
    }
}


class NotAFile extends \SugarRestHarness\Exception
{
    public function __construct($classFilePath)
    {
        $msg = "Cannot find '$classFilePath'. Please confirm you have entered the path to the job file correctly.";
        parent::__construct($msg);
    }
}


class DoesNotImplementJobInterface extends \SugarRestHarness\Exception
{
    public function __construct($className, $classFilePath)
    {
        $msg = "The class $className, defined in '$classFilePath', does not implement the JobInterface.";
        $msg .= "\nCannot use Job classes that do not implement the JobInterface".
        parent::__construct($msg);
    }
}


class LoginFailure extends \SugarRestHarness\Exception
{
    public function __construct($user_name, $url, $connectorMsgs)
    {
        $fatal = true;
        $msg = "Could not log in as user '$user_name' at domain '$url'\n";
        
        foreach ($connectorMsgs as $connectorMsgArray) {
            foreach ($connectorMsgArray as $index => $connectorMsg) {
                if (is_object($connectorMsg) && is_string($connectorMsg->error_message)) {
                    $connectorMsg = strip_tags($connectorMsg->error_message);
                }
                
                if (is_int($index)) {
                    $strIndex = '';
                } else {
                    $strIndex = "$index: ";
                }
                $msg .= "{$strIndex}{$connectorMsg}\n";
            }
        }
        
        parent::__construct($msg);
    }
}


class CannotWriteToDirectory extends \SugarRestHarness\Exception
{
    public function __construct($fileName)
    {
        $msg = "JobWriter could not open '$fileName' for writing, it appears to be a directory.";
        parent::__construct($msg);
    }
}


class CannotWriteToFile extends \SugarRestHarness\Exception
{
    public function __construct($fileName)
    {
        $msg = "JobWriter could not open '$fileName' for writing.";
        parent::__construct($msg);
    }
}



class WriteToFileFailed extends \SugarRestHarness\Exception
{
    public function __construct($fileName)
    {
        $msg = "JobWriter was able to open '$fileName' but could not write to it.";
        parent::__construct($msg);
    }
}


class RandomDataKeyIsInvalid extends \SugarRestHarness\Exception
{
    public function __construct($badKey, $finalKey)
    {
        $msg = "The randomizer cannot get a random value for '$badKey' because it's not defined in appListStrings.";
        parent::__construct($msg);
    }
}


class RandomDataParamMissing extends \SugarRestHarness\Exception
{
    public function __construct($className, $missingParamName)
    {
        $msg = "The $className randomizer requires a value for \$params['$missingParamName'] to be passed into getRandomData().";
        parent::__construct($msg);
    }
}


class RandomDataNoEnumFieldData extends \SugarRestHarness\Exception
{
    public function __construct($module, $field)
    {
        $msg = "There's no enum data for the {$module}->$field field.";
        parent::__construct($msg);
    }
}


class RandomDataAppListStringNotFound extends \SugarRestHarness\Exception
{
    public function __construct($key)
    {
        $msg = "Could not find '$key' in app_list_strings.";
        parent::__construct($msg);
    }
}


class ClassFileNotFound extends \SugarRestHarness\Exception
{
    public function __construct($className, $classFilePath)
    {
        if (is_array($classFilePath)) {
            $classFilePath = implode(",\n", $classFilePath);
        }
        
        $msg = "While trying to instantiate $className, we looked for class files in these locations:\n$classFilePath\n";
        $msg .= "None of them exist or they are not readable. Please check your class name and files.";
        parent::__construct($msg);
    }
}


class ClassIsNotDefined extends \SugarRestHarness\Exception
{
    public function __construct($className, $classFilePath)
    {
        if (is_array($classFilePath)) {
            $classFilePath = implode(",\n", $classFilePath);
        }
        
        $msg = "The class $className is not defined in any of these locations:\n$classFilePath.\nPlease make sure the class name is is correct, including the namespace.";
        parent::__construct($msg);
    }
}


class UploadFileNotSpecified extends \SugarRestHarness\Exception
{
    public function __construct($fieldName)
    {
        $msg = "No file path specified for $fieldName - expected a path to a file.";
        parent::__construct($msg);
    }
}

class UploadFileNotFound extends \SugarRestHarness\Exception
{
    public function __construct($fieldName, $filePath)
    {
        $msg = "Could not find the file at '$filePath' for field named '$fieldName'";
        parent::__construct($msg);
    }
}


class MissingPackageFileHash extends \SugarRestHarness\Exception
{
    public function __construct()
    {
        $msg = "You must set a package_file_hash config value that matches a staged package's file_hash.";
        parent::__construct($msg);
    }
}


class PackageInstallFailed extends \SugarRestHarness\Exception
{
    public function __construct($packageName, $previousException)
    {
        $msg = "$packageName failed to install. " . $previousException->getMessage();
        parent::__construct($msg);
    }
}


class PackageUploadFailed extends \SugarRestHarness\Exception
{
    public function __construct($packageName, $previousException)
    {
        $msg = "$packageName failed to upload. " . $previousException->getMessage();
        parent::__construct($msg);
    }
}
