<?php
/**
 * Created by PhpStorm.
 * User: mandersen
 * Date: 3/3/20
 * Time: 11:48 AM
 */

namespace SugarRestHarness\Jobs\Examples\Tasks;


class CreateTaskRelatedToCase extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct(array $options)
    {
        $description = $this->randomize('Description');
        $name = $this->randomize('Description');

        $priority = $this->randomize('Enum', array('module'=>'Tasks', 'field'=>'priority'));
        $status = $this->randomize('Enum', array('module' => 'Tasks', 'field'=>'status'));
        $start = $this->randomize('Date', array('range_in_days'=>10,'format'=>'Y-m-d\TH:i:sO'));

        $caseID = '342ebcc4-54db-11ea-860a-34363bc45b74';

        $this->config['method'] = "POST";
        $this->config['modules'] = 'Cases';
        $this->config['module'] = 'Cases';
        $this->config['bean_id'] = $caseID;
        $this->config['linkName'] = 'cases_tasks_1';
        $this->config['routeMap'] = 'createRelatedRecord';
        $this->config['post'] = array(
            'assigned_user_id' => $this->getMyId(),
            'date_due_flag' => false,
            'date_start' => $start,
            'date_start_flag' => true,
            'description' => $description,
            'following' => false,
            'my_favorite' => false,
            'name' => $name,
            'parent_id' => $caseID,
            'parent_type' => 'Cases',
            'priority' => $priority,
            'status' => $status,
            'tag' => '',
        );
        parent::__construct($options);
    }
}