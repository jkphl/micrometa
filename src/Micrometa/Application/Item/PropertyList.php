<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application\Item
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

namespace Jkphl\Micrometa\Application\Item;

use Jkphl\Micrometa\Application\Contract\ExportableInterface;
use Jkphl\Micrometa\Application\Factory\AliasFactoryInterface;
use Jkphl\Micrometa\Domain\Factory\IriFactory;

/**
 * Property list
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 */
class PropertyList extends \Jkphl\Micrometa\Domain\Item\PropertyList implements PropertyListInterface
{
    /**
     * Property name aliases
     *
     * @var array[]
     */
    protected $aliases = [];
    /**
     * Alias factory
     *
     * @var AliasFactoryInterface
     */
    protected $aliasFactory;

    /**
     * Property list constructor
     *
     * @param AliasFactoryInterface $aliasFactory Alias factory
     */
    public function __construct(AliasFactoryInterface $aliasFactory)
    {
        $this->aliasFactory = $aliasFactory;
    }

    /**
     * Set a particular property
     *
     * @param \stdClass|string $iri IRI
     * @param array $value          Property values
     */
    public function offsetSet($iri, $value)
    {
        $iri                    = IriFactory::create($iri);
        $iriStr                 = strval($iri);
        $cursor                 = array_key_exists($iriStr,
            $this->nameToCursor) ? $this->nameToCursor[$iriStr] : count($this->values);
        $this->aliases[$iriStr] = [];

        // Run through all name aliases
        foreach ($this->aliasFactory->createAliases($iri->name) as $alias) {
            $this->aliases[$iriStr][]                 = $alias;
            $this->nameToCursor[$iri->profile.$alias] = $cursor;
        }

        $this->names[$cursor]  = $iri;
        $this->values[$cursor] = $value;
    }

    /**
     * Export the object
     *
     * @return mixed
     */
    public function export()
    {
        $propertyList = [];
        foreach ($this->names as $iri) {
            $profiledName                = strval($iri);
            $cursor                      = $this->nameToCursor[$profiledName];
            $propertyList[$profiledName] = array_map(
                function (ExportableInterface $value) {
                    return $value->export();
                },
                $this->values[$cursor]
            );
        }

        return $propertyList;
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
            foreach ($this->aliases[strval($iri)] as $alias) {
                if ($name === $alias) {
                    return $cursor;
                }
            }
        }

        return $this->handleUnknownName($name);
    }
}
