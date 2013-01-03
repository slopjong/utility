<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use \Closure;

/**
 * String and grammar inflection. Converts strings to a certain format. Camel cased, singular, plural etc.
 */
class Inflector {

	/**
	 * Cached inflections for all methods.
	 *
	 * @access protected
	 * @var array
	 * @static
	 */
	protected static $_cache = [];

	/**
	 * Inflect a word to a camel case form with the first letter being capitalized.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function camelCase($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			return str_replace(' ', '', mb_convert_case(str_replace(['_', '-'], ' ', preg_replace('/[^-_a-z0-9\s]+/i', '', $string)), MB_CASE_TITLE));
		});
	}

	/**
	 * Inflect a word to a class name. Singular camel cased form.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function className($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			if (method_exists(__CLASS__, 'singularize')) {
				$string = self::singularize($string);
			}

			return self::camelCase($string);
		});
	}

	/**
	 * Inflect a word for a filename. Studly cased and capitalized.
	 *
	 * @access public
	 * @param string $string
	 * @param string $ext
	 * @param boolean $capitalize
	 * @return string
	 * @static
	 */
	public static function fileName($string, $ext = 'php', $capitalize = true) {
		if (mb_strpos($string, '.') !== false) {
			$string = mb_substr($string, 0, mb_strrpos($string, '.'));
		}

		$path = self::camelCase($string);

		if (!$capitalize) {
			$path = lcfirst($path);
		}

		if (mb_substr($path, -(mb_strlen($ext) + 1)) !== '.' . $ext) {
			$path .= '.' . $ext;
		}

		return $path;
	}

	/**
	 * Inflect a word to a human readable string with only the first word capitalized and the rest lowercased.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function normalCase($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			return ucfirst(mb_strtolower(str_replace('_', ' ', $string)));
		});
	}

	/**
	 * Inflect a word to a routeable format. All non-alphanumeric characters will be removed, and any spaces or underscores will be changed to dashes.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function route($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			return str_replace([' ', '_'], '-', preg_replace('/[^-_a-z0-9\s]+/i', '', preg_replace('/\s{2,}+/', ' ', $string)));
		});
	}

	/**
	 * Inflect a word to a URL friendly slug. Removes all punctuation, replaces dashes with underscores and spaces with dashes.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function slug($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			// Revert entities
			$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

			// Remove non-ascii characters
			if (method_exists(__CLASS__, 'transliterate')) {
				$string = self::transliterate($string);
			}

			$string = preg_replace('/[^-a-z0-9\s]+/i', '', $string);

			// Replace dashes and underscores
			$string = str_replace(' ', '-', str_replace('-', '_', $string));

			return mb_strtolower($string);
		});
	}

	/**
	 * Inflect a word for a database table name. Formatted as plural and camel case with the first letter lowercase.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function tableName($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			if (method_exists(__CLASS__, 'pluralize')) {
				$string = self::pluralize($string);
			}

			return lcfirst(self::camelCase($string));
		});
	}

	/**
	 * Inflect a word to a human readable string with all words capitalized.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function titleCase($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			return mb_convert_case(str_replace('_', ' ', $string), MB_CASE_TITLE);
		});
	}

	/**
	 * Inflect a word to an underscore form that strips all punctuation and special characters and converts spaces to underscores.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function underscore($string) {
		return self::_cache([__METHOD__, $string], function() use ($string) {
			return trim(mb_strtolower(str_replace('__', '_', preg_replace('/([A-Z]{1})/', '_$1', preg_replace('/[^_a-z0-9]+/i', '', preg_replace('/[\s]+/', '_', $string))))), '_');
		});
	}

	/**
	 * Inflect a word to be used as a PHP variable. Strip all but letters, numbers and underscores. Add an underscore if the first letter is numeric.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function variable($string) {
		$string = preg_replace('/[^_a-z0-9]+/i', '', $string);

		if (is_numeric(mb_substr($string, 0, 1))) {
			$string = '_' . $string;
		}

		return $string;
	}

	/**
	 * Cache the result of an inflection by using a Closure.
	 *
	 * @access protected
	 * @param string|array $key
	 * @param Closure $callback
	 * @return mixed
	 * @static
	 */
	protected static function _cache($key, Closure $callback) {
		if (is_array($key)) {
			$key = implode('-', $key);
		}

		if (isset(self::$_cache[$key])) {
			return self::$_cache[$key];
		}

		$callback = Closure::bind($callback, null, __CLASS__);

		self::$_cache[$key] = $callback();

		return self::$_cache[$key];
	}

}