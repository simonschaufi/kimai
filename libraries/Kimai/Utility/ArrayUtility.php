<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class with helper functions for array handling
 */
class Kimai_Utility_ArrayUtility
{
    /**
     * Checks if a given path exists in array
     *
     * Example:
     * - array:
     * array(
     *   'foo' => array(
     *     'bar' = 'test',
     *   )
     * );
     * - path: 'foo/bar'
     * - return: TRUE
     *
     * @param array $array Given array
     * @param string $path Path to test, 'foo/bar/foobar'
     * @param string $delimiter Delimiter for path, default /
     * @return bool TRUE if path exists in array
     */
    public static function isValidPath(array $array, $path, $delimiter = '/')
    {
        $isValid = true;
        try {
            // Use late static binding to enable mocking of this call in unit tests
            static::getValueByPath($array, $path, $delimiter);
        } catch (\RuntimeException $e) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * Returns a value by given path
     *
     * Example
     * - array:
     * array(
     *   'foo' => array(
     *     'bar' => array(
     *       'baz' => 42
     *     )
     *   )
     * );
     * - path: foo/bar/baz
     * - return: 42
     *
     * If a path segments contains a delimiter character, the path segment
     * must be enclosed by " (double quote), see unit tests for details
     *
     * @param array $array Input array
     * @param array|string $path Path within the array
     * @param string $delimiter Defined path delimiter, default /
     * @return mixed
     * @throws \RuntimeException if the path is empty, or if the path does not exist
     * @throws \InvalidArgumentException if the path is neither array nor string
     */
    public static function getValueByPath(array $array, $path, $delimiter = '/')
    {
        // Extract parts of the path
        if (is_string($path)) {
            if ($path === '') {
                throw new \RuntimeException('Path must not be empty', 1341397767);
            }
            $path = str_getcsv($path, $delimiter);
        } elseif (!is_array($path)) {
            throw new \InvalidArgumentException('getValueByPath() expects $path to be string or array, "' . gettype($path) . '" given.', 1476557628);
        }
        // Loop through each part and extract its value
        $value = $array;
        foreach ($path as $segment) {
            if (array_key_exists($segment, $value)) {
                // Replace current value with child
                $value = $value[$segment];
            } else {
                // Fail if key does not exist
                throw new \RuntimeException('Path does not exist in array', 1341397869);
            }
        }
        return $value;
    }

    /**
     * Modifies or sets a new value in an array by given path
     *
     * Example:
     * - array:
     * array(
     *   'foo' => array(
     *     'bar' => 42,
     *   ),
     * );
     * - path: foo/bar
     * - value: 23
     * - return:
     * array(
     *   'foo' => array(
     *     'bar' => 23,
     *   ),
     * );
     *
     * @param array $array Input array to manipulate
     * @param string|array $path Path in array to search for
     * @param mixed $value Value to set at path location in array
     * @param string $delimiter Path delimiter
     * @return array Modified array
     * @throws \RuntimeException
     */
    public static function setValueByPath(array $array, $path, $value, $delimiter = '/')
    {
        if (is_string($path)) {
            if ($path === '') {
                throw new \RuntimeException('Path must not be empty', 1341406194);
            }
            // Extract parts of the path
            $path = str_getcsv($path, $delimiter);
        } elseif (!is_array($path) && !$path instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('setValueByPath() expects $path to be string, array or an object implementing \\ArrayAccess, "' . (is_object($path) ? get_class($path) : gettype($path)) . '" given.', 1478781081);
        }
        // Point to the root of the array
        $pointer = &$array;
        // Find path in given array
        foreach ($path as $segment) {
            // Fail if the part is empty
            if ($segment === '') {
                throw new \RuntimeException('Invalid path segment specified', 1341406846);
            }
            // Create cell if it doesn't exist
            if (!array_key_exists($segment, $pointer)) {
                $pointer[$segment] = [];
            }
            // Set pointer to new cell
            $pointer = &$pointer[$segment];
        }
        // Set value of target cell
        $pointer = $value;
        return $array;
    }

    /**
     * Remove a sub part from an array specified by path
     *
     * @param array $array Input array to manipulate
     * @param string $path Path to remove from array
     * @param string $delimiter Path delimiter
     * @return array Modified array
     * @throws \RuntimeException
     */
    public static function removeByPath(array $array, $path, $delimiter = '/')
    {
        if (!is_string($path)) {
            throw new \RuntimeException('Path must be a string', 1371757719);
        }
        if ($path === '') {
            throw new \RuntimeException('Path must not be empty', 1371757718);
        }
        // Extract parts of the path
        $path = str_getcsv($path, $delimiter);
        $pathDepth = count($path);
        $currentDepth = 0;
        $pointer = &$array;
        // Find path in given array
        foreach ($path as $segment) {
            $currentDepth++;
            // Fail if the part is empty
            if ($segment === '') {
                throw new \RuntimeException('Invalid path segment specified', 1371757720);
            }
            if (!array_key_exists($segment, $pointer)) {
                throw new \RuntimeException('Path segment ' . $segment . ' does not exist in array', 1371758436);
            }
            if ($currentDepth === $pathDepth) {
                unset($pointer[$segment]);
            } else {
                $pointer = &$pointer[$segment];
            }
        }
        return $array;
    }

    /**
     * Sorts an array recursively by key
     *
     * @param array $array Array to sort recursively by key
     * @return array Sorted array
     */
    public static function sortByKeyRecursive(array $array)
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $array[$key] = self::sortByKeyRecursive($value);
            }
        }
        return $array;
    }

    /**
     * Exports an array as string.
     * Similar to var_export(), but representation follows the PSR-2 and Kimai core CGL.
     *
     * See unit tests for detailed examples
     *
     * @param array $array Array to export
     * @param int $level Internal level used for recursion, do *not* set from outside!
     * @return string String representation of array
     * @throws \RuntimeException
     */
    public static function arrayExport(array $array = array(), $level = 0)
    {
        $lines = 'array(' . LF;
        $level++;
        $writeKeyIndex = false;
        $expectedKeyIndex = 0;
        foreach ($array as $key => $value) {
            if ($key === $expectedKeyIndex) {
                $expectedKeyIndex++;
            } else {
                // Found a non integer or non consecutive key, so we can break here
                $writeKeyIndex = true;
                break;
            }
        }
        foreach ($array as $key => $value) {
            // Indention
            $lines .= str_repeat('    ', $level);
            if ($writeKeyIndex) {
                // Numeric / string keys
                $lines .= is_int($key) ? $key . ' => ' : '\'' . $key . '\' => ';
            }
            if (is_array($value)) {
                if (!empty($value)) {
                    $lines .= self::arrayExport($value, $level);
                } else {
                    $lines .= 'array(),' . LF;
                }
            } elseif (is_int($value) || is_float($value)) {
                $lines .= $value . ',' . LF;
            } elseif (is_null($value)) {
                $lines .= 'null' . ',' . LF;
            } elseif (is_bool($value)) {
                $lines .= $value ? 'true' : 'false';
                $lines .= ',' . LF;
            } elseif (is_string($value)) {
                // Quote \ to \\
                $stringContent = str_replace('\\', '\\\\', $value);
                // Quote ' to \'
                $stringContent = str_replace('\'', '\\\'', $stringContent);
                $lines .= '\'' . $stringContent . '\'' . ',' . LF;
            } else {
                throw new \RuntimeException('Objects are not supported', 1342294987);
            }
        }
        $lines .= str_repeat('    ', ($level - 1)) . ')' . ($level - 1 == 0 ? '' : ',' . LF);
        return $lines;
    }

    /**
     * Renumber the keys of an array to avoid leaps if keys are all numeric.
     *
     * Is called recursively for nested arrays.
     *
     * Example:
     *
     * Given
     *  array(0 => 'Zero' 1 => 'One', 2 => 'Two', 4 => 'Three')
     * as input, it will return
     *  array(0 => 'Zero' 1 => 'One', 2 => 'Two', 3 => 'Three')
     *
     * Will treat keys string representations of number (ie. '1') equal to the
     * numeric value (ie. 1).
     *
     * Example:
     * Given
     *  array('0' => 'Zero', '1' => 'One' )
     * it will return
     *  array(0 => 'Zero', 1 => 'One')
     *
     * @param array $array Input array
     * @param int $level Internal level used for recursion, do *not* set from outside!
     * @return array
     */
    public static function renumberKeysToAvoidLeapsIfKeysAreAllNumeric(array $array = [], $level = 0)
    {
        $level++;
        $allKeysAreNumeric = true;
        foreach ($array as $key => $_) {
            if (is_numeric($key) === false) {
                $allKeysAreNumeric = false;
                break;
            }
        }
        $renumberedArray = $array;
        if ($allKeysAreNumeric === true) {
            $renumberedArray = array_values($array);
        }
        foreach ($renumberedArray as $key => $value) {
            if (is_array($value)) {
                $renumberedArray[$key] = self::renumberKeysToAvoidLeapsIfKeysAreAllNumeric($value, $level);
            }
        }
        return $renumberedArray;
    }

    /**
     * Merges two arrays recursively and "binary safe" (integer keys are
     * overridden as well), overruling similar values in the original array
     * with the values of the overrule array.
     * In case of identical keys, ie. keeping the values of the overrule array.
     *
     * This method takes the original array by reference for speed optimization with large arrays
     *
     * The differences to the existing PHP function array_merge_recursive() are:
     *  * Keys of the original array can be unset via the overrule array. ($enableUnsetFeature)
     *  * Much more control over what is actually merged. ($addKeys, $includeEmptyValues)
     *  * Elements or the original array get overwritten if the same key is present in the overrule array.
     *
     * @param array $original Original array. It will be *modified* by this method and contains the result afterwards!
     * @param array $overrule Overrule array, overruling the original array
     * @param bool $addKeys If set to FALSE, keys that are NOT found in $original will not be set. Thus only existing value can/will be overruled from overrule array.
     * @param bool $includeEmptyValues If set, values from $overrule will overrule if they are empty or zero.
     * @param bool $enableUnsetFeature If set, special values "__UNSET" can be used in the overrule array in order to unset array keys in the original array.
     */
    public static function mergeRecursiveWithOverrule(array &$original, array $overrule, $addKeys = true, $includeEmptyValues = true, $enableUnsetFeature = true)
    {
        foreach ($overrule as $key => $_) {
            if ($enableUnsetFeature && $overrule[$key] === '__UNSET') {
                unset($original[$key]);
                continue;
            }
            if (isset($original[$key]) && is_array($original[$key])) {
                if (is_array($overrule[$key])) {
                    self::mergeRecursiveWithOverrule($original[$key], $overrule[$key], $addKeys, $includeEmptyValues, $enableUnsetFeature);
                }
            } elseif (
                ($addKeys || isset($original[$key])) &&
                ($includeEmptyValues || $overrule[$key])
            ) {
                $original[$key] = $overrule[$key];
            }
        }
        reset($original);
    }

    /**
     * Filters keys off from first array that also exist in second array. Comparison is done by keys.
     * This method is a recursive version of php array_diff_assoc()
     *
     * @param array $array1 Source array
     * @param array $array2 Reduce source array by this array
     * @return array Source array reduced by keys also present in second array
     */
    public static function arrayDiffAssocRecursive(array $array1, array $array2)
    {
        $differenceArray = array();
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                $differenceArray[$key] = $value;
            } elseif (is_array($value)) {
                if (is_array($array2[$key])) {
                    $differenceArray[$key] = self::arrayDiffAssocRecursive($value, $array2[$key]);
                }
            }
        }
        return $differenceArray;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function setKeyFromValue(array $array)
    {
        $newArray = array();
        foreach ($array as $item) {
            $newArray[$item] = $item;
        }
        return $newArray;
    }
}
