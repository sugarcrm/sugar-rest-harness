<?php
namespace SugarRestHarness\Jobs\Examples\Opportunities;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Opportunities';
        $this->config['qs']['fields'] = array('name', 'my_favorite', 'account_name', 'description', 'pcontact_id_c', 'account_id', 'sales_stage');
        $this->config['qs']['sort_order'] = 'asc';
        $this->config['qs']['max_num'] = '7';
        $this->config['qs']['order_by'] = 'date_modified';
        $this->config['qs']['favorites'] = 0;
        $this->config['qs']['filter_json'] = '
        {
            "filter": 
            [
                {
                    "description": {"$contains": "Laser"}
                }
            ]
        }
        ';
        $this->config['qs']['my_items'] = 0;
        parent::__construct($options);
    }
}
