<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

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

namespace Jkphl\Micrometa\Application\Factory;

use Jkphl\Micrometa\Application\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Item\ItemInterface;
use Jkphl\Micrometa\Application\Value\AlternateValues;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Value\ValueInterface;

/**
 * Item factory
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 */
class ItemFactory
{
    /**
     * Parser format
     *
     * @var int
     */
    protected $format;

    /**
     * Item factory constructor
     *
     * @param int $format Parser format
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    /**
     * Prepare a single property value
     *
     * @param mixed $propertyValue Property Value
     * @return ValueInterface Value
     */
    protected function processPropertyValue($propertyValue)
    {
        if (is_object($propertyValue)) {
            return $this->__invoke($propertyValue);
        }
        if (is_array($propertyValue)) {
            return new AlternateValues($propertyValue);
        }
        return new StringValue($propertyValue);
    }

    /**
     * Create an item instance
     *
     * @param \stdClass $item Raw item
     * @return ItemInterface Item instance
     */
    public function __invoke(\stdClass $item)
    {
        $type = isset($item->type) ? $item->type : null;
        $itemId = isset($item->id) ? $item->id : null;
        $value = isset($item->value) ? $item->value : null;
        $children = isset($item->children) ? array_map([$this, __METHOD__], $item->children) : [];
        $properties = $this->getProperties($item);
        return new Item($this->format, $type, $properties, $children, $itemId, $value);
    }

    /**
     * Prepare item properties
     *
     * @param \stdClass $item Item
     * @return array Properties
     */
    protected function getProperties(\stdClass $item)
    {
        $properties = [];
        if (isset($item->properties) && is_array($item->properties)) {
            foreach ($item->properties as $propertyName => $propertyValues) {
                $this->processPropertyValues($properties, $propertyName, $propertyValues);
            }
        }
        return $properties;
    }

    /**
     * Process the values of a property
     *
     * @param array $properties Properties
     * @param string $propertyName Property name
     * @param array $propertyValues Property values
     */
    protected function processPropertyValues(array &$properties, $propertyName, $propertyValues)
    {
        try {
            $expandedPropertyValues = $this->getPropertyValues($propertyValues);
            if (count($expandedPropertyValues)) {
                $properties[$propertyName] = $expandedPropertyValues;
            }
        } catch (InvalidArgumentException $e) {
            // Skip this property
        }
    }

    /**
     * Prepare item property values
     *
     * @param array $propertyValues Property values
     * @return array Expanded property values
     */
    protected function getPropertyValues($propertyValues)
    {
        // If it's not a list of property values
        if (!is_array($propertyValues)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_PROPERTY_VALUES_STR,
                InvalidArgumentException::INVALID_PROPERTY_VALUES
            );
        }

        return array_map([$this, 'processPropertyValue'], $propertyValues);
    }
}
