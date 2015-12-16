<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Expectations;

class Gt extends \SugarRestHarness\Expectations\ExpectationsAbstract implements ExpectationsInterface
{
    /**
     * test()
     *
     * Checks to see if the actual value is greater than the expected value.
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
        
        $met = ($actual > $expected);
        
        if ($met) {
            $msg = "$fieldName is $actual, which is greater than $expected";
        } else {
            $msg = "$fieldName is $actual, which is not greater than $expected";
        }
        
        return array($met, $msg);
    }
}
