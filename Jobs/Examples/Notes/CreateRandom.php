<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Notes;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $accountID = $this->randomize('Bean', array('module' => 'Accounts', 'field' => 'id'));
        $description = $this->randomize('Description');
        $name = $this->randomize('Description');
        
        $this->config = array(
            'module' => 'Notes',
            'routeMap' => 'createRecord',
            'post' => array(
                'assigned_user_id' => $this->getMyId(),
                'description' => $description,
                'name' => $name,
                'accounts' => array('add' => array($accountID)),
                ),
            );
        
        if ($this->randomize('Number', array('min'=>0,'max'=>1)) == 1) {
            $this->config['my_favorite'] = true;
        }
        
        
        parent::__construct($options);
    }
}