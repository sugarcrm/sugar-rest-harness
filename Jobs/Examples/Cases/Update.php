<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Cases;
// original team set id: 92d646c6-4ff4-11e9-9b57-34363bc45b74
class Update extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'updateRecord';
        $this->config['module'] = 'Cases';
        $this->config['bean_id'] = 'd0ed981a-524c-11e9-8368-34363bc45b74';
        $this->config['post'] = array(
            'description' => 'Who changed my team set?',
            //'team_set_id' => 'cslex1seg',
            'team_name' => array(
                array(
                    "id" => "cslex1seg",
                    "name" => "CSAT LEXINGTON 1 SEG",
                    "name_2" => "",
                    "primary" => false,
                    "selected" => false,
                    "email_adddress_c" => ""),
                /*
                array(
                    "id" => "East",
                    "name" => "East",
                    "name_2" => "",
                    "primary" => true,
                    "selected" => false,
                    "email_adddress_c" => "psqa101@gmail.com"
                ),*/
            ),
        );
        
        parent::__construct($options);
    }
}
