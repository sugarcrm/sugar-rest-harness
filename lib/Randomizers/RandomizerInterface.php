<?php
namespace SugarRestHarness;

interface RandomizerInterface
{
    public static function getInstance();
    public function getRandomData($params);
}
