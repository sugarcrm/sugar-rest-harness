<?php
namespace SugarRestHarness\Expectations;

interface ExpectationsInterface
{
    public function test($fieldName, $actual, $expected);
}