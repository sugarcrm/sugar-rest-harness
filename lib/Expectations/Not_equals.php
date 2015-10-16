<?php
namespace SugarRestHarness\Expectations;

class Not_equals extends \SugarRestHarness\Expectations\Equals implements ExpectationsInterface
{
    
    /**
     * test()
     *
     * Checks for equality between expected and actual.
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
        list($equalsMet, $msg) = parent::test($fieldName, $actual, $expected);
        $met = !$equalsMet;
        
        if ($met) {
            $msg = "$fieldName is '$actual', which does not equal '$expected'.";
        } else {
            $msg = "$fieldName equals '$expected', but should not.";
        }
        
        return array($met, $msg);
    }
}