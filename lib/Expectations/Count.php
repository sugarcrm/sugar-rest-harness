<?php
namespace SugarRestHarness\Expectations;

require_once("lib/Expectations/ExpectationsAbstract.php");

class Count extends \SugarRestHarness\Expectations\ExpectationsAbstract implements ExpectationsInterface
{
    /**
     * test()
     *
     * Compares the number of elements in the an array to the expected value. 
     * $actual must be an array, and $expected must be an integer.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param array $actual - the actual value (array) from a job.
     * @param integer $expected - the expected number of elements in $actual.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function test($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        if (!is_array($actual)) {
            $msg = "$fieldName is not an array, cannot test for a count of '$expected'";
            return array($met, $msg);
        }
        
        $count = count($actual);
        $met = ($count == $expected);
        
        if ($met) {
            $msg = "$fieldName contains $expected elements";
        } else {
            $msg = "$fieldName contains $count elements but job expected $expected";
        }
        
        return array($met, $msg);
    }
}
