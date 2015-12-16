<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Expectations;

class ExpectationsAbstract implements ExpectationsInterface
{
    public function __construct()
    {
    }
    
    
    /**
     * test()
     *
     * Expected to be over-written by specific expectation classes. However,
     * the args and return MUST be consistent across all children of this class.
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
        
    }
}
