<?php
namespace SugarRestHarness\Expectations;

class Is_empty extends \SugarRestHarness\Expectations\ExpectationsAbstract implements \SugarRestHarness\Expectations\ExpectationsInterface
{
    /**
     * test()
     *
     * Checks to see if the $fieldName's actual value was empty. Expected value
     * should be a boolean in this case: true if empty is expected, false if
     * empty is not expected.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param boolean $expected - true if empty is expected, false otherwise.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function test($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $empty = empty($actual);
        $expectedBool = (boolean) $expected;
        $met = $empty === $expectedBool;
        
        if ($expected) {
            if ($met) {
                $msg = "$fieldName is empty";
            } else {
                $msg = "$fieldName is not empty";
            }
        } else {
            if (!$met) {
                $msg = "$fieldName is empty";
            } else {
                $msg = "$fieldName is not empty";
            }
        }
        
        return array($met, $msg);
    }
}