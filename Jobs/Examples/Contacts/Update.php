<?php
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Update extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $id = '';
        $this->config['routeMap'] = 'updateRecord';
        $this->config['module'] = 'Contacts';
        $this->config['bean_id'] = $id;
        //$this->config['fields'] = array('first_name', 'last_name', 'email1', 'phone_work', 'title');
        $this->config['post'] = array(
            'id' => $id,
            'title' => 'Jedi Master',
            'phone_work' => '408-728-1459',
        );
        parent::__construct($options);
    }
}
