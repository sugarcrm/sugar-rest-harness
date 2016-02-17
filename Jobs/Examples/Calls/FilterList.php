<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Calls;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Calls';
        $this->config['qs']['fields'] = array('name', 'date_entered', 'account_name', 'contact_name');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['order_by'] = 'date_modified';
        $this->config['qs']['filter[0][date_entered][$gt]'] = '2016-02-13';
        $this->config['qs']['filter[0][name][$contains]'] = 'Shiny';
        parent::__construct($options);
    }
}