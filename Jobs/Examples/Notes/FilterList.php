<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Notes;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Notes';
        $this->config['qs']['fields'] = array('name', 'description', 'date_entered', 'parent_name');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['order_by'] = 'date_modified';
        $this->config['qs']['filter[0][description][$contains]'] = 'turn';
        parent::__construct($options);
    }
}