<?php
namespace SugarRestHarness;

/**
 * ExpectationsEngine
 *
 * This class provides the methods for comparing the expected outcomes of a Job
 * (the 'expectations' property) to the actual data.
 *
 * Expectations should be specified in the job file with the name of the property
 * of the job (dot delimited for nested properties), followed by a comparison
 * operator, followed by an expected value. For example:
 *
 * $this->expectations['results.first_name']['empty'] = false;
 * $this->expectations['results']['count'] = 3;
 *
 * NOTE: JobAbstract defines default expectations for all jobs.
 *
 * This class will take a job as its constructor argument, and then run through
 * all of the expectations in the job and determine which expectations were met.
 *
 * All expectation operator methods (i.e. count, contains, equals, etc.) will return
 * a tuple, like this:
 * (
    boolean true if expectation was met, false if not,
    msg describing expectation
   )
 */
class ExpectationsEngine
{
    protected $job;
    protected $exceptions = array();
    
    
    /**
     * __construct()
     *
     * @param JobAbstract $job - the job to compare expectations to results on.
     */
    public function __construct($job)
    {
        $this->setJob($job);
    }
    
    
    /**
     * setJob()
     *
     * @param JobAbstract $job - the job to compare expectations to results on.
     */
    public function setJob($job)
    {
        $this->job = $job;
    }
    
    
    /**
     * compareActualToExpected()
     *
     * Compares the expecations specified in the job with the actual data for that
     * Job.
     */
    public function compareActualToExpected()
    {
        foreach ($this->job->expectations as $fieldName => $expectations) {
            $actualValue = $this->job->get($fieldName);
            foreach ($expectations as $operator => $expectedValue) {
                try {
                    list($met, $msg) = $this->executeMethod($operator, $fieldName, $actualValue, $expectedValue);
                } catch (Exception $e) {
                    $this->storeException($e);
                    continue;
                }
            }
        }
    }
    
    
    /**
     * executeMethod()
     *
     * Executes the named method and returns the results. 
     * 
     * @param string $methodName - the name of a method on this class.
     * @param string $fieldName - name of the field being checked.
     * @param mixed $actualValue - the actual value on the results object being checked.
     * @param mixed $expectedValue - what the job expected $fieldName to be set to.
     * @return array - an array containing a boolean indicating whether the expectation
     *  was met or not, and a string describing the expectation check result.
     * @throws ExpectationMethodDoesNotExist if the named method does not exist in 
     *  this class.
     */
    public function executeMethod($methodName, $fieldName, $actualValue, $expectedValue)
    {
        if (method_exists($this, $methodName)) {
            $results = $this->$methodName($fieldName, $actualValue, $expectedValue);
            $this->job->addExpectationDelta($results[0], $results[1]);
        } else {
            throw new ExpectationMethodDoesNotExist($this->job->jobClass, $fieldName, $methodName);
        }
        
        return $results;
    }
    
    
    /**
     * storeException()
     *
     * Stores an exception object in the exceptions array for future reference.
     *
     * @param Exception $e - an exception object.
     */
    public function storeException($e)
    {
        $this->job->storeException($e);
    }
    
    
    /**
     * getExceptions()
     *
     * Returns the array of all exceptions objects.
     *
     * @return array - array of Exceptions.
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    
    /**
     * contains()
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
    public function contains($fieldName, $actual, $expected)
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
     * @see contains()
     */
    public function not_contains($fieldName, $actual, $expected)
    {
        list($containsMet, $msg) = $this->contains($fieldName, $actual, $expected);
        $met = !$containsMet;
        return array($met, $msg);
    }
    
    
    /**
     * equals()
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
    public function equals($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $met = ($actual === $expected);
        
        if ($met) {
            $msg = "$fieldName equals '$expected'.";
        } else {
            $msg = "$fieldName is '$actual', and not expected '$expected'.";
        }
        
        return array($met, $msg);
    }
    
    
    /**
     * not_equals()
     *
     * Checks for non-equality between expected and actual.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function not_equals($fieldName, $actual, $expected)
    {
        list($equalsMet, $msg) = $this->equals($fieldName, $actual, $expected);
        $met = !$equalsMet;
        
        if ($met) {
            $msg = "$fieldName is '$actual', which does not equal '$expected'.";
        } else {
            $msg = "$fieldName equals '$expected'.";
        }
        
        return array($met, $msg);
    }
    
    
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
    public function starts($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $actualStr = (string) $actual;
        $expectedStr = (string) $expected;
        
        if (stripos($actual, $expected) === 0) {
            $met = true;
            $msg = "$actualStr begins with '$expectedStr'";
        } else {
            $msg = "$actualStr does not being with '$expectedStr'";
        }
        
        return array($met, $msg);
    }
    
    
    /**
     * is_empty()
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
    public function is_empty($fieldName, $actual, $expected)
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
    
    
    /**
     * count()
     *
     * Compares the number of elements in an array to the expected value. 
     * $actual must be an array, and $expected must be an integer.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param array $actual - the actual value from a job.
     * @param integer $expected - the expected number of elements in $actual.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function count($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $count = count($actual);
        $met = ($count == $expected);
        
        if ($met) {
            $msg = "$fieldName contains $expected elements";
        } else {
            $msg = "$fieldName contains $count elements but job expected $expected";
        }
        
        return array($met, $msg);
    }
    
    
    /**
     * gt()
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
    public function gt($fieldName, $actual, $expected)
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
    
    
    /**
     * lt()
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
    public function lt($fieldName, $actual, $expected)
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
    
    
    /**
     * gte()
     *
     * Checks to see if the actual value is greater than or equal to the expected value.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function gte($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $met = ($actual >= $expected);
        
        if ($met) {
            $msg = "$fieldName is $actual, which is greater than or equal to $expected";
        } else {
            $msg = "$fieldName is $actual, which is not greater than or equal to $expected";
        }
        
        return array($met, $msg);
    }
    
    
    /**
     * lte()
     *
     * Checks to see if the actual value is less than or equal to the expected value.
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
     */
    public function lte($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        $met = ($actual <= $expected);
        
        if ($met) {
            $msg = "$fieldName is $actual, which is less than or equal to $expected";
        } else {
            $msg = "$fieldName is $actual, which is not less than or equal to $expected";
        }
        
        return array($met, $msg);
    }
    
    
    /**
     *
     *
     *
     *
     * @param string $fieldName - the name of the field we're checking.
     * @param mixed $actual - the actual value from a job.
     * @param mixed $expected - the expected value from a job.
     * @return array - a tuple consisting of a boolean, indicated whether the
     *  expectation was met, and a string describing how the expectation was or
     *  was not met.
    public function ($fieldName, $actual, $expected)
    {
        $met = false;
        $msg = '';
        
        return array($met, $msg);
    }
     */
}
