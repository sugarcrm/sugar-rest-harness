<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\UsingRouteMap;

class Detail extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        // if you set the 'routeMap' config param to an key from the routeMap array,
        // you will need to set any variables that route requires (see lib/routeMap.php).
        // In this case, 'module' and 'bean_id'
        $this->config['routeMap'] = 'retrieveRecord';
        $this->config['module'] = 'Contacts';
        
        // Note that bean_id is empty here - I don't know what a valid bean id is for
        // your system. But you can use the ContactsList.php to get a valid bean id
        // then either update this variable or pass the bean id on the command line
        // like this: --bean_id=1234567-some-bean-id
        $this->config['bean_id'] = '';
        
        $this->config['qs']['fields'] = array('first_name', 'last_name', 'phone_work');
        parent::__construct($options);
    }
}

