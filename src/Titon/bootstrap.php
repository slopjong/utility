<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon;

use Titon\Utility\Sanitize;

/**
 * @see Titon\Utility\Sanitize::escape()
 */
function esc($value, array $options = array()) {
	return Sanitize::escape($value, $options);
}

/**
 * @see Titon\Utility\Sanitize::html()
 */
function html($value, array $options = array()) {
	return Sanitize::html($value, $options);
}

/**
 * @see Titon\Utility\Sanitize::newlines()
 */
function nl($value, array $options = array()) {
	return Sanitize::newlines($value, $options);
}

/**
 * @see Titon\Utility\Sanitize::whitespace()
 */
function ws($value, array $options = array()) {
	return Sanitize::whitespace($value, $options);
}

/**
 * @see Titon\Utility\Sanitize::xss()
 */
function xss($value, array $options = array()) {
	return Sanitize::xss($value, $options);
}