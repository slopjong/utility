<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Utility\String;
use Titon\Utility\Exception\UnsupportedMethodException;

/**
 * Uuid handles the creation of compatible UUID's (unique universal identifier) in all versions.
 *
 * @package Titon\Utility
 */
class Uuid {

	/**
	 * Creates UUID version 1.
	 *
	 * @return string
	 * @throws \Titon\Utility\Exception\UnsupportedMethodException
	 */
	public static function v1() {
		throw new UnsupportedMethodException('UUID version 1 has not been implemented yet');
	}

	/**
	 * Creates UUID version 2.
	 *
	 * @return string
	 * @throws \Titon\Utility\Exception\UnsupportedMethodException
	 */
	public static function v2() {
		throw new UnsupportedMethodException('UUID version 2 has not been implemented yet');
	}

	/**
	 * Creates UUID version 3: md5 based.
	 *
	 * @return string
	 * @throws \Titon\Utility\Exception\UnsupportedMethodException
	 */
	public static function v3() {
		throw new UnsupportedMethodException('UUID version 3 has not been implemented yet');
	}

	/**
	 * Creates UUID version 4: random number generation based.
	 *
	 * @uses Titon\Utility\String
	 *
	 * @return string
	 * @throws \Titon\Utility\Exception\UnsupportedMethodException
	 */
	public static function v4() {
		return sprintf('%s-%s-%s%s-%s%s-%s',
			String::generate(8, String::HEX), // 1
			String::generate(4, String::HEX), // 2
			4, // 3
			String::generate(3, String::HEX), // 3
			String::generate(1, '89AB'), // 4
			String::generate(3, String::HEX), // 4
			String::generate(12, String::HEX)); // 5
	}

	/**
	 * Creates UUID version 5: sha1 based.
	 *
	 * @return string
	 * @throws \Titon\Utility\Exception\UnsupportedMethodException
	 */
	public static function v5() {
		throw new UnsupportedMethodException('UUID version 5 has not been implemented yet');
	}

}