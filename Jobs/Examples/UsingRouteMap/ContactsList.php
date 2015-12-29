<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\UsingRouteMap;

class ContactsList extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        // if you set the 'routeMap' config param to an key from the routeMap array,
        // you will need to set any variables that route requires  (see lib/routeMaps.php).
        // In this case, 'module'.
        $this->config['routeMap'] = 'list';
        $this->config['module'] = 'Contacts';
        
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'email1', 'phone_work');
        $this->expectations['results.records']['is_empty'] = false;
        parent::__construct($options);
    }
}