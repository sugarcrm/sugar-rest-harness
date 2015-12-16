# SugarRestHarness
(More REST, less api)

The SugarRestHarness is a command line tool for sending arbitrary REST API requests to a SugarCRM 
installation and then processing the returned results.

##Quick-Start Guide
1. Clone the repo https://github.com/sugarcrm/sugar-rest-harness.git anywhere on your system.
2. Edit the file custom/config/job.basic.config.php.
```
    $config['user_name'] = 'admin';  // replace with a valid sugar user's name
    $config['password'] = 'asdf';    // replace with that user's password
    $config['base_url'] = 'http://localhost';  // replace with the domain name of your sugar install
    $config['install_path'] = '/sc/ult';   // replace with the path to your sugar install
```
    The harness will log you in as the user you specify in this file.
    
3. Try running a job:
`./SugarRestHarness -j Jobs/Examples/Contacts/ContactsList.php`

4. You should see the results of your request, i.e. a list of contacts. Or maybe error messages
if something isn't configured right.
    

## What are "Jobs"?
"Jobs" are a single request sent to the SugarCRM install you're working with. 
Job files define all the parameters a single, specific request will send to the server.
Job classes are based on the JobAbstract class. A Job class's construct() method will define
the details of the request to be sent in its 'config' property. 

Here is a simple job file example:
```
<?php
namespace SugarRestHarness\Jobs\Examples\Contacts; // namespace must match path of job file.

// all classes should extend JobAbstract and implement JobInterface
class Update extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface 
{
    public function __construct($options)
    {
        // all jobs must set a route or a routeMap.
        $this->config['routeMap'] = 'updateRecord'; // You can use routeMap or you set your route explicitly.
        $this->config['module'] = 'Contacts'; // most jobs will need a module name
        $this->config['bean_id'] = '<some_contact_id>'; // replace '<some_contact_id>' with an actual contact id.
        
        // name/value pairs for the query string - values will be url_encoded for you, so don't do that here.
        $this->config['qs'] = array(
            'viewed' => 1
        );
        
        // name/value pairs for post
        $this->config['post'] = array(
            // Randomizers can be used to generate random values for various fields.
            'title' => $this->randomize('Title'),
            'phone_work' => '408-728-1459',
        );
        // expectations are optional. They will be compared against data from the job's results, and 
        // any unmet expectations will be reported in the output.
        $this->expectations['results.title']['contains'] = 'Master';
        
        // call parent::__construct() to complete the class's setup.
        parent::__construct($options);
    }
}
```


## I want to run more than one job.
You can run all of the jobs in a directory by just passing the directory's path to the harness:
`./SugarRestHarness -j Jobs/Examples/Contacts/`
will run every job file in Jobs/Examples/Contacts/, but those jobs will not share data from their results.


## I want to run multiple jobs, and pass data from one job to the next.
Then you need a JobSeries class, which can run jobs in a series and pass the results of one job to any 
subsequent jobs. Here is an example of a JobSeries class file that runs a search job, and then runs an
update job on every record returned by the search job:
```
<?php
namespace SugarRestHarness\Jobs\Examples\Contacts; // namespace must match path of job file.

class SearchAndUpdate extends \SugarRestHarness\JobSeries // all JobSeries extend the JobSeries class
{
    public function run()
    {
        // sets config data which will be passed to any job executed by runJob().
        $this->setOption('fields', array('id', 'title'));
        
        // runJob() returns the Job object after its run() method has completed.
        $completedJob = $this->runJob('Jobs/Examples/Contacts/Search.php');
        
        // the 'results' property of the job contains all of the data returned by the request.
        foreach ($completedJob->results->records as $record) {
            // set new options to pass to the update job.
            $options = array(
                'bean_id' => $record->id,
                'post' => array(
                    'id' => $record->id,
                    'title' => 'Updated Contact',
                ),
            );
            
            // processOptions() takes an array of options and sets them all on the next job to run. 
            $this->processOptions($options);
            
            // runJob() runs the job specified in its argument, and passes it the options you've set.
            $this->runJob('Jobs/Examples/Contacts/Update.php');
        }
    }
}
```

## What are Expectations?
Expectations are values you expect to see set on the Job object after it's run() method has been
executed. They are set on a property of the job. Nested properties can be accessed by concatenating 
their names together with '.', like 'results.first_name'. You set an Expectation by associating a 
property with an Expectation class name, and then assigining an expected value, like this:
`$this->expectations['results.title']['contains'] = 'Master';`
After the job is run, all of the expectations will be compared to the actual state of the job's
various properties, and any expectations that were not met will be displayed in the final output.


## I don't like changing files.
You can change any config value on the command line at run-time. Values that are nested arrays
can be changed by concatenating the property names together with '.', like 'post.first_name'. Here
are some examples of changing config values on the command line:
```
SugarRestHarness -j Jobs/Examples/Contacts/Create.php --user_name=max --password=max 
--base_url=http://www.sugarexample.com --install_path=/ --post.first_name=Mike --post.last_name=Andersen
```
