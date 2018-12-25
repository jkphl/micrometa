<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain\Item
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

namespace Jkphl\Micrometa\Domain\Item;

use Jkphl\Micrometa\Domain\Exceptions\ErrorException;
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Domain\Factory\IriFactory;

/**
 * Property list
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\RdfaLiteMicrodata\Domain
 */
class PropertyList implements PropertyListInterface
{
    /**
     * Property values
     *
     * @var array[]
     */
    protected $values = [];
    /**
     * Property names
     *
     * @var \stdClass[]
     */
    protected $names = [];
    /**
     * Name cursor mapping
     *
     * @var int[]
     */
    protected $nameToCursor = [];
    /**
     * Internal cursor
     *
     * @var int
     */
    protected $cursor = 0;

    /**
     * Unset a property
     *
     * @param \stdClass|string $iri IRI
     *
     * @throws ErrorException
     */
    public function offsetUnset($iri)
    {
        throw new ErrorException(
            sprintf(ErrorException::CANNOT_UNSET_PROPERTY_STR, $iri),
            ErrorException::CANNOT_UNSET_PROPERTY,
            E_WARNING
        );
    }

    /**
     * Return the number of properties
     *
     * @return int Number of properties
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * Return the current property values
     *
     * @return array Property values
     */
    public function current()
    {
        return $this->values[$this->cursor];
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        ++$this->cursor;
    }

    /**
     * Return the current IRI key
     *
     * @return \stdClass IRI key
     */
    public function key()
    {
        return $this->names[$this->cursor];
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The current position is valid
     */
    public function valid()
    {
        return isset($this->values[$this->cursor]);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->cursor = 0;
    }

    /**
     * Add a property
     *
     * @param \stdClass|Iri $property Property
     */
    public function add($property)
    {
        $iri    = IriFactory::create($property);
        $values = (is_object($property) && isset($property->values)) ? (array)$property->values : [];

        // Create the property values list if necessary
        if (!$this->offsetExists($iri)) {
            $this->offsetSet($iri, $values);

            return;
        }

        $propertyValues =& $this->offsetGet($iri);
        $propertyValues = array_merge($propertyValues, $values);
    }

    /**
     * Return whether a property exists
     *
     * @param \stdClass|Iri|string $iri IRI
     *
     * @return boolean Property exists
     */
    public function offsetExists($iri)
    {
        $iri = IriFactory::create($iri);
        try {
            ($iri->profile !== '') ?
                $this->getProfiledPropertyCursor($iri) : $this->getPropertyCursor($iri->name);

            return true;
        } catch (OutOfBoundsException $exception) {
            return false;
        }
    }

    /**
     * Get a particular property cursor by its profiled name
     *
     * @param Iri $iri IRI
     *
     * @return int Property cursor
     * @throws OutOfBoundsException If the property name is unknown
     */
    protected function getProfiledPropertyCursor($iri)
    {
        $iriStr = strval($iri);

        // If the property name is unknown
        if (!isset($this->nameToCursor[$iriStr])) {
            $this->handleUnknownName($iriStr);
        }

        return $this->nameToCursor[$iriStr];
    }

    /**
     * Handle an unknown property name
     *
     * @param string $name Property name
     *
     * @throws OutOfBoundsException If the property name is unknown
     */
    protected function handleUnknownName($name)
    {
        throw new OutOfBoundsException(
            sprintf(OutOfBoundsException::UNKNOWN_PROPERTY_NAME_STR, $name),
            OutOfBoundsException::UNKNOWN_PROPERTY_NAME
        );
    }

    /**
     * Get a particular property cursor by its name
     *
     * @param string $name Property name
     *
     * @return int Property cursor
     */
    protected function getPropertyCursor($name)
    {
        // Run through all property names
        foreach ($this->names as $cursor => $iri) {
            if (in_array($name, [$iri->name, strval($iri)])) {
                return $cursor;
            }
        }

        return $this->handleUnknownName($name);
    }

    /**
     * Set a particular property
     *
     * @param \stdClass|Iri|string $iri IRI
     * @param array $value              Property values
     */
    public function offsetSet($iri, $value)
    {
        $iri                   = IriFactory::create($iri);
        $iriStr                = strval($iri);
        $cursor                = array_key_exists($iriStr, $this->nameToCursor) ?
            $this->nameToCursor[$iriStr] : ($this->nameToCursor[$iriStr] = count($this->nameToCursor));
        $this->names[$cursor]  = $iri;
        $this->values[$cursor] = $value;
    }

    /**
     * Get a particular property
     *
     * @param \stdClass|Iri|string $iri IRI
     *
     * @return array Property values
     * @throws OutOfBoundsException If the property name is unknown
     */
    public function &offsetGet($iri)
    {
        $iri    = IriFactory::create($iri);
        $cursor = ($iri->profile !== '') ?
            $this->getProfiledPropertyCursor($iri) : $this->getPropertyCursor($iri->name);

        return $this->values[$cursor];
    }
}
