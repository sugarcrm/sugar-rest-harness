<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Create extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'createRecord';
        $this->config['module'] = 'Contacts';
        $this->config['post'] = array(
            'first_name' => 'Stevie Ray',
            'last_name' => 'Vaughn',
            'email1' => 'mandersen@sugarcrm.com',
            'primary_address_street' => '123 Fake Street',
            'primary_address_city' => 'Dalas',
            'primary_address_state' => 'TX',
            'primary_address_country' => 'US',
            'title' => 'Guitarist',
            'assigned_user_id' => $this->getMyId(),
            'phone_work' => '555-4321',
        );
        parent::__construct($options);
    }
}
