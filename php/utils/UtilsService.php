<?php namespace Anon\Utils;

/**
 * Collection of utility functions we want to make available globally in the app.
 */
class UtilsService {

    /**
     * Checks if the passed array is associative.
     * Associativeness is defined as an array containing either
     * either non-numeric, or non-sequential keys.
     *
     * @example
     *
     * isAssoc([0 => 'a', 1 => 'b', 2 => 'c']) // false
     *
     * isAssoc([0 => 'a', 1 => 'b', 3 => 'c']) // true
     *
     * isAssoc([1 => 'a', 2 => 'b', 3 => 'c']) // true
     *
     * isAssoc([0 => 'a', 1 => 'b', 'c' => 'd']) // true
     *
     * isAssoc(['a' => 0, 'b' => 2, 'c' => 3]) // true
     *
     * @param  mixed[mixed]  $array
     * @return boolean
     */
    public function isAssoc(array $array)
    {
        return !$this->isSeq($array);
    }

    /**
     * Checks if the passed array is sequential.
     * Sequentiality is defined as an array containing
     * strictly sequential keys, beginning from 0.
     *
     * @example
     *
     * isSeq([0 => 'a', 1 => 'b', 2 => 'c']) // true
     *
     * isSeq([0 => 'a', 1 => 'b', 3 => 'c']) // false
     *
     * isSeq([1 => 'a', 2 => 'b', 3 => 'c']) // false
     *
     * isSeq([0 => 'a', 1 => 'b', 'c' => 'd']) // false
     *
     * isSeq(['a' => 0, 'b' => 2, 'c' => 3]) // false
     *
     * @param  mixed[mixed]  $array
     * @return boolean
     */
    public function isSeq(array $array)
    {
        $index = 0;
        foreach ($array as $key => $val) {
            if ($key !== $index) {
                return false;
            }
            $index++;
        }
        return true;
    }

    /**
     * Groups the provided array according to a selector (key or callback).
     * Supports arrays of arrays or objects.
     *
     * @example
     *
     * groupBy([
     *     ['foo' => 'a'],
     *     ['foo' => 'b']
     * ], 'foo')
     *
     * Returns
     * ['a' => [['foo' => 'a']], 'b' => [['foo' => 'b']]]
     *
     * groupBy([
     *     ['foo' => 'a'],
     *     ['bar' => 'b']
     * ], function($element) {
     *     return isset($element['foo']) ?
     *         $element['foo']:
     *         $element['bar'];
     * });
     *
     * Returns
     * ['a' => [['foo' => 'a']], 'b' => [['bar' => 'b']]]
     *
     * @param  array|object[mixed]  $array     Array to perform grouping on.
     * @param  string|int|Closure   $selector  Callback or key to group by.
     *                                         Callback is called with the current
     *                                         collection item as the first argument.
     *
     * @return mixed[int][string]  Array of arrays grouped by selector.
     */
    public function groupBy(array $array, $selector) {
        if (empty($array)) {
            return [];
        }

        $result = [];
        if (is_string($selector) || is_numeric($selector)) {
            foreach ($array as $element) {
                if (is_array($element)) {
                    $key = $element[$selector];
                } else if (is_object($element)) {
                    $key = $element->$selector;
                } else {
                    throw new \UnexpectedValueException(
                        "groupBy() expects an array of objects or arrays, got '" . gettype($element) . "'."
                    );
                }
                $result[$key][] = $element;
            }
        } else if ($selector instanceof \Closure) {
            foreach ($array as $element) {
                $result[$selector($element)][] = $element;
            }
        } else {
            throw new \InvalidArgumentException(
                "groupBy() expects second argument to be of type callable or string, got '".gettype($selector)."'."
            );
        }
        return $result;
    }

    /**
     * Checks all elements of the array against the filter and
     * returns true if all return true.
     *
     * @example
     *
     * These are all valid values for $filter:
     * - 'is_string'
     * - function($a)
     * - function($a, $b)
     * - function($a, $b, $c)
     * - 'SomeClass::someStaticMethod'
     *
     * @param  mixed[int] $arr     Array to check. Empty arrays return false.
     * @param  callable   $filter  Filter to check by - can be a callable
     *                             string or closure. Function signature is:
     *                             (bool) function($val [, $key = null, $array = []])
     *
     * @return boolean
     */
    public function every(array $arr, callable $filter)
    {
        return $this->arrayFilterCheck($arr, $filter, false);
    }

    /**
     * Checks the array against the filter and
     * returns true if at least one element returns true.
     *
     * @example
     *
     * These are all valid values for $filter:
     * - 'is_string'
     * - function($a)
     * - function($a, $b)
     * - function($a, $b, $c)
     * - 'SomeClass::someStaticMethod'
     *
     * @param  mixed[int] $arr     Array to check. Empty arrays return false.
     * @param  callable   $filter  Filter to check by - can be a callable
     *                             string or closure. Function signature is:
     *                             (bool) function($val [, $key = null, $array = []])
     *
     * @return boolean
     */
    public function some(array $arr, $filter)
    {
        return $this->arrayFilterCheck($arr, $filter, true);
    }

    /**
     * Provides the internal functionality for running a conditional
     * check on an array's elements. Used by every() and some()
     * to efficiently calculate if an array passes a filter.
     *
     * @param  array     $array   The array to check.
     * @param  callable  $filter  Filter to check by - can be a callable string
     *                            or closure, accepting up to three arguments.
     *                            Function signature is:
     *                            (bool) function($val [, $key = null, $array = []])
     * @param  boolean  $flag     The filter return value to watch for. If this value
     *                            is returned from a filter, return early with the value.
     *                            If not, return with the inverse at the end of the loop.
     *
     * @return boolean
     */
    protected function arrayFilterCheck(array $arr, callable $filter, $flag)
    {
        if (empty($arr)) {
            return false;
        }

        // Count number of expected params in the filter.
        // This is to make sure we can pass internal
        // methods such as `is_string` as filters, which
        // expect one argument, while still supporting passing
        // 1-3 arguments to custom closures.
        // Class methods need to be handled separately.
        if (is_string($filter) &&
            count($parts = explode('::', $filter)) > 1
        ) {
            // Calling method names directly does not work for class methods -
            // but call_user_func_array is ~30% slower. Therefore we only do this
            // if we're passed a class method.
            $filter = function() use ($filter) {
                return call_user_func_array($filter, func_get_args());
            };

            $params = (new \ReflectionMethod($parts[0], $parts[1]))->getNumberOfParameters();
        } else {
            $params = (new \ReflectionFunction($filter))->getNumberOfParameters();
        }

        // Number of params doesn't change,
        // more efficient to just check once and
        // adapt the for-loop instead.
        if ($params === 1) {
            foreach ($arr as $val) {
                if ($filter($val) === $flag) {
                    return $flag;
                }
            }
        } else if ($params === 2) {
            foreach ($arr as $key => $val) {
                if ($filter($val, $key) === $flag) {
                    return $flag;
                }
            }
        } else if ($params === 3) {
            foreach ($arr as $key => $val) {
                if ($filter($val, $key, $arr) === $flag) {
                    return $flag;
                }
            }
        } else {
            $callee = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            throw new \InvalidArgumentException($callee[1]["function"] .
                "expects the second argument to be a callable accepting 1-3 arguments,".
                "the passed callable takes $params arguments."
            );
        }

        return !$flag;
    }
}
