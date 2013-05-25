<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

/**
 * Provides convenience functions for inflecting notation paths, namespace paths and file system paths.
 *
 * @package Titon\Utility
 */
class Loader {

	/**
	 * Strips the namespace to return the base class name.
	 *
	 * @param string $class
	 * @param string $separator
	 * @return string
	 */
	public static function baseClass($class, $separator = '\\') {
		return self::stripExt(trim(mb_strrchr($class, $separator), $separator));
	}

	/**
	 * Returns a namespace with only the base package, and not the class name.
	 *
	 * @param string $class
	 * @param string $separator
	 * @return string
	 */
	public static function baseNamespace($class, $separator = '\\') {
		$class = self::toNamespace($class);

		return mb_substr($class, 0, mb_strrpos($class, $separator));
	}

	/**
	 * Converts OS directory separators to the standard forward slash.
	 *
	 * @param string $path
	 * @param bool $endSlash
	 * @return string
	 */
	public static function ds($path, $endSlash = false) {
		$path = str_replace('\\', '/', $path);

		if ($endSlash && mb_substr($path, -1) !== '/') {
			$path .= '/';
		}

		return $path;
	}

	/**
	 * Return the extension from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function ext($path) {
		return mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
	}

	/**
	 * Define additional include paths for PHP to detect within.
	 *
	 * @param string|array $paths
	 */
	public static function includePath($paths) {
		$current = array(get_include_path());

		if (is_array($paths)) {
			foreach ($paths as $path) {
				$current[] = $path;
			}
		} else {
			$current[] = $paths;
		}

		set_include_path(implode(PATH_SEPARATOR, $current));
	}

	/**
	 * Strip off the extension if it exists.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function stripExt($path) {
		if (mb_strpos($path, '.') !== false) {
			$path = mb_substr($path, 0, mb_strrpos($path, '.'));
		}

		return $path;
	}

	/**
	 * Converts a path to a namespace package.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function toNamespace($path) {
		$path = self::ds(self::stripExt($path));

		// Attempt to split path at src folder
		if (mb_strpos($path, 'src/') !== false) {
			$paths = explode('src/', $path);
			$path = $paths[1];
		}

		return trim(str_replace('/', '\\', $path), '\\');
	}

	/**
	 * Converts a namespace to a relative or absolute file system path.
	 *
	 * @param string $path
	 * @param string $ext
	 * @param string $root
	 * @return string
	 */
	public static function toPath($path, $ext = 'php', $root = '') {
		$path = self::ds($path);
		$dirs = explode('/', $path);
		$file = array_pop($dirs);
		$path = implode('/', $dirs) . '/' . str_replace('_', '/', $file);

		if ($ext && mb_substr($path, -mb_strlen($ext)) !== $ext) {
			$path .= '.' . $ext;
		}

		if ($root) {
			$path = $root . $path;
		}

		return $path;
	}

}