<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Contacts;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Contacts';
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'account_name', 'office_phone');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['max_num'] = '25';
        $this->config['qs']['order_by'] = 'date_modified';
        $this->config['qs']['favorites'] = 0;
        $this->config['qs']['filter[0][first_name][$contains]'] = 's';
        $this->config['qs']['filter[0][last_name][$contains]'] = 'e';
        $this->config['qs']['filter[0][account_name][$contains]'] = 'Investment';
        $this->config['qs']['my_items'] = 0;
        parent::__construct($options);
    }
}
