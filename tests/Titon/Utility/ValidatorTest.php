<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

/**
 * Test class for Titon\Utility\Validator.
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Validator instance.
	 *
	 * @var \Titon\Utility\Validator
	 */
	public $object;

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new Validator([
			'username' => 'miles',
			'email' => 'miles@titon' // invalid
		]);
	}

	/**
	 * Test that addError() will flag an field, and getErrors() will return all errors.
	 */
	public function testAddGetErrors() {
		$this->assertEquals([], $this->object->getErrors());

		$this->object->addError('username', 'Invalid username');
		$this->assertEquals(['username' => 'Invalid username'], $this->object->getErrors());
	}

	/**
	 * Test that addField() will add a field, and getFields() will return all fields.
	 */
	public function testAddGetFields() {
		$this->assertEquals([], $this->object->getFields());

		$this->object->addField('username', 'Username');
		$this->assertEquals(['username' => 'Username'], $this->object->getFields());
	}

	/**
	 * Test that addRule() will add a single rule, or addField() will add multiple rules, and getRules() will return all rules.
	 */
	public function testAddGetRules() {
		$this->assertEquals([], $this->object->getRules());

		// via addRule()
		$this->object
			->addField('basicRule', 'Basic Rule')
				->addRule('basicRule', 'alphaNumeric', 'Custom alpha-numeric message')
				->addRule('basicRule', 'between', 'May only be between 0 and 100 characters', [0, 100]); // use default message

		$this->assertEquals([
			'basicRule' => [
				'alphaNumeric' => [
					'message' => 'Custom alpha-numeric message',
					'options' => []
				],
				'between' => [
					'message' => 'May only be between 0 and 100 characters',
					'options' => [0, 100]
				]
			]
		], $this->object->getRules());

		// via third argument in addField()
		$this->object->addField('advRule', 'Advanced Rule', array(
			'phone' => 'Invalid phone number',
			'email' => 'Please provide an email',
			'ext' => ['Valid extensions are txt, pdf', ['txt', 'pdf']],
			'ip' => ['Please provide an IPv4', Validate::IPV4]
		));

		$this->assertEquals([
			'basicRule' => [
				'alphaNumeric' => [
					'message' => 'Custom alpha-numeric message',
					'options' => []
				],
				'between' => [
					'message' => 'May only be between 0 and 100 characters',
					'options' => [0, 100]
				]
			],
			'advRule' => [
				'phone' => [
					'message' => 'Invalid phone number',
					'options' => []
				],
				'email' => [
					'message' => 'Please provide an email',
					'options' => []
				],
				'ext' => [
					'message' => 'Valid extensions are txt, pdf',
					'options' => [['txt', 'pdf']]
				],
				'ip' => [
					'message' => 'Please provide an IPv4',
					'options' => [Validate::IPV4]
				]
			]
		], $this->object->getRules());
	}

	/**
	 * Test that validate() validates the data against the schema rules.
	 */
	public function testValidate() {
		$this->object->addField('username', 'Username')->addRule('username', 'alpha', 'Not alpha');

		$this->assertTrue($this->object->validate());
		$this->assertEquals([], $this->object->getErrors());

		// this will fail
		$this->object->addField('email', 'Email')->addRule('email', 'email', 'Invalid email');

		$this->assertFalse($this->object->validate());
		$this->assertEquals(['email' => 'Invalid email'], $this->object->getErrors());
	}

}