<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\JobSeries;
class RandomAccCntOpp extends \SugarRestHarness\JobSeries
{
    public function run()
    {
        $accountJob = $this->runJob('Jobs/Examples/Accounts/CreateRandom.php');
        $accountID = $accountJob->results->id;
        $campaignID = $accountJob->results->campaign_id;
        $contactJobOptions = array(
            'post' => array(
                'account_id' => $accountID,
            ),
        );
        $this->processOptions($contactJobOptions);
        $contactJob = $this->runJob('Jobs/Examples/Contacts/CreateRandom.php');
        
        $opportunityJobOptions = array(
            'post' => array(
                'account_id' => $accountID,
                'name' => 'Randomly Generated Opportunity',
                'campaign_id' => $campaignID,
            ),
        );
        $this->processOptions($opportunityJobOptions);
        $opportunityJob = $this->runJob('Jobs/Examples/Opportunities/CreateRandom.php');
        
        $opportunityID = $opportunityJob->results->id;
        $rliJobOptions = array(
            'post' => array(
                'account_id' => $accountID,
                'opportunity_id' => $opportunityID,
                'campaign_id' => $campaignID,
            ),
        );
        
        $this->processOptions($rliJobOptions);
        $this->runJob('Jobs/Examples/RevenueLineItems/CreateRandom.php');
    }
}