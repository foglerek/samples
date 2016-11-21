<?php

use Anon\Utils\UtilsService;

class UtilsServiceTest extends PHPUnit_Framework_TestCase
{
    protected $utils;

    public function setUp()
    {
        parent::setUp();

        $this->utils = new UtilsService();
    }

    /**
     * String keyed array is associative.
     */
    public function test_isAssoc_string_keys()
    {
        $this->assertEquals(true, $this->utils->isAssoc([
            'a' => 0,
            'b' => 2,
            'c' => 3
        ]));
    }

    /**
     * Sequential array is not associative.
     */
    public function test_isAssoc_sequential()
    {
        $this->assertEquals(false, $this->utils->isAssoc([
            0 => 'a',
            1 => 'b',
            2 => 'c'
        ]));
    }

    /**
     * Non-sequential array is associative.
     */
    public function test_isAssoc_non_sequential()
    {
        $this->assertEquals(true, $this->utils->isAssoc([
            0 => 'a',
            1 => 'b',
            3 => 'c'
        ]));
    }

    /**
     * Non-zero-indexed sequential array is associative.
     */
    public function test_isAssoc_non_zero_indexed()
    {
        $this->assertEquals(true, $this->utils->isAssoc([
            1 => 'a',
            2 => 'b',
            3 => 'c'
        ]));
    }

    /**
     * Numeric/string keys is associative.
     */
    public function test_isAssoc_mixed_keys()
    {
        $this->assertEquals(true, $this->utils->isAssoc([
            0 => 'a',
            1 => 'b',
            'c' => 'c'
        ]));
    }

    /**
     * String keyed array is not sequential.
     */
    public function test_isSeq_string_keys()
    {
        $this->assertEquals(false, $this->utils->isSeq([
            'a' => 0,
            'b' => 2,
            'c' => 3
        ]));
    }

    /**
     * Sequential array should be sequential.
     */
    public function test_isSeq_sequential()
    {
        $this->assertEquals(true, $this->utils->isSeq([
            0 => 'a',
            1 => 'b',
            2 => 'c'
        ]));
    }

    /**
     * Broken index should not be sequential.
     */
    public function test_isSeq_non_sequential()
    {
        $this->assertEquals(false, $this->utils->isSeq([
            0 => 'a',
            1 => 'b',
            3 => 'c'
        ]));
    }

    /**
     * Non-zero-indexed should not be sequential.
     */
    public function test_isSeq_non_zero_indexed()
    {
        $this->assertEquals(false, $this->utils->isSeq([
            1 => 'a',
            2 => 'b',
            3 => 'c'
        ]));
    }

    /**
     * Non-numeric keys in index should not be sequential.
     */
    public function test_isSeq_mixed_keys()
    {
        $this->assertEquals(false, $this->utils->isSeq([
            0 => 'a',
            1 => 'b',
            'c' => 'c'
        ]));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_groupBy_invalid_selector()
    {
        $this->utils->groupBy([
            ['foo' => 'a'],
            ['foo' => 'b']
        ], []);
    }

    /**
     * Arrays can be grouped by values in numeric keys.
     */
    public function test_groupBy_numeric_selector()
    {
        $this->assertEquals(
            [
                'a' => [[52 => 'a']],
                'b' => [[52 => 'b']]
            ],
            $this->utils->groupBy([
                [52 => 'a'],
                [52 => 'b']
            ], 52)
        );
    }

    /**
     * Arrays can be grouped by values in string keys.
     */
    public function test_groupBy_string_selector()
    {
        $this->assertEquals(
            [
                'a' => [['foo' => 'a']],
                'b' => [['foo' => 'b']]
            ],
            $this->utils->groupBy([
                ['foo' => 'a'],
                ['foo' => 'b']
            ], 'foo')
        );
    }

    /**
     * Arrays can be grouped by callable return value.
     */
    public function test_groupBy_callable_selector()
    {
        $this->assertEquals(
            [
                'a' => [['foo' => 'a']],
                'b' => [['bar' => 'b']]
            ],
            $this->utils->groupBy([
                ['foo' => 'a'],
                ['bar' => 'b']
            ], function($element) {
                return isset($element['foo']) ? $element['foo'] : $element['bar'];
            })
        );
    }

    /**
     * Empty arrays do not throw errors.
     */
    public function test_groupBy_empty_array()
    {
        $this->assertEquals(
            [],
            $this->utils->groupBy([], 'foo')
        );
    }

    /**
     * Arrays of other values than objects and arrays
     * throw UnexpectedValueException.
     *
     * @expectedException UnexpectedValueException
     */
    public function test_groupBy_wrong_array_contents()
    {
        $this->utils->groupBy(['foo' => 'a', 'bar' => 'b'], 'foo');
    }

    /**
     * GroupBy can handle arrays of both objects and arrays.
     */
    public function test_groupBy_supports_objects_arrays()
    {
        $this->assertEquals(
            [
                'a' => [
                    (object) ['foo' => 'a']
                ],
                'b' => [
                    ['foo' => 'b']
                ]
            ],
            $this->utils->groupBy([
                (object) ['foo' => 'a'],
                ['foo' => 'b']
            ], 'foo')
        );
    }

    /**
     * Empty array returns false.
     */
    public function test_every_empty_array()
    {
        $this->assertEquals(false, $this->utils->every([], function() {}));
    }

    /**
     * Array with elements that do not pass the filter
     * return false.
     */
    public function test_every_failing_elements()
    {
        $this->assertEquals(false, $this->utils->every(
            ['foo', 'bar', 3],
            function($val) {
                return is_string($val);
            }
        ));
    }

    /**
     * Array with all elements passing the filter
     * return true.
     */
    public function test_every_every_passing_elements()
    {
        $this->assertEquals(true, $this->utils->every(
            ['foo', 'bar', 'baz'],
            function($val) {
                return is_string($val);
            }
        ));
    }

    /**
     * Filter can be a callable string.
     */
    public function test_every_callable_string()
    {
        $this->assertEquals(true, $this->utils->every(
            ['foo', 'bar', 'baz'],
            'is_string'
        ));
    }

    /**
     * Callback gets passed value as argument.
     */
    public function test_every_callback_with_one_arg()
    {
        $arr = ['foo'];
        $this->utils->every(
            $arr,
            function($val) use ($arr) {
                $this->assertEquals($arr[0], $val);
            }
        );
    }

    /**
     * Callback gets passed value, key as arguments.
     */
    public function test_every_callback_with_two_args()
    {
        $arr = ['foo' => 'bar'];
        $this->utils->every(
            $arr,
            function($val, $key) use ($arr) {
                $this->assertEquals($arr['foo'], $val);
                $this->assertEquals('foo', $key);
            }
        );
    }

    /**
     * Callback gets passed value, key, array as arguments.
     */
    public function test_every_callback_with_three_args()
    {
        $arr = ['foo' => 'bar'];
        $this->utils->every(
            $arr,
            function($val, $key, $full) use ($arr) {
                $this->assertEquals($arr['foo'], $val);
                $this->assertEquals('foo', $key);
                $this->assertEquals($arr, $full);
            }
        );
    }

    /**
     * Passing a callback taking more than three arguments raises
     * an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function test_every_callback_with_more_than_three_args()
    {
        $this->utils->every(
            ['foo', 'bar'],
            function($val, $key, $full, $baz) {}
        );
    }

    /**
     * Passing a callback taking no parameters raises
     * an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function test_every_callback_with_zero_args()
    {
        $this->utils->every(
            ['foo', 'bar'],
            function() {}
        );
    }

    /**
     * Static class methods can be passed as filters.
     */
    public function test_every_callable_class_method()
    {
        $this->assertEquals(
            true,
            $this->utils->every(
                [true, 1],
                'TestClass::foo'
            )
        );
    }

    /**
     * Empty array returns false.
     */
    public function test_some_empty_array()
    {
        $this->assertEquals(false, $this->utils->some([], function() {}));
    }

    /**
     * Array with no elements that pass the filter
     * return false.
     */
    public function test_some_no_matching_elements()
    {
        $this->assertEquals(false, $this->utils->some(
            [1, 2, 3],
            function($val) {
                return is_string($val);
            }
        ));
    }

    /**
     * Array with some elements passing the filter
     * return true.
     */
    public function test_some_every_passing_elements()
    {
        $this->assertEquals(true, $this->utils->some(
            [1, 2, 'baz'],
            function($val) {
                return is_string($val);
            }
        ));
    }

    /**
     * Filter can be a callable string.
     */
    public function test_some_callable_string()
    {
        $this->assertEquals(true, $this->utils->some(
            [1, 2, 'baz'],
            'is_string'
        ));
    }

    /**
     * Callback gets passed value as argument.
     */
    public function test_some_callback_with_one_arg()
    {
        $arr = ['foo'];
        $this->utils->some(
            $arr,
            function($val) use ($arr) {
                $this->assertEquals($arr[0], $val);
            }
        );
    }

    /**
     * Callback gets passed value, key as arguments.
     */
    public function test_some_callback_with_two_args()
    {
        $arr = ['foo' => 'bar'];
        $this->utils->some(
            $arr,
            function($val, $key) use ($arr) {
                $this->assertEquals($arr['foo'], $val);
                $this->assertEquals('foo', $key);
            }
        );
    }

    /**
     * Callback gets passed value, key, array as arguments.
     */
    public function test_some_callback_with_three_args()
    {
        $arr = ['foo' => 'bar'];
        $this->utils->some(
            $arr,
            function($val, $key, $full) use ($arr) {
                $this->assertEquals($arr['foo'], $val);
                $this->assertEquals('foo', $key);
                $this->assertEquals($arr, $full);
            }
        );
    }

    /**
     * Passing a callback taking more than three arguments raises
     * an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function test_some_callback_with_more_than_three_args()
    {
        $this->utils->some(
            ['foo', 'bar'],
            function($val, $key, $full, $baz) {}
        );
    }

    /**
     * Passing a callback taking no parameters raises
     * an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function test_some_callback_with_zero_args()
    {
        $this->utils->some(
            ['foo', 'bar'],
            function() {}
        );
    }

    /**
     * Static class methods can be passed as filters.
     */
    public function test_some_callable_class_method()
    {
        $this->assertEquals(
            true,
            $this->utils->some(
                [false, 1],
                'TestClass::foo'
            )
        );
    }

}

class TestClass {
    public static function foo($val) {
        return (bool) $val;
    }
}
