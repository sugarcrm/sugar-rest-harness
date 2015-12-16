<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Expectations;

class Starts extends \SugarRestHarness\Expectations\ExpectationsAbstract implements \SugarRestHarness\Expectations\ExpectationsInterface
{
    /**
     * starts()
     *
     * Checks to see if the actual value begins with the expected value.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function test($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $actualStr = (string) $actual;
        $expectedStr = (string) $expected;
        
        if (stripos($actual, $expected) === 0) {
            $met = true;
            $msg = "$fieldName is '$actualStr' and begins with '$expectedStr'";
        } else {
            $msg = "$fieldName is '$actualStr', which does not begin with '$expectedStr'";
        }
        
        return array($met, $msg); 
    }
}