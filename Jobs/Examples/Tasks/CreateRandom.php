<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Tasks;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $description = $this->randomize('Description');
        $name = $this->randomize('Description');
        $contactID = $this->randomize('Bean', array('module' => 'Contacts', 'field' => 'id'));
        $priority = $this->randomize('Enum', array('module'=>'Tasks', 'field'=>'priority'));
        $status = $this->randomize('Enum', array('module' => 'Tasks', 'field'=>'status'));
        $start = $this->randomize('Date', array('range_in_days'=>10,'format'=>'Y-m-d\TH:i:sO'));
        
        $this->config = array(
            'modules' => 'Tasks',
            'module' => 'Tasks',
            'routeMap' => 'createRecord',
            'post' => array(
                'assigned_user_id' => $this->getMyId(),
                'contacts' => array('add', array($contactID)),
                'date_due_flag' => false,
                'date_start' => $start,
                'date_start_flag' => true,
                'description' => $description,
                'following' => false,
                'my_favorite' => false,
                'name' => $name,
                'parent_id' => $contactID,
                'parent_type' => 'Contacts',
                'priority' => $priority,
                'status' => $status,
                'tag' => '',
                ),
            
            );
        parent::__construct($options);
    }
}