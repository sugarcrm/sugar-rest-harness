<?php
namespace SugarRestHarness\Jobs\Examples\Contacts;

class ContactsList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        parent::__construct($options);
    }
}
