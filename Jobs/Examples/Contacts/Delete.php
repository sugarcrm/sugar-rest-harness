<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Contacts;

class Delete extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'deleteRecord';
        $this->config['module'] = 'Contacts';
        $this->config['bean_id'] = '';
        parent::__construct($options);
    }
}

