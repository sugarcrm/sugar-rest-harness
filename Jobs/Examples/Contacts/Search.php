<?php
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Search extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        $this->config['qs']['term'] = 'Stevie Ray';
        $this->config['qs']['my_items'] = 0;
        $this->config['qs']['favorites'] = 0;
        $this->config['qs']['max_num'] = 3;
        parent::__construct($options);
    }
}
