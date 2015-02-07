<?php
namespace SugarRestHarness\Jobs\Examples\Opportunities;

class FilterList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'listFilter';
        $this->config['module'] = 'Opportunities';
        $this->config['fields'] = array('name', 'my_favorite', 'account_name', 'description', 'pcontact_id_c', 'account_id', 'sales_stage');
        $this->config['sort_order'] = 'asc';
        $this->config['max_num'] = '7';
        $this->config['order_by'] = 'date_modified';
        $this->config['favorites'] = 0;
        $this->config['filter_json'] = '
        {
            "filter": 
            [
                {
                    "name": {"$contains": "Air"}
                }
            ]
        }
        ';
        $this->config['my_items'] = 0;
        parent::__construct($options);
    }
}
