<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2014 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 */

namespace Jkphl\Micrometa;

/***********************************************************************************
 *  The MIT License (MIT)
 *  
 *  Copyright © 2014 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

/**
 * Micro information item
 *
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2014 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 */
class Item {
	/**
	 * Item base URL (used for relative URL resolution)
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	/**
	 * Item type list
	 * 
	 * @var \array
	 */
	public $types = array();
	/**
	 * Explicit item ID
	 *
	 * @var \string
	 */
	public $id = null;
	/**
	 * Explicit item value
	 * 
	 * @var \string
	 */
	public $value = null;
	/**
	 * List of nested properties
	 *
	 * @var \array
	 */
	protected $_properties = null;
	/**
	 * Properties holding URL strings (and that need to get expanded / sanitized)
	 * 
	 * @var array
	 */
	protected static $_urlProperties = array('image', 'photo', 'logo', 'url');
	
	/************************************************************************************************
	 * PUBLIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Constructor
	 * 
	 * @param \array $data					Item data
	 * @param \Jkphl\Utility\Url $url		Item base URL
	 * @return \Jkphl\Micrometa\Item		Micro information item
	 */
	public function __construct(array $data, \Jkphl\Utility\Url $url) {
		$this->_url					= $url;
		$this->types				= empty($data['type']) ? array() : (array)$data['type'];
		$this->_properties			= new \stdClass();
		
		if (!empty($data['properties']) && is_array($data['properties'])) {
			foreach ($data['properties'] as $property => $values) {
				if (is_array($values)) {
					$property							= lcfirst(implode('', array_map('ucfirst', explode('-', $property))));
					$this->_properties->$property		= array();
					$propertyValues						=& $this->_properties->$property;
					$hasSubItems						= false;
					foreach ($values as $value) {
						if ($this->_isItem($value)) {
							$hasSubItems				= true;
							break;
						}
					}
					foreach ($values as $value) {
						$propertyValues[]				= $hasSubItems ? new self((array)$value, $this->_url) : $this->_resolveUrlValue($property, $value);
					}
				}
			}
		}
		if (!empty($data['value'])) {
			$this->value			= $data['value'];
		}
		if (!empty($data['id'])) {
			$this->id				= $data['id'];
		}
	}
	
	/**
	 * Check if this item is of a specific type
	 * 
	 * @param \string $type			List of type names (arbitrary length)
	 * @return \boolean				If this item is of a specific type
	 */
	public function isOfType() {
		return func_num_args() ? (count(array_intersect($this->types, is_array(func_get_arg(0)) ? func_get_arg(0) : array_map('trim', func_get_args()))) > 0) : false;
	}
	
	/**
	 * Return the first available property in a list of properties
	 * 
	 * @param \string $property1	First property
	 * @param \string $property2	Second property
	 * ...
	 * @return \mixed				Property value
	 */
	public function firstOf() {
		foreach (func_get_args() as $property) {
			$value					= $this->$property;
			if ($value !== null) {
				return $value;
			}
		}
		return null;
	}
	
	/**
	 * Return a vanilla object representation of this item 
	 * 
	 * @return \stdClass			Vanilla object item representation
	 */
	public function toObject() {
		$result						= (object)array(
			'id'					=> $this->id,
			'types'					=> $this->types,
			'value'					=> $this->value,
			'properties'			=> array(),
		);
		
		// Run through all properties and recursively refine them
		foreach ($this->_properties as $propertyKey => $propertyValues) {
			if (is_array($propertyValues) && count($propertyValues)) {
				$result->properties[$propertyKey]			= array();
				foreach ($propertyValues as $propertyValue) {
					$result->properties[$propertyKey][]		= ($propertyValue instanceof self) ? $propertyValue->toObject() : $propertyValue; 
				}
			}
		}
		
		return $result;
	}
	
	/************************************************************************************************
	 * MAGIC METHODS
	 ***********************************************************************************************/

	/**
	 * Return a list of properties or a single property
	 *
	 * @param \string $key			Property (list) name
	 * @return \mixed				Property (list) value(s)
	 */
	public function __get($key) {
		$key						= strtolower(preg_replace("%([A-Z])%", "-$1", $key));
	
		// If a single property value was requested
		if (isset($this->_properties->$key)) {
			$property				=& $this->_properties->$key;
			return $property[0];
		
		// Else: If a property value list was requested 
		} elseif ((substr($key, -1) == 's') && isset($this->_properties->{substr($key, 0, -1)})) {
			return $this->_properties->{substr($key, 0, -1)};
				
		// Else: Unknown property
		} else {
			return null;
		}
	}
	
	/************************************************************************************************
	 * PRIVATE METHODS
	 ***********************************************************************************************/
	
	/**
	 * Check if a subelement is a microcontent item itself
	 * 
	 * @param \mixed $item			Subelement
	 * @return \boolean				Is an item
	 */
	protected function _isItem($item) {
		if (is_array($item)) {
			return array_key_exists('type', $item) && is_array($item['type']) && array_key_exists('properties', $item) && is_array($item['properties']);
		} elseif (is_object($item)) {
			return isset($item->type) && is_array($item->type) && isset($item->properties) && is_array($item->properties);
		} else {
			return false;
		}
	}
	
	/**
	 * Sanitize URL values
	 * 
	 * @param \string $property		Property name
	 * @param \mixed $value			Value
	 * @return void
	 */
	protected function _resolveUrlValue($property, $value) {
		if (in_array($property, self::$_urlProperties)) {
			$value					= new \Jkphl\Utility\Url($value);
			$value					= strval($value->resolve($this->_url));
		}
		return $value;
	}
}