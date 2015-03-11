<?php
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
    
    public function output()
    {
        $msg = array($this->getMessage());
        $file = $this->getFile();
        $line = $this->getLine();
        $msg[] = "Thrown in $file:$line";
        print("\n****************\nSugarRestHarness Exception\n\n" . implode("\n", $msg) . "\n****************\n");
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
        '422' => 'Request is missing required parameters, or has invalid parameters',
        '424' => 'Request has invalid parameters',
        '433' => 'Client version is too low/out of date',
        '503' => 'Server is in Mainenance mode',
    );
    
    public function __construct($httpReturnCode)
    {
        $msg = "Server returned a $httpReturnCode error";
        if (IsSet($this->msgMap[$httpReturnCode])) {
            $msg .= " - {$this->msgMap[$httpReturnCode]}";
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
