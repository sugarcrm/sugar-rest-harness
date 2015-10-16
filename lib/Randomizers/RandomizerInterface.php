<?php
namespace SugarRestHarness\Randomizers;

interface RandomizerInterface
{
    public static function getInstance();
    public function getRandomData($params);
}
