<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Jobs\Examples\RevenueLineItems;
class CreateRandom extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $account_id = $this->randomize('Bean', array('module'=>'Accounts', 'field'=>'id'));
        $book_value_date = $this->randomize('Date', array('format'=>'Y-m-d'));
        $book_value = $this->randomize('Number', array('min'=>1000, 'max'=>10000));
        $campaign_id = $this->randomize('Bean', array('module'=>'Campaigns', 'field'=>'id'));
        $category_name = $this->randomize('Bean', array('module'=>'ProductCategories', 'field'=>'name'));
        $commit_stage = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'commit_stage'));
        $currency_id = $this->randomize('Bean', array('module'=>'Currencies', 'field'=>'id'));
        $date_closed = $this->randomize('Date', array('format'=>'Y-m-d'));
        $date_purchased = $this->randomize('Date', array('format'=>'Y-m-d'));
        $date_support_expires = $this->randomize('Date', array('format'=>'Y-m-d'));
        $date_support_starts = $this->randomize('Date', array('format'=>'Y-m-d'));
        $description = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>0));
        $discount_price = $this->randomize('Number', array('min'=>1, 'max'=>1000));
        $discount_amount = $this->randomize('Number', array('min'=>1, 'max'=>$discount_price));
        $following = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $lead_source = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'lead_source'));
        $manufacturer_name = $this->randomize('Bean', array('module'=>'Manufacturers', 'field'=>'name'));
        $my_favorite = (bool)$this->randomize('Number', array('min'=>0, 'max'=>1));
        $name = $this->randomize('Description', array('pattern' => 'adj color noun', 'maxLength'=>50));
        $opportunity_id = $this->randomize('Bean', array('module'=>'Opportunities', 'field'=>'id'));
        $probability = $this->randomize('Number', array('min'=>1, 'max'=>100));
        $product_template_id = $this->randomize('Bean', array('module'=>'ProductTemplates', 'field'=>'id'));
        $product_type = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'product_type'));
        $quantity = $this->randomize('Number', array('min'=>1, 'max'=>100));
        $quote_id = $this->randomize('Bean', array('module'=>'Quotes', 'field'=>'id'));
        $sales_stage = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'sales_stage'));
        $status = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'status'));
        $tax_class = $this->randomize('Enum', array('module'=>'RevenueLineItems', 'field'=>'tax_class'));
        $team_count = $this->randomize('Bean', array('module'=>'Teams', 'field'=>'team_count'));
        $team_name = $this->randomize('Bean', array('module'=>'Teams', 'field'=>'name'));
        $type_id = $this->randomize('Bean', array('module'=>'ProductTypes', 'field'=>'id'));
        $this->config = array(
            'modules' => 'RevenueLineItems',
            'configFileName' => 'job.core.config.php',
            'module' => 'RevenueLineItems',
            'routeMap' => 'createRecord',
            'post' => array(
                'account_id' => $account_id,
                'assigned_user_id' => $this->getMyId(),
                'assigned_user_name' => 'Max Jensen',
                'book_value_date' => $book_value_date,
                'book_value_usdollar' => $book_value,
                'campaign_id' => $campaign_id,
                'category_name' => $category_name,
                'commit_stage' => $commit_stage,
                'cost_price' => $book_value,
                'cost_usdollar' => $book_value,
                'currency_id' => $currency_id,
                'date_closed' => $date_closed,
                'date_purchased' => $date_purchased,
                'date_support_expires' => $date_support_expires,
                'date_support_starts' => $date_support_starts,
                'description' => $description, 
                'discount_price' => $discount_price,
                'discount_amount' => $discount_amount,
                'following' => $following,
                'lead_source' => $lead_source,
                'manufacturer_name' => $manufacturer_name,
                'my_favorite' => $my_favorite,
                'name' => $name,
                'opportunity_id' => $opportunity_id,
                'probability' => $probability,
                'product_template_id' => $product_template_id,
                'product_type' => $product_type,
                'quantity' => $quantity,
                'quote_id' => $quote_id,
                'sales_stage' => $sales_stage,
                'status' => $status,
                'tax_class' => $tax_class,
                'type_id' => $type_id,
                ),
            
            );
        parent::__construct($options);
    }
}