<?php

namespace SugarRestHarness\Jobs\Examples\Cases;

class CaseCreateRandomSeries extends \SugarRestHarness\JobSeries
{
    public function run()
    {
        $count = $this->config['count'] ?? 1;

        for ($i = 0; $i < $this->config['count']; $i++) {
            $this->runJob('Jobs/Examples/Cases/CaseCreateRandom.php');
        }
    }
}