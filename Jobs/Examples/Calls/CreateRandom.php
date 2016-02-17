<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Calls;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $contactID = $this->randomize('Bean', array('module' => 'Contacts', 'field' => 'id'));
        $description = $this->randomize('Description');
        $name = $this->randomize('Description');
        $hours = $this->randomize('Enum', array('module'=>'Calls', 'field'=>'duration_hours'));
        $mins = $this->randomize('Enum', array('module'=>'Calls', 'field'=>'duration_minutes'));
        $direction = $this->randomize('Enum', array('module'=>'Calls', 'field'=>'direction'));
        $status = $this->randomize('Enum', array('module'=>'Calls', 'field'=>'status'));
        $start = $this->randomize('Date', array('range_in_days'=>10,'format'=>'Y-m-d\TH:i:sO'));
        $accept = $this->randomize('Enum', array('module'=>'Calls', 'field'=>'accept_status_users'));
        $this->config = array(
            'module' => 'Calls',
            'routeMap' => 'createRecord',
            'post' => array(
                'assigned_user_id' => $this->getMyId(),
                'accept_status_users' => $accept,
                'auto_invite_parent' => false,
                'contacts' => array('add' => array($contactID)),
                'date_start' => $start,
                'description' => $description,
                'direction' => $direction,
                'duration_hours' => $hours,
                'duration_minutes' => $mins,
                'email_reminder_checked' => true,
                'email_reminder_sent' => true,
                'email_reminder_time' => 18000,
                'following' => true,
                'my_favorite' => true,
                'name' => $name,
                'reminder_checked' => true,
                'reminder_time' => 900,
                'status' => $status,
                'parent_type' => 'Contacts',
                'parent_id' => $contactID,
                ),
            );
        parent::__construct($options);
    }
}