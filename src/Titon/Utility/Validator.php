<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Utility\Exception;

/**
 * The Validator allows for quick validation against a defined set of rules and fields.
 */
class Validator {

	/**
	 * Data to validate against.
	 *
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Errors gathered during validation.
	 *
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * Mapping of fields and titles.
	 *
	 * @var array
	 */
	protected $_fields = [];

	/**
	 * Mapping of fields and validation rules.
	 *
	 * @var array
	 */
	protected $_rules = [];

	/**
	 * Store the data to validate.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = []) {
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
	public function addField($field, $title, array $rules = []) {
		$this->_fields[$field] = $title;

		/**
		 * rule => message
		 * rule => [message, opt, ...]
		 */
		if ($rules) {
			foreach ($rules as $rule => $params) {
				$message = null;
				$options = [];

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
	 * @throws \Titon\Utility\Exception
	 */
	public function addRule($field, $rule, $message, $options = []) {
		if (empty($this->_fields[$field])) {
			throw new Exception(sprintf('Field %s does not exist', $field));
		}

		$this->_rules[$field][$rule] = [
			'message' => $message,
			'options' => (array) $options
		];

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
	 * @return boolean
	 * @throws \Titon\Utility\Exception
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
					throw new Exception(sprintf('Validation rule %s does not exist', $rule));
				}

				if (!call_user_func_array([$class, $rule], $options)) {
					$this->addError($field, $params['message']);
					break;
				}
			}
		}

		return (count($this->_errors) === 0);
	}

}