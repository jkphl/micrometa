<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 * @author     Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Jkphl\Micrometa\Application\Contract\ValueInterface;
use Jkphl\Micrometa\Application\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Item\ItemInterface;
use Jkphl\Micrometa\Application\Value\AlternateValues;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Ports\Exceptions\RuntimeException;

/**
 * Item factory
 *
 * @package    Jkphl\Micrometa
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
     * Property lit factory
     *
     * @var PropertyListFactoryInterface
     */
    protected $propertyListFactory;

    /**
     * Item factory constructor
     *
     * @param int $format Parser format
     */
    public function __construct($format)
    {
        $this->format              = $format;
        $this->propertyListFactory = new PropertyListFactory();
    }

    /**
     * Prepare a single property value
     *
     * @param mixed $propertyValue Property Value
     *
     * @return ValueInterface Value
     */
    protected function processPropertyValue($propertyValue)
    {
        // If this is an item value
        if (is_object($propertyValue) && isset($propertyValue->type)) {
            return $this->__invoke($propertyValue);

            // Else these are alternate values
        } elseif (is_array($propertyValue)) {
            return new AlternateValues(array_map([$this, __METHOD__], $propertyValue));
        }

        list($propertyValue, $language) = $this->processLanguageTaggedPropertyValue($propertyValue);

        return new StringValue($propertyValue, $language);
    }

    /**
     * Create an item instance
     *
     * The item object is expected to be layed out like this:
     *
     * {
     *     format: 2, // Parser formag
     *     type: 'type', // String / IRI object (see below) or list of strings / IRI objects
     *     properties: [...], // List of item property objects (see below)
     *     value: 'Item value', // Item value (optional)
     *     id: 'item-1', // Item ID (optional)
     *     children: [...] // Nested item objects (optional)
     * }
     *
     * The item property objects are expected to be layed out like this:
     *
     * {
     *      name: 'name', // Property name
     *      profile: 'http://microformats.org/profile/', // Profile
     *      values: [...] // List of property values
     * }
     *
     * Item property values may be either
     *
     * - a string: Interpreted as simple value
     * - an array: Interpreted as alternate simple values
     * - an object: Interpreted as an object property (recursively processed)
     *
     * IRI objects are expected to be layed out like this:
     *
     * {
     *      name: 'h-entry',
     *      profile: 'http://microformats.org/profile/', // Profile (optional)
     * }
     *
     * @param \stdClass $item Raw item
     *
     * @return ItemInterface Item instance
     */
    public function __invoke(\stdClass $item)
    {
        $type         = $this->normalizeEmptyValue($item, 'type');
        $itemId       = $this->normalizeEmptyValue($item, 'id');
        $itemLanguage = $this->normalizeEmptyValue($item, 'lang');
        $value        = $this->normalizeEmptyValue($item, 'value');
        $children     = isset($item->children) ? array_map([$this, __METHOD__], $item->children) : [];
        $properties   = $this->getProperties($item);

        return new Item(
            $this->format,
            $this->propertyListFactory,
            $type,
            $properties,
            $children,
            $itemId,
            $itemLanguage,
            $value
        );
    }

    /**
     * Normalize an empty item value
     *
     * @param \stdClass $item  Item
     * @param string $property Property name
     *
     * @return string|null Normalized property value
     */
    protected function normalizeEmptyValue(\stdClass $item, $property)
    {
        return isset($item->$property) ? $item->$property : null;
    }

    /**
     * Prepare item properties
     *
     * @param \stdClass $item Item
     *
     * @return array Properties
     */
    protected function getProperties(\stdClass $item)
    {
        $properties = [];
        if (isset($item->properties) && is_array($item->properties)) {
            foreach ($item->properties as $property) {
                $this->processProperty($properties, $property);
            }
        }

        return $properties;
    }

    /**
     * Process a property
     *
     * @param array $properties   Properties
     * @param \stdClass $property Property
     */
    protected function processProperty(array &$properties, $property)
    {
        try {
            if ($this->validatePropertyStructure($property)) {
                $property->values = $this->getPropertyValues($property->values);
                if (count($property->values)) {
                    $properties[] = $property;
                }
            }
        } catch (InvalidArgumentException $exception) {
            // Skip this property
        }
    }

    /**
     * Validate if an object is a valid property
     *
     * @param \stdClass $property Property
     *
     * @return bool Is a valid property
     */
    protected function validatePropertyStructure($property)
    {
        return is_object($property) && isset($property->profile) && isset($property->name) && isset($property->values);
    }

    /**
     * Prepare item property values
     *
     * @param array $propertyValues Property values
     *
     * @return array Expanded property values
     * @throws InvalidArgumentException If it's not a list of property values
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

    /**
     * Process a language tagged property value
     *
     * @param string|\stdClass $propertyValue Property value
     *
     * @return array Language and property value
     * @throws RuntimeException If this is an invalid language tagged value
     */
    protected function processLanguageTaggedPropertyValue($propertyValue)
    {
        $language = null;
        if (is_object($propertyValue)) {
            // If this is an invalid language tagged object
            if (!isset($propertyValue->lang) || !isset($propertyValue->value)) {
                throw new RuntimeException(
                    RuntimeException::INVALID_LANGUAGE_TAGGED_VALUE_STR,
                    RuntimeException::INVALID_LANGUAGE_TAGGED_VALUE
                );
            }

            $language      = $propertyValue->lang;
            $propertyValue = $propertyValue->value;
        }

        return [$propertyValue, $language];
    }
}
