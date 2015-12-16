<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

interface RandomizerInterface
{
    public static function getInstance();
    public function getRandomData($params);
}
