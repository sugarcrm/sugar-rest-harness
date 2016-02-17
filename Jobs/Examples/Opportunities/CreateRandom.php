<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Opportunities;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $account_id = $this->randomize('Bean', array('module'=>'Accounts', 'field'=>'id'));
        $campaign_id = $this->randomize('Bean', array('module'=>'Campaigns', 'field'=>'id'));
        $commit_stage = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'commit_stage'));
        $contact_role = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'contact_role'));
        $currency_name = $this->randomize('Bean', array('module'=>'Currencies', 'field'=>'name'));
        $currency_symbol = $this->randomize('Bean', array('module'=>'Currencies', 'field'=>'symbol'));
        $date_closed = $this->randomize('Date', array('format'=>'Y-m-d'));
        $date_entered = $this->randomize('Date', array('format'=>'Y-m-d\TH:i:sO'));
        $date_modified = $this->randomize('Date', array('format'=>'Y-m-d\TH:i:sO'));
        $description = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>0));
        $lead_source = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'lead_source'));
        $my_favorite = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $name = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>50));
        $next_step = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>100));
        $opportunity_type = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'opportunity_type'));
        $probability = $this->randomize('Number', array('min'=>1, 'max'=>10000));
        $sales_stage = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'sales_stage'));
        $sales_status = $this->randomize('Enum', array('module'=>'Opportunities', 'field'=>'sales_status'));
        $this->config = array(
            'modules' => 'Opportunities',
            'configFileName' => 'job.core.config.php',
            'module' => 'Opportunities',
            'routeMap' => 'createRecord',
            'post' => array(
                'account_id' => $account_id,
                'amount' => '',
                'amount_usdollar' => '',
                'assigned_user_id' => $this->getMyId(),
                'best_case' => '',
                'campaign_id' => $campaign_id,
                'commit_stage' => $commit_stage,
                'contact_role' => $contact_role,
                'created_by' => $this->getMyId(),
                'currency_id' => '-99',
                'currency_name' => $currency_name,
                'currency_symbol' => $currency_symbol,
                'date_closed' => $date_closed,
                'date_entered' => $date_entered,
                'date_modified' => $date_modified,
                'description' => $description,
                'lead_source' => $lead_source,
                'modified_user_id' => $this->getMyId(),
                'my_favorite' => $my_favorite,
                'name' => $name,
                'next_step' => $next_step,
                'opportunity_type' => $opportunity_type,
                'probability' => $probability,
                'sales_stage' => $sales_stage,
                'sales_status' => $sales_status,
                ),
            );
        parent::__construct($options);
    }
}