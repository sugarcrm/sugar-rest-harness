<?php

namespace SugarRestHarness\Jobs\Examples\Cases;

use SugarRestHarness\JobInterface;
use SugarRestHarness\JobAbstract;

class CaseCreateRandom extends JobAbstract implements JobInterface
{
    public function __construct($options)
    {
        $this->config['method'] = 'POST';
        $this->config['route'] = '/Cases';


        $this->config['post'] = [
            'name' => $this->randomize('Title'),
            'description' => $this->randomize('Description'),
            'account_id' => $this->randomize('Bean', array('module'=>'Accounts', 'field'=>'id')),
            'source' => $this->randomize('Enum', array('module'=>'Cases', 'field'=>'source')),
            'type' => $this->randomize('Enum', array('module'=>'Cases', 'field'=>'type')),
            'tag' => ['From Rest Harness'],
            'status' => $this->randomize('Enum', array('module'=>'Cases', 'field'=>'status')),
            'team_id' => 'East',
            'assigned_user_id' => 'seed_will_id',
            'team_name' => array(
                array(
                    "id" => "East",
                    "name" => "East",
                    "name_2" => "",
                    "primary" => true,
                    "selected" => true,
                )
            ),
        ];
        parent::__construct($options);
    }
}