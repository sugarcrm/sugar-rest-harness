<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\UsingRoute;

class Detail extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        // if you set set the route explicitly, you must include the ID of the bean
        // you want to retrieve.
        $this->config['method'] = 'GET';
        $this->config['route'] = '/Contacts/<bean_id>';
        
        // Note that I don't know what a valid bean id is for
        // your system. But you can use the ContactsList.php to get a valid bean id
        // then either update the route param. You cannot pass --bean_id in on the
        // command line when you set config['route'] the way you can when you use
        // config['routeMap'].
        
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'phone_work');
        parent::__construct($options);
    }
}

