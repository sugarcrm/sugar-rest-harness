<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Expectations;

interface ExpectationsInterface
{
    public function test($fieldName, $actual, $expected);
}