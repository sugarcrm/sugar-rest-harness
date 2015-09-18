<?php
namespace SugarRestHarness\Jobs\Examples;

class ExpectationsListExample extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        
        // expect failure
        $this->expectations['results.records.0.first_name']['equals'] = '666';
        $this->expectations['results.records.0.email1']['contains'] = '!';
        $this->expectations['results.records.0.email1']['not_contains'] = '@';
        $this->expectations['results.records.0.email1']['starts'] = 'xsugareps';
        
        // expect pass
        $this->expectations['results.records.0.email1']['contains'] = '@';
        $this->expectations['results.records.0.last_name']['is_empty'] = false;

        // not sure what to expect
        $this->expectations['results.records']['is_empty'] = true;
        $this->expectations['results.records']['count'] = 3;
        $this->expectations['results.records.0.email1']['not_equals'] = 'sugareps@gmail.com';
        
        parent::__construct($options);
    }
}
