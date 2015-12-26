<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\UsingRoute;

class Create extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        // when you're creating a new record, you just need to set the route and
        // methdo config vars:
        $this->config['route'] = '/Contacts';
        $this->config['method'] = 'POST';
        
        // Everything else is the same.
        $this->config['post'] = array(
            'first_name' => 'Stevie Ray',
            'last_name' => 'Vaughn',
            'email1' => 'support@sugarcrm.com',
            'primary_address_street' => '123 Fake Street',
            'primary_address_city' => 'Dalas',
            'primary_address_state' => 'TX',
            'primary_address_country' => 'US',
            'title' => 'Guitarist',
            'assigned_user_id' => '1',
            'phone_work' => '408-555-4321',
        );
        parent::__construct($options);
    }
}
