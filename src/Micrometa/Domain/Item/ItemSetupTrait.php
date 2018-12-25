<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa\Domain\Item;

use Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Domain\Factory\IriFactory;
use Jkphl\Micrometa\Domain\Factory\PropertyListFactoryInterface;
use Jkphl\Micrometa\Domain\Value\ValueInterface;

/**
 * Item setup methods
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
trait ItemSetupTrait
{
    /**
     * Property list factory
     *
     * @var PropertyListFactoryInterface
     */
    protected $propertyListFactory;

    /**
     * Item type(s)
     *
     * @var \stdClass[]
     */
    protected $type;

    /**
     * Item properties
     *
     * @var PropertyListInterface
     */
    protected $properties;

    /**
     * Item ID
     *
     * @var string
     */
    protected $itemId;

    /**
     * Item language
     *
     * @var string
     */
    protected $itemLanguage;

    /**
     * Setup the item
     *
     * @param PropertyListFactoryInterface $propertyListFactory Property list factory
     * @param string[]|\stdClass[] $type                        Item type(s)
     * @param \stdClass[] $properties                           Item properties
     * @param string $itemId                                    Item ID
     * @param string $itemLanguage                              Item language
     */
    protected function setup(
        PropertyListFactoryInterface $propertyListFactory,
        array $type,
        array $properties,
        $itemId,
        $itemLanguage
    ) {
        $this->propertyListFactory = $propertyListFactory;
        $this->type                = $this->valTypes($type);
        $this->properties          = $this->valProperties($properties);
        $this->itemId              = $itemId ?: null;
        $this->itemLanguage        = $itemLanguage ?: null;
    }

    /**
     * Validate and sanitize the item types
     *
     * @param string[]|\stdClass[] $types Item types
     *
     * @return array Validated item types
     * @throws InvalidArgumentException If there are no valid types
     */
    protected function valTypes(array $types)
    {
        $nonEmptyTypes = array_filter(array_map([$this, 'valType'], $types));

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
     *
     * @return PropertyListInterface Validated item properties
     * @throws InvalidArgumentException If the property name is empty
     */
    protected function valProperties(array $properties)
    {
        $validatedProperties = $this->propertyListFactory->create();

        // Run through all validated properties
        foreach (array_filter(array_map([$this, 'valProp'], $properties)) as $property) {
            $validatedProperties->add($property);
        }

        return $validatedProperties;
    }

    /**
     * Validate a single property
     *
     * @param \stdClass $property Property
     *
     * @return \stdClass Validated property
     */
    protected function valProp($property)
    {
        // Validate the property structure
        $this->valPropStructure($property);

        // If the property has values
        if (count($property->values)) {
            // Validate the property name
            $property->name = $this->valPropName($property);

            // Validate the property values
            $property->values = $this->valPropValues($property->values);

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
     *
     * @throws InvalidArgumentException If the property object is invalid
     */
    protected function valPropStructure($property)
    {
        // If the property object is invalid
        if (!is_object($property) || !$this->valPropProperties($property)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_PROPERTY_STR,
                InvalidArgumentException::INVALID_PROPERTY
            );
        }
    }

    /**
     * Validate the properties of a property
     *
     * @param \stdClass $property Property
     *
     * @return bool Property properties are valid
     */
    protected function valPropProperties($property)
    {
        return isset($property->profile)
               && isset($property->name)
               && isset($property->values)
               && is_array($property->values);
    }

    /**
     * Validate a property name
     *
     * @param \stdClass $property Property
     *
     * @return string Property name
     */
    protected function valPropName($property)
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
     *
     * @return array Validated property values
     * @throws InvalidArgumentException If the value is not a nested item
     */
    protected function valPropValues(array $values)
    {
        $validPropertyValues = [];

        // Run through all property values
        /** @var ValueInterface $value */
        foreach ($values as $value) {
            $this->procPropValue($value, $validPropertyValues);
        }

        return $validPropertyValues;
    }

    /**
     * Process a (non-empty) property value
     *
     * @param ValueInterface $value      Property value
     * @param array $validPropertyValues Non-empty property values
     */
    protected function procPropValue($value, array &$validPropertyValues)
    {
        // If the value is not a nested item
        if (!($value instanceof ValueInterface)) {
            throw new InvalidArgumentException(
                sprintf(InvalidArgumentException::INVALID_PROPERTY_VALUE_STR, gettype($value)),
                InvalidArgumentException::INVALID_PROPERTY_VALUE
            );
        }

        // If the value isn't empty
        if (!$value->isEmpty()) {
            $validPropertyValues[] = $value;
        }
    }

    /**
     * Validate a single item type
     *
     * @param \stdClass|Iri|string $type Item type
     *
     * @return Iri|null Validated item type
     * @throws InvalidArgumentException If the item type object is invalid
     */
    protected function valType($type)
    {
        $type = IriFactory::create($type);

        return strlen($type->name) ? $type : null;
    }
}
