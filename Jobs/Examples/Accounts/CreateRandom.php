<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\Accounts;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {   
        $account_type = $this->randomize('Enum', array('module'=>'Accounts', 'field'=>'account_type'));
        $annual_revenue = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>100));
        $billing_address_city = $this->randomize('City');
        $billing_address_postalcode = $this->randomize('Number', array('min'=>10000, 'max'=>99999));
        $billing_address_state = 'CA';
        $billing_address_street = $this->randomize('StreetAddress');
        $campaign_id = $this->randomize('Bean', array('module'=>'Campaigns', 'field'=>'id'));
        $description = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>0));
        $email_opt_out = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $following = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $industry = $this->randomize('Enum', array('module'=>'Accounts', 'field'=>'industry'));
        $invalid_email = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $my_favorite = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $name = $this->randomize('Description', array('pattern' => 'color noun', 'maxLength'=>150));
        $phone_fax = $this->randomize('PhoneNumber');
        $phone_office = $this->randomize('PhoneNumber');
        $shipping_address_city = $this->randomize('City');
        $shipping_address_postalcode = $this->randomize('Number', array('min'=>10000, 'max'=>99999));
        $shipping_address_state = 'CA';
        $shipping_address_street = $this->randomize('StreetAddress');
        
        $this->config = array(
            'modules' => 'Accounts',
            'configFileName' => 'job.core.config.php',
            'module' => 'Accounts',
            'routeMap' => 'createRecord',
            'post' => array(
                'account_type' => $account_type,
                'annual_revenue' => $annual_revenue,
                'assigned_user_id' => $this->getMyId(),
                'billing_address_city' => $billing_address_city,
                'billing_address_country' => 'US',
                'billing_address_postalcode' => $billing_address_postalcode,
                'billing_address_state' => $billing_address_state,
                'billing_address_street' => $billing_address_street,
                'campaign_id' => $campaign_id,
                'description' => $description,
                'email' => str_replace(' ', '.', "$name@sugarcrm.com"),
                'email_opt_out' => $email_opt_out,
                'following' => $following,
                'industry' => $industry,
                'invalid_email' => $invalid_email,
                'my_favorite' => $my_favorite,
                'name' => $name,
                'phone_fax' => $phone_fax,
                'phone_office' => $phone_office,
                'shipping_address_city' => $shipping_address_city,
                'shipping_address_country' => 'US',
                'shipping_address_postalcode' => $shipping_address_postalcode,
                'shipping_address_state' => $shipping_address_state,
                'shipping_address_street' => $shipping_address_street,
                ),
            );
        parent::__construct($options);
    }
}