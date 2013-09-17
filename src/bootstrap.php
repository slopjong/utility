<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

use Titon\Utility\Sanitize;

/**
 * @see Titon\Utility\Sanitize::escape()
 */
if (!function_exists('esc')) {
    function esc($value, array $options = array()) {
        return Sanitize::escape($value, $options);
    }
}

/**
 * @see Titon\Utility\Sanitize::html()
 */
if (!function_exists('html')) {
    function html($value, array $options = array()) {
        return Sanitize::html($value, $options);
    }
}

/**
 * @see Titon\Utility\Sanitize::newlines()
 */
if (!function_exists('nl')) {
    function nl($value, array $options = array()) {
        return Sanitize::newlines($value, $options);
    }
}

/**
 * @see Titon\Utility\Sanitize::whitespace()
 */
if (!function_exists('ws')) {
    function ws($value, array $options = array()) {
        return Sanitize::whitespace($value, $options);
    }
}

/**
 * @see Titon\Utility\Sanitize::xss()
 */
if (!function_exists('xss')) {
    function xss($value, array $options = array()) {
        return Sanitize::xss($value, $options);
    }
}