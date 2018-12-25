<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
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

namespace Jkphl\Micrometa\Ports\Item;

use Jkphl\Micrometa\Application\Contract\ValueInterface;
use Jkphl\Micrometa\Application\Factory\AliasFactory;
use Jkphl\Micrometa\Application\Factory\PropertyListFactory;
use Jkphl\Micrometa\Application\Item\ItemInterface as ApplicationItemInterface;
use Jkphl\Micrometa\Application\Item\PropertyListInterface;
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException as DomainOutOfBoundsException;
use Jkphl\Micrometa\Domain\Item\Iri;
use Jkphl\Micrometa\Infrastructure\Factory\ItemFactory;
use Jkphl\Micrometa\Infrastructure\Factory\ProfiledNamesFactory;
use Jkphl\Micrometa\Infrastructure\Parser\ProfiledNamesList;
use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

/**
 * Micro information item
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class Item extends ItemList implements ItemInterface
{
    /**
     * Application item
     *
     * @var ApplicationItemInterface
     */
    protected $item;

    /**
     * Item constructor
     *
     * @param ApplicationItemInterface $item Application item
     */
    public function __construct(ApplicationItemInterface $item)
    {
        $this->item = $item;
        parent::__construct(ItemFactory::createFromApplicationItems($this->item->getChildren()));
    }

    /**
     * Get the first value of an item property
     *
     * @param string $name Item property name
     *
     * @return ValueInterface|ValueInterface[]|array|ItemInterface First value of an item property
     * @api
     */
    public function __get($name)
    {
        return $this->getProperty($name, null, 0);
    }

    /**
     * Get a single property (value)
     *
     * @param string|\stdClass|Iri $name Property name
     * @param string $profile            Property profile
     * @param int|null $index            Property value index
     *
     * @return ValueInterface|ValueInterface[]|array|ItemInterface Property value(s)
     * @throws OutOfBoundsException If the property name is unknown
     * @throws OutOfBoundsException If the property value index is out of bounds
     * @api
     */
    public function getProperty($name, $profile = null, $index = null)
    {
        try {
            $propertyValues = $this->item->getProperty($name, $profile);
        } catch (DomainOutOfBoundsException $exception) {
            throw new OutOfBoundsException($exception->getMessage(), $exception->getCode());
        }

        // Return the value(s)
        return ($index === null) ?
            array_map([$this, 'getPropertyValue'], $propertyValues) : $this->getPropertyIndex($propertyValues, $index);
    }

    /**
     * Return a particular property index
     *
     * @param ValueInterface[] $propertyValues Property values
     * @param int $index                       Property value index
     *
     * @return ValueInterface|ItemInterface
     */
    protected function getPropertyIndex(array $propertyValues, $index)
    {
        // If the property value index is out of bounds
        if (!isset($propertyValues[$index])) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_PROPERTY_VALUE_INDEX_STR, $index),
                OutOfBoundsException::INVALID_PROPERTY_VALUE_INDEX
            );
        }

        return $this->getPropertyValue($propertyValues[$index]);
    }

    /**
     * Prepare a property value for returning it
     *
     * @param ValueInterface $value Property value
     *
     * @return ValueInterface|ItemInterface Returnable property value
     */
    protected function getPropertyValue(ValueInterface $value)
    {
        return ($value instanceof ApplicationItemInterface) ?
            ItemFactory::createFromApplicationItem($value) : $value;
    }

    /**
     * Return whether the item is of a particular type (or contained in a list of types)
     *
     * The item type(s) can be specified in a variety of ways, @see ProfiledNamesFactory::createFromArguments()
     *
     * @param array ...$types Item types
     *
     * @return boolean Item type is contained in the list of types
     * @api
     */
    public function isOfType(...$types)
    {
        /** @var ProfiledNamesList $profiledTypes */
        $profiledTypes = ProfiledNamesFactory::createFromArguments($types);
        $aliasFactory  = new AliasFactory();

        // Run through all item types
        /** @var \stdClass $itemType */
        foreach ($this->item->getType() as $itemType) {
            $itemTypeNames = $aliasFactory->createAliases($itemType->name);
            if ($this->isOfProfiledTypes($itemType->profile, $itemTypeNames, $profiledTypes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return whether an aliased item type is contained in a set of query types
     *
     * @param string $profile          Type profile
     * @param array $names             Aliased type names
     * @param ProfiledNamesList $types Query types
     *
     * @return bool Item type is contained in the set of query types
     */
    protected function isOfProfiledTypes($profile, array $names, ProfiledNamesList $types)
    {
        // Run through all query types
        /** @var \stdClass $queryType */
        foreach ($types as $queryType) {
            if ($this->isTypeInNames($queryType, $profile, $names)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test whether a type is contained in a list of names
     *
     * @param \stdClass $type Type
     * @param string $profile Type profile
     * @param array $names    Aliased type names
     *
     * @return bool Type is contained in names list
     */
    protected function isTypeInNames($type, $profile, array $names)
    {
        return in_array($type->name, $names) &&
               (($type->profile === null) ? true : ($type->profile == $profile));
    }

    /**
     * Get all values of the first available property in a stack
     *
     * The property stack can be specified in a variety of ways, @see ProfiledNamesFactory::createFromArguments()
     *
     * @param array $properties Properties
     *
     * @return ValueInterface[]|array Property values
     * @throws InvalidArgumentException If no property name was given
     * @throws OutOfBoundsException If none of the requested properties is known
     * @api
     */
    public function getFirstProperty(...$properties)
    {
        /** @var ProfiledNamesList $properties */
        $properties = ProfiledNamesFactory::createFromArguments(func_get_args());

        // Prepare a default exception
        $exception = new OutOfBoundsException(
            OutOfBoundsException::NO_MATCHING_PROPERTIES_STR,
            OutOfBoundsException::NO_MATCHING_PROPERTIES
        );

        // Run through all properties
        foreach ($properties as $property) {
            try {
                return (array)$this->getProperty($property->name, $property->profile);
            } catch (OutOfBoundsException $exception) {
                continue;
            }
        }

        throw $exception;
    }

    /**
     * Return all properties
     *
     * @return PropertyListInterface Properties
     * @api
     */
    public function getProperties()
    {
        $propertyList = (new PropertyListFactory())->create();
        foreach ($this->item->getProperties() as $propertyName => $propertyValues) {
            $propertyList[$propertyName] = array_map([$this, 'getPropertyValue'], $propertyValues);
        }

        return $propertyList;
    }

    /**
     * Return an object representation of the item
     *
     * @return \stdClass Micro information item
     * @api
     */
    public function toObject()
    {
        return $this->item->export();
    }

    /**
     * Get the item type
     *
     * @return \stdClass[] Item type
     * @api
     */
    public function getType()
    {
        return $this->item->getType();
    }

    /**
     * Get the item format
     *
     * @return int Item format
     * @api
     */
    public function getFormat()
    {
        return $this->item->getFormat();
    }

    /**
     * Get the item ID
     *
     * @return string Item ID
     * @api
     */
    public function getId()
    {
        return $this->item->getId();
    }

    /**
     * Get the item language
     *
     * @return string Item language
     * @api
     */
    public function getLanguage()
    {
        return $this->item->getLanguage();
    }

    /**
     * Return the item value
     *
     * @return string Item value
     * @api
     */
    public function getValue()
    {
        return $this->item->getValue();
    }
}
