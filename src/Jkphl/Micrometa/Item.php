<?php

namespace Jkphl\Micrometa;

/***********************************************************************************
 *  The MIT License (MIT)
 *  
 *  Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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
 * Microcontent parser result
 * 
 * @author joschi
 */
class Item {
	/**
	 * Base URL
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	/**
	 * Type list
	 * 
	 * @var \array
	 */
	protected $_types = array();
	/**
	 * Property list
	 * 
	 * @var \array
	 */
	protected $_properties = null;
	/**
	 * Item value
	 * 
	 * @var \string
	 */
	protected $_value = null;
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @return void
	 */
	public function __construct(array $data, \Jkphl\Utility\Url $url) {
		$this->_url					= $url;
		$this->_types				= empty($data['type']) ? array() : (array)$data['type'];
		$this->_properties			= new \stdClass();
		
		if (!empty($data['properties']) && is_array($data['properties'])) {
			foreach ($data['properties'] as $property => $values) {
				if (is_array($values)) {
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
						$propertyValues[]				= $hasSubItems ? new self($value, $this->_url) : $this->_resolveUrlValue($property, $value);
					}
				}
			}
		}
		if (!empty($data['value'])) {
			$this->_value			= $data['value'];
		}
	}
	
	/**
	 * Return if this item is of a specific type
	 * 
	 * @param \string $type			List of type names (arbitrary length)
	 * @return \boolean				If this item is of a specific type
	 */
	public function isOfType() {
		return func_num_args() ? (count(array_intersect($this->_types, is_array(func_get_arg(0)) ? func_get_arg(0) : array_map('trim', func_get_args()))) > 0) : false;
	}
	
	/**
	 * Return a list of properties or a single property
	 * 
	 * @param \string $key			Property (list) name
	 * @return \mixed				Property (list) value(s)
	 */
	public function __get($key) {
		
		// Special case: Value property
		if ($key == 'value') {
			return $this->_value;
			
		// Else: If this is a known property
		} elseif (isset($this->_properties->$key)) {
			$property				=& $this->_properties->$key;
			return $property[0];
			
		// Else: If this is a known properties list
		} elseif ((substr($key, -1) == 's') && isset($this->_properties->{substr($key, 0, -1)})) {
			return $this->_properties->{substr($key, 0, -1)};
			
		// Else: Empty return value
		} else {
			return null;
		}
	}
	
	/**
	 * Return the first available property of a property list
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
	 * @return \stdClass			Vanilla item representation
	 */
	public function toObject() {
		$result						= (object)array(
			'types'					=> $this->_types,
			'properties'			=> array(),
			'value'					=> $this->_value,
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
	 * PRIVATE METHODS
	 ***********************************************************************************************/
	
	/**
	 * Check if a subelement is a microcontent item itself
	 * 
	 * @param \mixed $item			Subelement
	 * @return \boolean				Is an item
	 */
	protected function _isItem($item) {
		return is_array($item) && array_key_exists('type', $item) && is_array($item['type']) && array_key_exists('properties', $item) && is_array($item['properties']);
	}
	
	/**
	 * Sanitize URL values
	 * 
	 * @param \string $property		Property name
	 * @param \mixed $value			Value
	 * @return void
	 */
	protected function _resolveUrlValue($property, $value) {
		if (in_array($property, array('image', 'photo', 'logo', 'url'))) {
			$value					= new \Jkphl\Utility\Url($value);
			$value					= strval($value->resolve($this->_url));
		}
		return $value;
	}
}