<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Tasks;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Tasks';
        $this->config['qs']['fields'] = array('name', 'priority', 'status', 'parent_type', 'parent_name', 'date_created');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['order_by'] = 'parent_type';
        $this->config['qs']['max_num'] = 100;
        $this->config['qs']['filter[0][priority][$equals]'] = 'High';
        $this->config['qs']['filter[0][parent_type][$not_equals]'] = 'Contacts';
        $this->config['qs']['filter[1][parent_type][$not_equals]'] = '';
        parent::__construct($options);
    }
}