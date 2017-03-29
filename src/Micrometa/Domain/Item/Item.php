<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain\Miom
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

namespace Jkphl\Micrometa\Domain\Item;

use Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Domain\Factory\IriFactory;
use Jkphl\Micrometa\Domain\Value\ValueInterface;

/**
 * Micro information item
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
class Item implements ItemInterface
{
    /**
     * Item type(s)
     *
     * @var string[]
     */
    protected $type;

    /**
     * Item properties
     *
     * @var PropertyList
     */
    protected $properties;

    /**
     * Item ID
     *
     * @var string
     */
    protected $itemId;

    /**
     * Item constructor
     *
     * @param string|\stdClass|\stdClass[] $type Item type(s)
     * @param array[] $properties Item properties
     * @param string|null $itemId Item id
     */
    public function __construct($type, array $properties = [], $itemId = null)
    {
        $this->type = $this->validateTypes(is_array($type) ? $type : [$type]);
        $this->properties = $this->validateProperties($properties);
        $this->itemId = trim($itemId) ?: null;
    }

    /**
     * Validate and sanitize the item types
     *
     * @param \stdClass[] $types Item types
     * @return array Validated item types
     * @throws InvalidArgumentException If there are no valid types
     */
    protected function validateTypes(array $types)
    {
        $nonEmptyTypes = array_filter(array_map([$this, 'validateType'], $types));

        // If there are no valid types
        if (!count($nonEmptyTypes)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::EMPTY_TYPES_STR,
                InvalidArgumentException::EMPTY_TYPES
            );
        }

        return array_values($nonEmptyTypes);
    }

    /**
     * Validate the item properties
     *
     * @param array $properties Item properties
     * @return PropertyList Validated item properties
     * @throws InvalidArgumentException If the property name is empty
     */
    protected function validateProperties(array $properties)
    {
        $validatedProperties = new PropertyList();

        // Run through all validated properties
        foreach (array_filter(array_map([$this, 'validateProperty'], $properties)) as $property) {
            $validatedProperties->add($property);
        }

        return $validatedProperties;
    }

    /**
     * Return the item types
     *
     * @return string[] Item types
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the item ID (if any)
     *
     * @return string|null Item id
     */
    public function getId()
    {
        return $this->itemId;
    }

    /**
     * Return all item properties
     *
     * @return PropertyList Item properties list
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Return the values of a particular property
     *
     * @param string $name Property name
     * @param string|null $profile Property profile
     * @return array Item property values
     * @throws OutOfBoundsException If the property is unknown
     */
    public function getProperty($name, $profile = null)
    {
        $iri = IriFactory::create(($profile === null) ? $name : (object)['profile' => $profile, 'name' => $name]);
        return $this->properties->offsetGet($iri);
    }

    /**
     * Return whether the value should be considered empty
     *
     * @return boolean Value is empty
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Validate a single property
     *
     * @param \stdClass $property Property
     * @return \stdClass Validated property
     */
    protected function validateProperty($property)
    {
        // Validate the property structure
        $this->validatePropertyStructure($property);

        // If the property has values
        if (count($property->values)) {
            // Validate the property name
            $property->name = $this->validatePropertyName($property);

            // Validate the property values
            $property->values = $this->validatePropertyValues($property->values);

            // If the property has significant values
            if (count($property->values)) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Validate the structure of a property object
     *
     * @param \stdClass $property Property object
     * @throws InvalidArgumentException If the property object is invalid
     */
    protected function validatePropertyStructure($property)
    {
        // If the property object is invalid
        if (!is_object($property)
            || !isset($property->profile)
            || !isset($property->name)
            || !isset($property->values)
            || !is_array($property->values)
        ) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_PROPERTY_STR,
                InvalidArgumentException::INVALID_PROPERTY
            );
        }
    }

    /**
     * Validate a property name
     *
     * @param \stdClass $property Property
     * @return string Property name
     */
    protected function validatePropertyName($property)
    {
        $propertyName = trim($property->name);

        // If the property name is empty
        if (!strlen($propertyName)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::EMPTY_PROPERTY_NAME_STR,
                InvalidArgumentException::EMPTY_PROPERTY_NAME
            );
        }

        return $propertyName;
    }

    /**
     * Validate a list of property values
     *
     * @param array $values Property values
     * @return array Validated property values
     * @throws InvalidArgumentException If the value is not a nested item
     */
    protected function validatePropertyValues(array $values)
    {
        $nonEmptyPropertyValues = [];

        // Run through all property values
        /** @var ValueInterface $value */
        foreach ($values as $value) {
            // If the value is not a nested item
            if (!($value instanceof ValueInterface)) {
                throw new InvalidArgumentException(
                    sprintf(InvalidArgumentException::INVALID_PROPERTY_VALUE_STR, gettype($value)),
                    InvalidArgumentException::INVALID_PROPERTY_VALUE
                );
            }

            if (!$value->isEmpty()) {
                $nonEmptyPropertyValues[] = $value;
            }
        }

        return $nonEmptyPropertyValues;
    }

    /**
     * Validate a single item type
     *
     * @param \stdClass|string $type Item type
     * @return \stdClass|null Validated item type
     * @throws InvalidArgumentException If the item type object is invalid
     */
    protected function validateType($type)
    {
        $type = IriFactory::create($type);
        return strlen($type->name) ? $type : null;
    }
}
