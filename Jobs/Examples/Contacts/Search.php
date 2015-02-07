<?php
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Search extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        $this->config['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        $this->config['term'] = 'Stevie Ray';
        $this->config['my_items'] = 0;
        $this->config['favorites'] = 0;
        $this->config['max_num'] = 3;
        parent::__construct($options);
    }
}
