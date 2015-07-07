<?php
namespace SugarRestHarness\Jobs;

class Generic extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        parent::__construct($options);
    }
}
