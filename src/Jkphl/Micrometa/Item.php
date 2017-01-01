<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Jkphl\Micrometa;

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Jkphl\Utility\Url;

/**
 * Micro information item
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Item implements ItemInterface
{
    /**
     * Properties holding URL strings (and that need to get expanded / sanitized)
     *
     * @var array
     */
    public static $urlProperties = array('image', 'photo', 'logo', 'url', 'uid');
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
     * Item base URL (used for relative URL resolution)
     *
     * @var Url
     */
    protected $_url = null;
    /**
     * List of nested properties
     *
     * @var \stdClass
     */
    protected $_properties = null;
    /**
     * List of nested children
     *
     * @var \array
     */
    protected $_children = array();
    /**
     * Yielding parser
     *
     * @var string
     */
    const PARSER = 'none';

    /**
     * Constructor
     *
     * @param \array $data Item data
     * @param string|Url $url Item base URL
     */
    public function __construct(array $data, Url $url)
    {
        $this->_url = $url;
        $this->types = empty($data['type']) ? array() : (array)$data['type'];
        $this->_properties = new \stdClass();
        $classname = get_class($this);

        // Construct the properties
        if (!empty($data['properties']) && is_array($data['properties'])) {
            foreach ($data['properties'] as $property => $values) {
                if (is_array($values)) {
                    $property = lcfirst(implode('', array_map('ucfirst', explode('-', $property))));
                    $this->_properties->$property = array();
                    $propertyValues =& $this->_properties->$property;
                    $hasSubItems = false;
                    foreach ($values as $value) {
                        if ($this->_isItem($value)) {
                            $hasSubItems = true;
                            break;
                        }
                    }
                    foreach ($values as $value) {
                        $propertyValues[] = $hasSubItems ?
                            new $classname((array)$value, $this->_url) : $this->_resolveUrlValue($property, $value);
                    }
                }
            }
        }

        // Construct the children
        if (!empty($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if ($this->_isItem($child)) {
                    $this->_children[] = ($child instanceof self) ? $child : new $classname((array)$child, $this->_url);
                }
            }
        }

        if (!empty($data['value'])) {
            $this->value = $data['value'];
        }

        if (!empty($data['id'])) {
            $this->id = $data['id'];
        }
    }

    /**
     * Check if this item is of a specific type
     *
     * @param \string $type List of type names (arbitrary length)
     * @return \boolean                If this item is of a specific type
     */
    public function isOfType()
    {
        return func_num_args() ? (count(
                array_intersect(
                    $this->types, is_array(func_get_arg(0)) ? func_get_arg(0) : array_map('trim', func_get_args())
                )
            ) > 0) : false;
    }

    /**
     * Return the first available property in a list of properties
     *
     * @param \string $property1 First property
     * @param \string $property2 Second property
     * ...
     * @return \mixed                Property value
     */
    public function firstOf()
    {
        foreach (func_get_args() as $property) {
            $value = $this->$property;
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Return a list of properties or a single property
     *
     * @param \string $key Property (list) name
     * @return \mixed                Property (list) value(s)
     */
    public function __get($key)
    {

        // If a single property value was requested
        if (isset($this->_properties->$key)) {
            $property =& $this->_properties->$key;
            return $property[0];

            // Else: If a property value list was requested
        } elseif ((substr($key, -1) == 's') && isset($this->_properties->{substr($key, 0, -1)})) {
            return $this->_properties->{substr($key, 0, -1)};

            // Else: Unknown property
        } else {
            return null;
        }
    }

    /**
     * String serialization as JSON object
     *
     * @return \string                String serialization as JSON object
     */
    public function __toString()
    {
        return strval($this->toJSON(false));
    }

    /**
     * Return a JSON representation of the embedded micro information
     *
     * @param \boolean $beautify Beautify the JSON output (available since PHP 5.4)
     * @return \string                    JSON representation
     */
    public function toJSON($beautify = false)
    {
        $options = 0;
        if ($beautify && version_compare(PHP_VERSION, '5.4', '>=')) {
            $options |= JSON_PRETTY_PRINT;
        }
        return json_encode($this->toObject(), $options);
    }

    /**
     * Return a vanilla object representation of this item
     *
     * @return \stdClass            Vanilla object item representation
     */
    public function toObject()
    {
        $result = (object)array(
            'id' => $this->id,
            'types' => $this->types,
            'value' => $this->value,
            'properties' => array(),
            'children' => array(),
            'parser' => $this->parser(),
        );

        // Run through all properties and recursively serialize them
        foreach ($this->_properties as $propertyKey => $propertyValues) {
            if (is_array($propertyValues) && count($propertyValues)) {
                $result->properties[$propertyKey] = array();
                foreach ($propertyValues as $propertyValue) {
                    $result->properties[$propertyKey][] = ($propertyValue instanceof self) ? $propertyValue->toObject() : $propertyValue;
                }
            }
        }

        // Run through all children and recursively serialize them
        foreach ($this->_children as $child) {
            $result->children[] = $child->toObject();
        }

        return $result;
    }

    /**
     * Check if a subelement is a microcontent item itself
     *
     * @param \mixed $item Subelement
     * @return \boolean                Is an item
     */
    protected function _isItem($item)
    {
        if ($item instanceof ItemInterface) {
            return true;
        } elseif (is_array($item)) {
            return array_key_exists('type', $item) &&
                is_array($item['type']) &&
                array_key_exists('properties', $item) &&
                is_array($item['properties']);
        } elseif (is_object($item)) {
            return isset($item->type) &&
                is_array($item->type) &&
                isset($item->properties) &&
                is_array($item->properties);
        } else {
            return false;
        }
    }

    /**
     * Sanitize URL values
     *
     * @param \string $property Property name
     * @param \mixed $value Value
     * @return mixed                URL resolved value
     */
    protected function _resolveUrlValue($property, $value)
    {
        return in_array($property, self::$urlProperties) ? strval(
            Url::instance($value, true, $this->_url)
        ) : $value;
    }

    /**
     * Return the parser name
     *
     * @return string Parser name
     */
    public function parser() {
        return static::PARSER;
    }
}
