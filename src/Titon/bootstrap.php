<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon;

use Titon\Utility\Sanitize;

/**
 * Global function for Titon\Utility\Sanitize::escape().
 */
function esc($value, array $options = array()) {
	return Sanitize::escape($value, $options);
}

/**
 * Global function for Titon\Utility\Sanitize::html().
 */
function html($value, array $options = array()) {
	return Sanitize::html($value, $options);
}

/**
 * Global function for Titon\Utility\Sanitize::newlines().
 */
function nl($value, array $options = array()) {
	return Sanitize::newlines($value, $options);
}

/**
 * Global function for Titon\Utility\Sanitize::whitespace().
 */
function ws($value, array $options = array()) {
	return Sanitize::whitespace($value, $options);
}

/**
 * Global function for Titon\Utility\Sanitize::xss().
 */
function xss($value, array $options = array()) {
	return Sanitize::xss($value, $options);
}