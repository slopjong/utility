<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Utility\Exception\InvalidArgumentException;
use Titon\Utility\Exception\InvalidValidationRuleException;

/**
 * The Validator allows for quick validation against a defined set of rules and fields.
 *
 * @package Titon\Utility
 */
class Validator {

	/**
	 * Data to validate against.
	 *
	 * @type array
	 */
	protected $_data = array();

	/**
	 * Errors gathered during validation.
	 *
	 * @type array
	 */
	protected $_errors = array();

	/**
	 * Mapping of fields and titles.
	 *
	 * @type array
	 */
	protected $_fields = array();

	/**
	 * Mapping of fields and validation rules.
	 *
	 * @type array
	 */
	protected $_rules = array();

	/**
	 * Store the data to validate.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		$this->_data = $data;
	}

	/**
	 * Mark a field has an error.
	 *
	 * @param string $field
	 * @param string $message
	 * @return \Titon\Utility\Validator
	 */
	public function addError($field, $message) {
		$this->_errors[$field] = $message;

		return $this;
	}

	/**
	 * Add a field to be used in validation. Can optionally apply an array of validation rules.
	 *
	 * @param string $field
	 * @param string $title
	 * @param array $rules
	 * @return \Titon\Utility\Validator
	 */
	public function addField($field, $title, array $rules = array()) {
		$this->_fields[$field] = $title;

		/**
		 * rule => message
		 * rule => [message, opt, ...]
		 */
		if ($rules) {
			foreach ($rules as $rule => $params) {
				$message = null;
				$options = array();

				if (is_array($params)) {
					$message = array_shift($params);
					$options = $params;

				} else {
					$message = $params;
				}

				$this->addRule($field, $rule, $message, $options);
			}
		}

		return $this;
	}

	/**
	 * Add a validation rule to a field. Can supply an optional error message and options.
	 *
	 * @param string $field
	 * @param string $rule
	 * @param string $message
	 * @param array $options
	 * @return \Titon\Utility\Validator
	 * @throws \Titon\Utility\Exception\InvalidArgumentException
	 */
	public function addRule($field, $rule, $message, $options = array()) {
		if (empty($this->_fields[$field])) {
			throw new InvalidArgumentException(sprintf('Field %s does not exist', $field));
		}

		$this->_rules[$field][$rule] = array(
			'message' => $message,
			'options' => (array) $options
		);

		return $this;
	}

	/**
	 * Return the errors.
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * Return the fields.
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->_fields;
	}

	/**
	 * Return the rules.
	 *
	 * @return array
	 */
	public function getRules() {
		return $this->_rules;
	}

	/**
	 * Validate the data against the rules schema. Return true if all fields passed validation.
	 *
	 * @return bool
	 * @throws \Titon\Utility\Exception\InvalidValidationRuleException
	 */
	public function validate() {
		if (!$this->_data) {
			return false;
		}

		foreach ($this->_data as $field => $value) {
			if (empty($this->_rules[$field])) {
				continue;
			}

			foreach ($this->_rules[$field] as $rule => $params) {
				$options = $params['options'];
				array_unshift($options, $value);

				// Use G11n if it is available
				if (class_exists('Titon\G11n\Utility\Validate')) {
					$class = 'Titon\G11n\Utility\Validate';
				} else {
					$class = 'Titon\Utility\Validate';
				}

				if (!method_exists($class, $rule)) {
					throw new InvalidValidationRuleException(sprintf('Validation rule %s does not exist', $rule));
				}

				if (!call_user_func_array(array($class, $rule), $options)) {
					$this->addError($field, $params['message']);
					break;
				}
			}
		}

		return (count($this->_errors) === 0);
	}

}