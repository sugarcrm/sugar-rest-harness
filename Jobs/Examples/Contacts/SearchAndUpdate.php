<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Contacts;

class SearchAndUpdate extends \SugarRestHarness\JobSeries
{
    public function run()
    {
        $this->setOption('fields', array('id', 'title'));
        $results = $this->runJob('Jobs/Examples/Contacts/Search.php');
        foreach ($results->results->records as $record) {
            $options = array(
                'bean_id' => $record->id,
                'post' => array(
                    'id' => $record->id,
                ),
            );
            
            $this->processOptions($options);
            
            $this->runJob('Jobs/Examples/Contacts/Update.php');
        }
    }
}

