<?php
namespace SugarRestHarness\Expectations;

require_once("lib/Expectations/ExpectationsAbstract.php");

class Lt extends \SugarRestHarness\Expectations\ExpectationsAbstract implements ExpectationsInterface
{
    /**
     * test()
     *
     * Checks to see if the actual value is less than the expected value.
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
        
        $met = ($actual < $expected);
        
        if ($met) {
            $msg = "$fieldName is $actual, which is less than $expected";
        } else {
            $msg = "$fieldName is $actual, which is not less than $expected";
        }
        
        return array($met, $msg);
    }
}
