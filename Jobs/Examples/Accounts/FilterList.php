<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Accounts;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Accounts';
        $this->config['qs']['fields'] = array('name', 'contact_name', 'phone_office', 'billing_address_city');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['order_by'] = 'name';
        $this->config['qs']['filter[0][name][$contains]'] = 't';
        parent::__construct($options);
    }
}