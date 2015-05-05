<?php
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Update extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'updateRecord';
        $this->config['module'] = 'Contacts';
        $this->config['bean_id'] = '';
        $this->config['post'] = array(
            'title' => 'Jedi Master',
            'phone_work' => '408-728-1459',
        );
        $this->expectations['results.title']['contains'] = 'Master';
        parent::__construct($options);
    }
}
