<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Utility\Exception\InvalidTypeException;
use \Closure;

/**
 * Manipulates, manages and processes multiple types of result sets: objects and arrays.
 *
 * @package Titon\Utility
 */
class Hash {

	/**
	 * Determines the total depth of a multi-dimensional array or object.
	 * Has two methods of determining depth: based on recursive depth, or based on tab indentation (faster).
	 *
	 * @uses Titon\Utility\Converter
	 *
	 * @param array|object $set
	 * @return int
	 * @throws \Titon\Utility\Exception\InvalidTypeException
	 */
	public static function depth($set) {
		if (is_object($set)) {
			$set = Converter::toArray($set);

		} else if (!is_array($set)) {
			throw new InvalidTypeException('Value passed must be an array');
		}

		if (!$set) {
			return 0;
		}

		$depth = 1;

		foreach ($set as $value) {
			if (is_array($value)) {
				$count = self::depth($value) + 1;

				if ($count > $depth) {
					$depth = $count;
				}
			}
		}

		return $depth;
	}

	/**
	 * Calls a function for each key-value pair in the set.
	 * If recursive is true, will apply the callback to nested arrays as well.
	 *
	 * @param array $set
	 * @param \Closure $callback
	 * @param bool $recursive
	 * @return array
	 */
	public static function each($set, Closure $callback, $recursive = true) {
		foreach ((array) $set as $key => $value) {
			if (is_array($value) && $recursive) {
				$set[$key] = self::each($value, $callback, $recursive);
			} else {
				$set[$key] = $callback($value, $key);
			}
		}

		return $set;
	}

	/**
	 * Returns true if every element in the array satisfies the provided testing function.
	 *
	 * @param array $set
	 * @param \Closure $callback
	 * @return bool
	 */
	public static function every($set, Closure $callback) {
		foreach ((array) $set as $key => $value) {
			if (!$callback($value, $key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Expand an array to a fully workable multi-dimensional array, where the values key is a dot notated path.
	 *
	 * @param array $set
	 * @return array
	 */
	public static function expand($set) {
		$data = array();

		foreach ((array) $set as $key => $value) {
			$data = self::insert($data, $key, $value);
		}

		return $data;
	}

	/**
	 * Extract the value of an array, depending on the paths given, represented by Key.Key.Key notation.
	 * Can extract multiple values by passing an array of paths as the second argument.
	 *
	 * @param array $set
	 * @param string $path
	 * @return mixed
	 */
	public static function extract($set, $path) {
		if (!is_array($set) || !$set) {
			return null;
		}

		$search =& $set;
		$paths = explode('.', (string) $path);
		$total = count($paths);

		while ($total > 0) {
			$key = $paths[0];

			// Within the last path
			if ($total === 1) {
				return array_key_exists($key, $search) ? $search[$key] : null;

			// Break out of non-existent paths early
			} else if (!array_key_exists($key, $search) || !is_array($search[$key])) {
				return null;
			}

			$search =& $search[$key];
			array_shift($paths);
			$total--;
		}

		unset($search);

		return null;
	}

	/**
	 * Filter out all keys within an array that have an empty value, excluding 0 (string and numeric).
	 * If $recursive is set to true, will remove all empty values within all sub-arrays.
	 *
	 * @param array $set
	 * @param bool $recursive
	 * @param \Closure $callback
	 * @return array
	 */
	public static function filter($set, $recursive = true, Closure $callback = null) {
		$set = (array) $set;

		if ($recursive) {
			foreach ($set as $key => $value) {
				if (is_array($value)) {
					$set[$key] = self::filter($value, $recursive);
				}
			}
		}

		if ($callback === null) {
			$callback = function($var) {
				return ($var === 0 || $var === '0' || !empty($var));
			};
		}

		return array_filter($set, $callback);
	}

	/**
	 * Flatten a multi-dimensional array by returning the values with their keys representing their previous pathing.
	 *
	 * @param array $set
	 * @param string $path
	 * @return array
	 */
	public static function flatten($set, $path = null) {
		if ($path) {
			$path = $path . '.';
		}

		$data = array();

		foreach ((array) $set as $key => $value) {
			if (is_array($value)) {
				if ($value) {
					$data += self::flatten($value, $path . $key);
				} else {
					$data[$path . $key] = null;
				}
			} else {
				$data[$path . $key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Flip the array by replacing all array keys with their value, if the value is a string and the key is numeric.
	 * If the value is empty/false/null and $truncate is true, that key will be removed.
	 *
	 * @param array $set
	 * @param bool $recursive
	 * @param bool $truncate
	 * @return array
	 */
	public static function flip($set, $recursive = true, $truncate = true) {
		if (!is_array($set)) {
			return $set;
		}

		$data = array();

		foreach ($set as $key => $value) {
			$empty = ($value === '' || $value === false || $value === null);

			if (is_array($value)) {
				if ($recursive) {
					$data[$key] = self::flip($value, $truncate);
				}

			} else if (is_int($key) && !$empty) {
				$data[$value] = '';

			} else {
				if ($truncate && !$empty) {
					$data[$value] = $key;
				}
			}
		}

		return $data;
	}

	/**
	 * Get a value from the set. If they path doesn't exist, return null, or if the path is empty, return the whole set.
	 *
	 * @param array $set
	 * @param string $path
	 * @return mixed
	 */
	public static function get($set, $path = null) {
		if (!$path) {
			return $set;
		}

		return self::extract($set, $path);
	}

	/**
	 * Checks to see if a key/value pair exists within an array, determined by the given path.
	 *
	 * @param array $set
	 * @param string $path
	 * @return array
	 */
	public static function has($set, $path) {
		if (!is_array($set) || !$path) {
			return false;
		}

		$search =& $set;
		$paths = explode('.', (string) $path);
		$total = count($paths);

		while ($total > 0) {
			$key = $paths[0];

			// Within the last path
			if ($total === 1) {
				return array_key_exists($key, $search);

			// Break out of non-existent paths early
			} else if (!array_key_exists($key, $search) || !is_array($search[$key])) {
				return false;
			}

			$search =& $search[$key];
			array_shift($paths);
			$total--;
		}

		unset($search);

		return false;
	}

	/**
	 * Includes the specified key-value pair in the set if the key doesn't already exist.
	 *
	 * @param array $set
	 * @param string $path
	 * @param mixed $value
	 * @return array
	 */
	public static function inject($set, $path, $value) {
		if (self::has($set, $path)) {
			return $set;
		}

		return self::insert($set, $path, $value);
	}

	/**
	 * Inserts a value into the array set based on the given path.
	 *
	 * @param array $set
	 * @param string $path
	 * @param mixed $value
	 * @return array
	 */
	public static function insert($set, $path, $value) {
		if (!is_array($set) || !$path) {
			return $set;
		}

		$search =& $set;
		$paths = explode('.', $path);
		$total = count($paths);

		while ($total > 0) {
			$key = $paths[0];

			// Within the last path
			if ($total === 1) {
				$search[$key] = $value;

			// Break out of non-existent paths early
			} else if (!array_key_exists($key, $search) || !is_array($search[$key])) {
				$search[$key] = array();
			}

			$search =& $search[$key];
			array_shift($paths);
			$total--;
		}

		unset($search);

		return $set;
	}

	/**
	 * Checks to see if all values in the array are strings, returns false if not.
	 * If $strict is true, method will fail if there are values that are numerical strings, but are not cast as integers.
	 *
	 * @param array $set
	 * @param bool $strict
	 * @return bool
	 */
	public static function isAlpha($set, $strict = true) {
		return self::every($set, function($value) use ($strict) {
			if (!is_string($value)) {
				return false;
			}

			if ($strict) {
				if (is_string($value) && is_numeric($value)) {
					return false;
				}
			}

			return true;
		});
	}

	/**
	 * Checks to see if all values in the array are numeric, returns false if not.
	 *
	 * @param array $set
	 * @return bool
	 */
	public static function isNumeric($set) {
		return self::every($set, function($value) {
			return is_numeric($value);
		});
	}

	/**
	 * Returns the key of the specified value. Will recursively search if the first pass doesn't match.
	 *
	 * @param array $set
	 * @param mixed $match
	 * @return mixed
	 */
	public static function keyOf($set, $match) {
		$return = null;
		$isArray = array();

		foreach ((array) $set as $key => $value) {
			if ($value === $match) {
				$return = $key;
			}

			if (is_array($value)) {
				$isArray[] = $key;
			}
		}

		if (!$return && $isArray) {
			foreach ($isArray as $key) {
				if ($value = self::keyOf($set[$key], $match)) {
					$return = $key . '.' . $value;
				}
			}
		}

		return $return;
	}

	/**
	 * Works in a similar fashion to array_map() but can be used recursively as well as supply arguments for the function callback.
	 * Additionally, the $function argument can be a string or array containing the class and method name.
	 *
	 * @param array $set
	 * @param string|\Closure $function
	 * @param array $args
	 * @return array
	 */
	public static function map($set, $function, $args = array()) {
		foreach ((array) $set as $key => $value) {
			if (is_array($value)) {
				$set[$key] = self::map($value, $function, $args);

			} else {
				$temp = $args;
				array_unshift($temp, $value);

				$set[$key] = call_user_func_array($function, $temp);
			}
		}

		return $set;
	}

	/**
	 * Compares to see if the first array set matches the second set exactly.
	 *
	 * @param array $set1
	 * @param array $set2
	 * @return bool
	 */
	public static function matches($set1, $set2) {
		return ((array) $set1 === (array) $set2);
	}

	/**
	 * Merge is a combination of array_merge() and array_merge_recursive(). However, when merging two keys with the same key,
	 * the previous value will be overwritten instead of being added into an array. The later array takes precedence when merging.
	 *
	 * @param array $array,...
	 * @return array
	 */
	public static function merge() {
		$sets = func_get_args();
		$data = array();

		if ($sets) {
			foreach ($sets as $set) {
				foreach ((array) $set as $key => $value) {
					if (isset($data[$key])) {
						if (is_array($value) && is_array($data[$key])) {
							$data[$key] = self::merge($data[$key], $value);

						} else {
							$data[$key] = $value;
						}
					} else {
						$data[$key] = $value;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Works similar to merge(), except that it will only overwrite/merge values if the keys exist in the previous array.
	 *
	 * @param array $set1 - The base array
	 * @param array $set2 - The array to overwrite the base array
	 * @return array
	 */
	public static function overwrite($set1, $set2) {
		if (!is_array($set1) || !is_array($set2)) {
			return null;
		}

		$overwrite = array_intersect_key($set2, $set1);

		if ($overwrite) {
			foreach ($overwrite as $key => $value) {
				if (is_array($value)) {
					$set1[$key] = self::overwrite($set1[$key], $value);
				} else {
					$set1[$key] = $value;
				}
			}
		}

		return $set1;
	}

	/**
	 * Pluck a value out of each child-array and return an array of the plucked values.
	 *
	 * @param array $set
	 * @param string $path
	 * @return array
	 */
	public static function pluck($set, $path) {
		$data = array();

		foreach ((array) $set as $array) {
			if ($value = self::extract($array, $path)) {
				$data[] = $value;
			}
		}

		return $data;
	}

	/**
	 * Generate an array with a range of numbers. Can apply a step interval to increase/decrease with larger increments.
	 *
	 * @param int $start
	 * @param int $stop
	 * @param int $step
	 * @param bool $index
	 * @return array
	 */
	public static function range($start, $stop, $step = 1, $index = true) {
		$array = array();

		if ($stop > $start) {
			for ($i = $start; $i <= $stop; $i += $step) {
				if ($index) {
					$array[$i] = $i;
				} else {
					$array[] = $i;
				}
			}

		} else if ($stop < $start) {
			for ($i = $start; $i >= $stop; $i -= $step) {
				if ($index) {
					$array[$i] = $i;
				} else {
					$array[] = $i;
				}
			}
		}

		return $array;
	}

	/**
	 * Remove an index from the array, determined by the given path.
	 *
	 * @param array $set
	 * @param string $path
	 * @return array
	 */
	public static function remove($set, $path) {
		if (!is_array($set) || !$path) {
			return $set;
		}

		$search =& $set;
		$paths = explode('.', (string) $path);
		$total = count($paths);

		while ($total > 0) {
			$key = $paths[0];

			// Within the last path
			if ($total === 1) {
				unset($search[$key]);
				return $set;

			// Break out of non-existent paths early
			} else if (!array_key_exists($key, $search) || !is_array($search[$key])) {
				return $set;
			}

			$search =& $search[$key];
			array_shift($paths);
			$total--;
		}

		unset($search);

		return $set;
	}

	/**
	 * Set a value into the result set. If the paths is an array, loop over each one and insert the value.
	 *
	 * @param array $set
	 * @param array|string $path
	 * @param mixed $value
	 * @return array
	 */
	public static function set($set, $path, $value = null) {
		if (is_array($path)) {
			foreach ($path as $key => $value) {
				$set = self::insert($set, $key, $value);
			}
		} else {
			$set = self::insert($set, $path, $value);
		}

		return $set;
	}

	/**
	 * Returns true if at least one element in the array satisfies the provided testing function.
	 *
	 * @param array $set
	 * @param \Closure $callback
	 * @return bool
	 */
	public static function some($set, Closure $callback) {
		$pass = false;

		if ($set) {
			foreach ((array) $set as $value) {
				if ($callback($value, $value)) {
					$pass = true;
					break;
				}
			}
		}

		return $pass;
	}

}
