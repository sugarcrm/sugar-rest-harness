<?php
namespace SugarRestHarness\Expectations;

class Contains extends \SugarRestHarness\Expectations\ExpectationsAbstract implements ExpectationsInterface
{    
    /**
     * test()
     *
     * Checks to see if $expected is contained in $actual.
     * For strings, if $expected is in $actual, the expectation is met.
     * Numbers will be treated like strings. 
     * For arrays, if $expected is an element in $actual, the expectation is met.
     * Objects are not supported.
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
        $type = gettype($actual);
        switch ($type) {
            case 'string':
            case 'integer':
            case 'double':
                $actual = (string) $actual;
                $expected = (string) $expected;
                $met = stripos($actual, $expected) !== false;
                break;
            case 'array':
                $met = in_array($expected, $actual);
                $actual = 'an array';
                break;
            case 'NULL':
                $met = is_null($expected);
                break;
            default:
                $met = false;
                $msg = "$fieldName is a $type, and cannot be checked using 'contains'.";
                break;
        }
        
        if (empty($msg)) {
            if ($met) {
                $msg = "$fieldName is '$actual', which contains '$expected'.";
            } else {
                $msg = "$fieldName is '$actual', which does not contain '$expected'.";
            }
        }
        
        return array($met, $msg);
    }
}