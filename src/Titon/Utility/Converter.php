<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use \SimpleXmlElement;

/**
 * A class that handles the detection and conversion of certain resource formats / content types into other formats.
 * The current formats are supported: XML, JSON, Array, Object, Serialized
 *
 * @package Titon\Utility
 */
class Converter {

	/**
	 * Disregard XML attributes and only return the value.
	 */
	const XML_NONE = 0;

	/**
	 * Merge attributes and the value into a single dimension; the values key will be "value".
	 */
	const XML_MERGE = 1;

	/**
	 * Group the attributes into a key "attributes" and the value into a key of "value".
	 */
	const XML_GROUP = 2;

	/**
	 * Attributes will only be returned.
	 */
	const XML_ATTRIBS = 3;

	/**
	 * Autobox a value by type casting it.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function autobox($value) {
		if (is_numeric($value)) {
			if (strpos($value, '.') !== false) {
				return (float) $value;

			} else {
				return (int) $value;
			}
		} else if (is_bool($value)) {
			return (bool) $value;

		} else if ($value === 'true' || $value === 'false') {
			return ($value === 'true');
		}

		return (string) $value;
	}

	/**
	 * Unbox values by type casting to a string equivalent.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function unbox($value) {
		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		return (string) $value;
	}

	/**
	 * Returns a string for the detected type.
	 *
	 * @param mixed $data
	 * @return string
	 */
	public static function is($data) {
		if (self::isArray($data)) {
			return 'array';

		} else if (self::isObject($data)) {
			return 'object';

		} else if (self::isJson($data)) {
			return 'json';

		} else if (self::isSerialized($data)) {
			return 'serialized';

		} else if (self::isXml($data)) {
			return 'xml';
		}

		// Attempt other types
		return strtolower(gettype($data));
	}

	/**
	 * Check to see if data passed is an array.
	 *
	 * @param mixed $data
	 * @return bool
	 */
	public static function isArray($data) {
		return is_array($data);
	}

	/**
	 * Check to see if data passed is a JSON object.
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function isJson($data) {
		$json = @json_decode($data, true);

		return ($json !== null) ? $json : false;
	}

	/**
	 * Check to see if data passed is an object.
	 *
	 * @param mixed $data
	 * @return bool
	 */
	public static function isObject($data) {
		return is_object($data);
	}

	/**
	 * Check to see if data passed has been serialized.
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function isSerialized($data) {
		$ser = @unserialize($data);

		return ($ser !== false) ? $ser : false;
	}

	/**
	 * Check to see if data passed is an XML document.
	 *
	 * @param mixed $data
	 * @return \SimpleXmlElement
	 */
	public static function isXml($data) {
		$xml = @simplexml_load_string($data);

		return ($xml instanceof SimpleXmlElement) ? $xml : false;
	}

	/**
	 * Transforms a resource into an array.
	 *
	 * @param mixed $resource
	 * @param bool $recursive
	 * @return array
	 */
	public static function toArray($resource, $recursive = false) {
		if (self::isArray($resource)) {
			return $recursive ? self::buildArray($resource) : $resource;

		} else if (self::isObject($resource)) {
			return self::buildArray($resource);

		} else if ($json = self::isJson($resource)) {
			$resource = $json;

		} else if ($ser = self::isSerialized($resource)) {
			$resource = $ser;

		} else if ($xml = self::isXml($resource)) {
			$resource = self::xmlToArray($xml);
		}

		return (array) $resource;
	}

	/**
	 * Transforms a resource into a JSON object.
	 *
	 * @param mixed $resource
	 * @return string
	 */
	public static function toJson($resource) {
		if (self::isJson($resource)) {
			return $resource;
		}

		if (self::isObject($resource)) {
			$resource = self::buildArray($resource);

		} else if ($xml = self::isXml($resource)) {
			$resource = self::xmlToArray($xml);

		} else if ($ser = self::isSerialized($resource)) {
			$resource = $ser;
		}

		return json_encode($resource);
	}

	/**
	 * Transforms a resource into an object.
	 *
	 * @param mixed $resource
	 * @param bool $recursive
	 * @return object
	 */
	public static function toObject($resource, $recursive = false) {
		if (self::isObject($resource)) {
			if (!$recursive) {
				return $resource;
			}

		} else if (self::isArray($resource)) {
			// Continue

		} else if ($json = self::isJson($resource)) {
			$resource = $json;

		} else if ($ser = self::isSerialized($resource)) {
			$resource = $ser;

		} else if ($xml = self::isXml($resource)) {
			$resource = self::xmlToArray($xml);
		}

		return self::buildObject($resource);
	}

	/**
	 * Transforms a resource into a serialized form.
	 *
	 * @param mixed $resource
	 * @return string
	 */
	public static function toSerialize($resource) {
		return serialize(self::toArray($resource));
	}

	/**
	 * Transforms a resource into an XML document.
	 *
	 * @param mixed $resource
	 * @param string $root
	 * @return string
	 */
	public static function toXml($resource, $root = 'root') {
		if (self::isXml($resource)) {
			return $resource;
		}

		if ($array = self::toArray($resource, true)) {
			$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><' . $root . '></' . $root . '>');
			$response = self::buildXml($xml, $array);

			return trim($response->asXML());
		}

		return $resource;
	}

	/**
	 * Turn an object into an array. Alternative to array_map magic.
	 *
	 * @param object|array $object
	 * @return array
	 */
	public static function buildArray($object) {
		$array = array();

		foreach ($object as $key => $value) {
			if (is_object($value) || is_array($value)) {
				$array[$key] = self::buildArray($value);
			} else {
				$array[$key] = self::autobox($value);
			}
		}

		return $array;
	}

	/**
	 * Turn an array into an object. Alternative to array_map magic.
	 *
	 * @param array|object $array
	 * @return object
	 */
	public static function buildObject($array) {
		$obj = new \stdClass();

		foreach ($array as $key => $value) {
			if (is_array($value) || is_object($value)) {
				$obj->{$key} = self::buildObject($value);
			} else {
				$obj->{$key} = self::autobox($value);
			}
		}

		return $obj;
	}

	/**
	 * Turn an array into an XML document. Alternative to array_map magic.
	 *
	 * @param \SimpleXMLElement $xml
	 * @param array $array
	 * @return \SimpleXMLElement
	 */
	public static function buildXml(SimpleXMLElement &$xml, $array) {
		if (is_array($array)) {
			foreach ($array as $key => $value) {

				// XML_NONE
				if (!is_array($value)) {
					$xml->addChild($key, self::unbox($value));
					continue;
				}

				// Multiple nodes of the same name
				if (isset($value[0])) {
					foreach ($value as $kValue) {
						if (is_array($kValue)) {
							self::buildXml($xml, array($key => $kValue));
						} else {
							$xml->addChild($key, self::unbox($kValue));
						}
					}

				// XML_GROUP
				} else if (isset($value['attributes'])) {
					if (is_array($value['value'])) {
						$node = $xml->addChild($key);
						self::buildXml($node, $value['value']);
					} else {
						$node = $xml->addChild($key, self::unbox($value['value']));
					}

					if (!empty($value['attributes'])) {
						foreach ($value['attributes'] as $aKey => $aValue) {
							$node->addAttribute($aKey, self::unbox($aValue));
						}
					}

				// XML_MERGE
				} else if (isset($value['value'])) {
					$node = $xml->addChild($key, $value['value']);
					unset($value['value']);

					if (!empty($value)) {
						foreach ($value as $aKey => $aValue) {
							if (is_array($aValue)) {
								self::buildXml($node, array($aKey => $aValue));
							} else {
								$node->addAttribute($aKey, self::unbox($aValue));
							}
						}
					}

				// XML_ATTRIBS
				} else {
					$node = $xml->addChild($key);

					if (!empty($value)) {
						foreach ($value as $aKey => $aValue) {
							if (is_array($aValue)) {
								self::buildXml($node, array($aKey => $aValue));
							} else {
								$node->addChild($aKey, self::unbox($aValue));
							}
						}
					}
				}
			}
		}

		return $xml;
	}

	/**
	 * Convert a SimpleXML object into an array.
	 *
	 * @param mixed $xml
	 * @param int $format
	 * @return array
	 */
	public static function xmlToArray($xml, $format = self::XML_GROUP) {
		if (is_string($xml)) {
			$xml = @simplexml_load_string($xml);
		}

		if (count($xml->children()) <= 0) {
			return self::autobox((string) $xml);
		}

		$array = array();

		/** @type SimpleXMLElement $node */
		foreach ($xml->children() as $element => $node) {
			$data = array();

			if (!isset($array[$element])) {
				$array[$element] = '';
			}

			if (!$node->attributes() || $format === self::XML_NONE) {
				$data = self::xmlToArray($node, $format);

			} else {
				switch ($format) {
					case self::XML_GROUP:
						$data = array(
							'value' => self::autobox((string) $node),
							'attributes' => array()
						);

						if (count($node->children()) > 0) {
							$data['value'] = self::xmlToArray($node, $format);
						}

						foreach ($node->attributes() as $attr => $value) {
							$data['attributes'][$attr] = self::autobox((string) $value);
						}
					break;

					case self::XML_MERGE:
						if (count($node->children()) > 0) {
							$data = $data + self::xmlToArray($node, $format);
						} else {
							$data['value'] = self::autobox((string) $node);
						}
					/* fall-through */

					case self::XML_ATTRIBS:
						foreach ($node->attributes() as $attr => $value) {
							$data[$attr] = self::autobox((string) $value);
						}
					break;
				}
			}

			if (count($xml->{$element}) > 1) {
				$array[$element][] = $data;
			} else {
				$array[$element] = $data;
			}
		}

		return $array;
	}

}