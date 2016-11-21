# About
This is a Utility class with various methods for working with PHP arrays, aimed to cover a variety of inputs and be as efficient as possible.

# Examples

### isAssoc()
Tests for associative arrays. Any array that is not sequential is defined as associative, that is:
- Has a non-zero index.
- Has holes in the index.
- Has any non-numeric key.

Example:

`isAssoc([0, 1, 2, 'c' => 'cat']) // true`

### isSeq()
Tests for sequential arrays. A sequential array needs to:
- Be zero-indexed
- Be strictly sequential.
- Contain strictly numeric keys.

Example:

`isSeq([0, 1, 2, 4]) // false`

### groupBy()
Groups collections (arrays of arrays or objects) by a provided selector (Closure, string or int).

Example:

`groupBy([['foo' => 'a', 'bar' => 'b'], ['foo' => 'a', 'bar' => 'c']], 'foo')`

Returns `['a' => [['foo' => 'a', 'bar' => 'b'], ['foo' => 'a', 'bar' => 'c']]];`

### every()
Checks an array against a filter and returns true if all elements of the array return true for the filter.
The filter can be any callable, e.g:
- `every([...], 'is_string');`
- `every([...], 'SomeClass::someStaticMethod');`
- `every([...], function($val, $key, $arr) { ... });`

Example:

`every(['a', 'b', 'c', 3], 'is_string') // false`

### some()
Checks an array against a filter and returns true if at least one element in the array returns true for the filter.
The filter can be any callable, e.g:
- `every([...], 'is_string');`
- `every([...], 'SomeClass::someStaticMethod');`
- `every([...], function($val, $key, $arr) { ... });`

Example:

`every([1, 2, 3, 'c'], 'is_string') // true`

# Testing
Begin by installing dependencies:

```
composer install
```

Then execute `composer test` in your terminal to run the automated unit tests for this code.
