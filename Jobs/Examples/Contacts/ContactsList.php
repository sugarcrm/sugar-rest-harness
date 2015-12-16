<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Contacts;

class ContactsList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        $this->expectations['results.records']['is_empty'] = false;
        parent::__construct($options);
    }
}
