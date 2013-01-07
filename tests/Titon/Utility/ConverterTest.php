<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Utility\Converter;

/**
 * Test class for Titon\Utility\Converter.
 */
class ConverterTest extends \PHPUnit_Framework_TestCase {

	public $array;
	public $object;
	public $json;
	public $serialized;
	public $xml;
	public $barbarian;

	/**
	 * Setup resources.
	 */
	protected function setUp() {
		$data = ['key' => 'value', 'number' => 1337, 'boolean' => true, 'float' => 1.50, 'array' => [1, 2, 3]];

		$this->array = $data;

		// Object
		$object = new \stdClass();
		$object->key = 'value';
		$object->number = 1337;
		$object->boolean = true;
		$object->float = 1.50;
		$subObject = new \stdClass();
		$subObject->{'0'} = 1;
		$subObject->{'1'} = 2;
		$subObject->{'2'} = 3;
		$object->array = $subObject;

		$this->object = $object;

		// Json
		$this->json = json_encode($data);

		// Serialized
		$this->serialized = serialize($data);

		// XML
		$xml  = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<root>';
		$xml .= '<key>value</key>';
		$xml .= '<number>1337</number>';
		$xml .= '<boolean>true</boolean>';
		$xml .= '<float>1.5</float>';
		$xml .= '<array>1</array><array>2</array><array>3</array>';
		$xml .= '</root>';

		$this->xml = $xml;
		$this->barbarian = file_get_contents(TEMP_DIR . '/barbarian.xml');
	}

	/**
	 * Test that is() returns a string of the type name.
	 */
	public function testIs() {
		$this->assertEquals('array', Converter::is($this->array));
		$this->assertEquals('object', Converter::is($this->object));
		$this->assertEquals('json', Converter::is($this->json));
		$this->assertEquals('serialized', Converter::is($this->serialized));
		$this->assertEquals('xml', Converter::is($this->xml));
	}

	/**
	 * Test that isArray() only returns true for arrays.
	 */
	public function testIsArray() {
		$this->assertTrue(Converter::isArray($this->array));
		$this->assertFalse(Converter::isArray($this->object));
		$this->assertFalse(Converter::isArray($this->json));
		$this->assertFalse(Converter::isArray($this->serialized));
		$this->assertFalse(Converter::isArray($this->xml));
	}

	/**
	 * Test that isObject() only returns true for objects.
	 */
	public function testIsObject() {
		$this->assertFalse(Converter::isObject($this->array));
		$this->assertTrue(Converter::isObject($this->object));
		$this->assertFalse(Converter::isObject($this->json));
		$this->assertFalse(Converter::isObject($this->serialized));
		$this->assertFalse(Converter::isObject($this->xml));
	}

	/**
	 * Test that isJson() only returns true for JSON strings.
	 */
	public function testIsJson() {
		$this->assertFalse((bool) Converter::isJson($this->array));
		$this->assertFalse((bool) Converter::isJson($this->object));
		$this->assertTrue((bool) Converter::isJson($this->json));
		$this->assertFalse((bool) Converter::isJson($this->serialized));
		$this->assertFalse((bool) Converter::isJson($this->xml));
	}

	/**
	 * Test that isSerialized() only returns true for serialized strings.
	 */
	public function testIsSerialized() {
		$this->assertFalse((bool) Converter::isSerialized($this->array));
		$this->assertFalse((bool) Converter::isSerialized($this->object));
		$this->assertFalse((bool) Converter::isSerialized($this->json));
		$this->assertTrue((bool) Converter::isSerialized($this->serialized));
		$this->assertFalse((bool) Converter::isSerialized($this->xml));
	}

	/**
	 * Test that isXml() only returns true for XML strings.
	 */
	public function testIsXml() {
		$this->assertFalse((bool) Converter::isXml($this->array));
		$this->assertFalse((bool) Converter::isXml($this->object));
		$this->assertFalse((bool) Converter::isXml($this->json));
		$this->assertFalse((bool) Converter::isXml($this->serialized));
		$this->assertTrue((bool) Converter::isXml($this->xml));
	}

	/**
	 * Test that toArray() converts any resource type to an array.
	 */
	public function testToArray() {
		$this->assertEquals($this->array, Converter::toArray($this->array));
		$this->assertEquals($this->array, Converter::toArray($this->object));
		$this->assertEquals($this->array, Converter::toArray($this->json));
		$this->assertEquals($this->array, Converter::toArray($this->serialized));
		$this->assertEquals($this->array, Converter::toArray($this->xml));
	}

	/**
	 * Test that toObject() converts any resource type to an object.
	 */
	public function testToObject() {
		$this->assertEquals($this->object, Converter::toObject($this->array));
		$this->assertEquals($this->object, Converter::toObject($this->object));
		$this->assertEquals($this->object, Converter::toObject($this->json));
		$this->assertEquals($this->object, Converter::toObject($this->serialized));
		$this->assertEquals($this->object, Converter::toObject($this->xml));
	}

	/**
	 * Test that toJson() converts any resource type to a JSON string.
	 */
	public function testToJson() {
		$this->assertEquals($this->json, Converter::toJson($this->array));
		$this->assertEquals($this->json, Converter::toJson($this->object));
		$this->assertEquals($this->json, Converter::toJson($this->json));
		$this->assertEquals($this->json, Converter::toJson($this->serialized));
		$this->assertEquals($this->json, Converter::toJson($this->xml));
	}

	/**
	 * Test that toSerialize() converts any resource type to a serialized string.
	 */
	public function testToSerialize() {
		$this->assertEquals($this->serialized, Converter::toSerialize($this->array));
		$this->assertEquals($this->serialized, Converter::toSerialize($this->object));
		$this->assertEquals($this->serialized, Converter::toSerialize($this->json));
		$this->assertEquals($this->serialized, Converter::toSerialize($this->serialized));
		$this->assertEquals($this->serialized, Converter::toSerialize($this->xml));
	}

	/**
	 * Test that toXml() converts any resource type to an XML string.
	 */
	public function testToXml() {
		$this->assertEquals($this->xml, Converter::toXml($this->array));
		$this->assertEquals($this->xml, Converter::toXml($this->object));
		$this->assertEquals($this->xml, Converter::toXml($this->json));
		$this->assertEquals($this->xml, Converter::toXml($this->serialized));
		$this->assertEquals($this->xml, Converter::toXml($this->xml));
	}

	/**
	 * Test that buildArray() and buildObject() convert all nested tiers.
	 */
	public function testBuildArrayObject() {
		$array = ['one' => 1];
		$object = new \stdClass();
		$object->one = 1;

		$this->assertEquals($array, Converter::toArray($object));
		$this->assertEquals($object, Converter::toObject($array));

		$array['one'] = ['two' => 2];
		$level = new \stdClass();
		$level->two = 2;
		$object->one = $level;

		$this->assertEquals($array, Converter::toArray($object));
		$this->assertEquals($object, Converter::toObject($array));

		$array['one']['two'] = ['three' => 3];
		$level = new \stdClass();
		$level->three = 3;
		$object->one->two = $level;

		$this->assertEquals($array, Converter::toArray($object));
		$this->assertEquals($object, Converter::toObject($array));
	}

	/**
	 * Test that xmlToArray(XML_NONE) returns the XML without attributes.
	 */
	public function testXmlToArrayNone() {
		$expected = [
			'name' => 'Barbarian',
			'life' => 50,
			'mana' => 100,
			'stamina' => 15,
			'vitality' => 20,
			'dexterity' => '',
			'agility' => '',
			'armors' => [
				'armor' => ['Helmet', 'Shoulder Plates', 'Breast Plate', 'Greaves', 'Gloves', 'Shield']
			],
			'weapons' => [
				'sword' => ['Broadsword', 'Longsword'],
				'axe' => ['Heavy Axe', 'Double-edged Axe'],
				'polearm' => 'Polearm',
				'mace' => 'Mace'
			],
			'items' => [
				'potions' => [
					'potion' => ['Health Potion', 'Mana Potion']
				],
				'keys' => [
					'chestKey' => 'Chest Key',
					'bossKey' => 'Boss Key'
				],
				'food' => ['Fruit', 'Bread', 'Vegetables'],
				'scrap' => 'Scrap'
			]
		];

		$this->assertEquals($expected, Converter::xmlToArray($this->barbarian, Converter::XML_NONE));
	}

	/**
	 * Test that xmlToArray(XML_MERGE) returns the XML without attributes.
	 */
	public function testXmlToArrayMerge() {
		$expected = [
			'name' => 'Barbarian',
			'life' => ['value' => 50, 'max' => 150],
			'mana' => ['value' => 100, 'max' => 250],
			'stamina' => 15,
			'vitality' => 20,
			'dexterity' => ['value' => '', 'evade' => '5%', 'block' => '10%'],
			'agility' => ['value' => '', 'turnRate' => '1.25', 'acceleration' => 5],
			'armors' => [
				'armor' => [
					['value' => 'Helmet', 'defense' => 15],
					['value' => 'Shoulder Plates', 'defense' => 25],
					['value' => 'Breast Plate', 'defense' => 50],
					['value' => 'Greaves', 'defense' => 10],
					['value' => 'Gloves', 'defense' => 10],
					['value' => 'Shield', 'defense' => 25],
				],
				'items' => 6
			],
			'weapons' => [
				'sword' => [
					['value' => 'Broadsword', 'damage' => 25],
					['value' => 'Longsword', 'damage' => 30]
				],
				'axe' => [
					['value' => 'Heavy Axe', 'damage' => 20],
					['value' => 'Double-edged Axe', 'damage' => 25],
				],
				'polearm' => ['value' => 'Polearm', 'damage' => 50, 'range' => 3, 'speed' => 'slow'],
				'mace' => ['value' => 'Mace', 'damage' => 15, 'speed' => 'fast'],
				'items' => 6
			],
			'items' => [
				'potions' => [
					'potion' => ['Health Potion', 'Mana Potion']
				],
				'keys' => [
					'chestKey' => 'Chest Key',
					'bossKey' => 'Boss Key'
				],
				'food' => ['Fruit', 'Bread', 'Vegetables'],
				'scrap' => ['value' => 'Scrap', 'count' => 25]
			]
		];

		$this->assertEquals($expected, Converter::xmlToArray($this->barbarian, Converter::XML_MERGE));
	}

	/**
	 * Test that xmlToArray(XML_GROUP) returns the XML with attributes and value grouped separately.
	 */
	public function testXmlToArrayGroup() {
		$expected = [
			'name' => 'Barbarian',
			'life' => [
				'value' => 50,
				'attributes' => ['max' => 150]
			],
			'mana' => [
				'value' => 100,
				'attributes' => ['max' => 250]
			],
			'stamina' => 15,
			'vitality' => 20,
			'dexterity' => [
				'value' => '',
				'attributes' => ['evade' => '5%', 'block' => '10%']
			],
			'agility' => [
				'value' => '',
				'attributes' => ['turnRate' => '1.25', 'acceleration' => 5]
			],
			'armors' => [
				'value' => [
					'armor' => [
						['value' => 'Helmet', 'attributes' => ['defense' => 15]],
						['value' => 'Shoulder Plates', 'attributes' => ['defense' => 25]],
						['value' => 'Breast Plate', 'attributes' => ['defense' => 50]],
						['value' => 'Greaves', 'attributes' => ['defense' => 10]],
						['value' => 'Gloves', 'attributes' => ['defense' => 10]],
						['value' => 'Shield', 'attributes' => ['defense' => 25]],
					],
				],
				'attributes' => ['items' => 6]
			],
			'weapons' => [
				'value' => [
					'sword' => [
						['value' => 'Broadsword', 'attributes' => ['damage' => 25]],
						['value' => 'Longsword', 'attributes' => ['damage' => 30]]
					],
					'axe' => [
						['value' => 'Heavy Axe', 'attributes' => ['damage' => 20]],
						['value' => 'Double-edged Axe', 'attributes' => ['damage' => 25]],
					],
					'polearm' => [
						'value' => 'Polearm',
						'attributes' => ['damage' => 50, 'range' => 3, 'speed' => 'slow']
					],
					'mace' => [
						'value' => 'Mace',
						'attributes' => ['damage' => 15, 'speed' => 'fast']
					],
				],
				'attributes' => ['items' => 6]
			],
			'items' => [
				'potions' => [
					'potion' => ['Health Potion', 'Mana Potion']
				],
				'keys' => [
					'chestKey' => 'Chest Key',
					'bossKey' => 'Boss Key'
				],
				'food' => ['Fruit', 'Bread', 'Vegetables'],
				'scrap' => [
					'value' => 'Scrap',
					'attributes' => ['count' => 25]
				]
			]
		];

		$this->assertEquals($expected, Converter::xmlToArray($this->barbarian, Converter::XML_GROUP));
	}

	/**
	 * Test that xmlToArray(XML_ATTRIBS) returns the XML with only attributes.
	 */
	public function testXmlToArrayAttribs() {
		$expected = [
			'name' => 'Barbarian',
			'life' => ['max' => 150],
			'mana' => ['max' => 250],
			'stamina' => 15,
			'vitality' => 20,
			'dexterity' => ['evade' => '5%', 'block' => '10%'],
			'agility' => ['turnRate' => '1.25', 'acceleration' => 5],
			'armors' => [
				'items' => 6
			],
			'weapons' => [
				'items' => 6
			],
			'items' => [
				'potions' => [
					'potion' => ['Health Potion', 'Mana Potion']
				],
				'keys' => [
					'chestKey' => 'Chest Key',
					'bossKey' => 'Boss Key'
				],
				'food' => ['Fruit', 'Bread', 'Vegetables'],
				'scrap' => ['count' => 25]
			]
		];

		$this->assertEquals($expected, Converter::xmlToArray($this->barbarian, Converter::XML_ATTRIBS));
	}

}