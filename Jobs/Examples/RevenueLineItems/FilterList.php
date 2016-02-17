<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\RevenueLineItems;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'RevenueLineItems';
        $this->config['qs']['fields'] = array('name', 'account_name', 'opportunity_name', 'lead_source', 'sales_stage', 'probability');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['order_by'] = 'probability';
        $this->config['qs']['max_num'] = 100;
        
        $this->config['qs']['filter[0][probability][$gte]'] = '50';
        $this->config['qs']['filter[0][probability][$lte]'] = '70';
        parent::__construct($options);
    }
}