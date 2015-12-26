<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\UsingRoute;

class ContactsList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        // if you don't use an element from the routeMap array, you must set the method
        // and route in your config.
        $this->config['method'] = 'GET';
        $this->config['route'] = '/Contacts';
        
        // everything else is pretty much the same
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        $this->expectations['results.records']['is_empty'] = false;
        parent::__construct($options);
    }
}