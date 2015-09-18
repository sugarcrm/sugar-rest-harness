<?php
namespace SugarRestHarness\Expectations;

require_once("lib/Expectations/ExpectationsAbstract.php");
require_once("lib/Expectations/Contains.php");

class Not_contains extends \SugarRestHarness\Expectations\Contains implements ExpectationsInterface
{
    
    /**
     * not_contains()
     *
     * Returns the opposite of contains().
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     * @see class Contains()
     */
    public function test($fieldName, $actual, $expected)
    {
        list($met, $msg) = parent::test($fieldName, $actual, $expected);
        return array(!$met, $msg);
    }
}