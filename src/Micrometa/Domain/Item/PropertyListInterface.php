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

/**
 * Property list interface
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
interface PropertyListInterface extends \ArrayAccess, \Iterator, \Countable
{
    /**
     * Return whether a property exists
     *
     * @param \stdClass|string $iri IRI
     *
     * @return boolean Property exists
     */
    public function offsetExists($iri);

    /**
     * Get a particular property
     *
     * @param \stdClass|string $iri IRI
     *
     * @return array Property values
     */
    public function &offsetGet($iri);

    /**
     * Set a particular property
     *
     * @param \stdClass|string $iri IRI
     * @param array $value          Property values
     */
    public function offsetSet($iri, $value);

    /**
     * Unset a property
     *
     * @param \stdClass|string $iri IRI
     */
    public function offsetUnset($iri);

    /**
     * Add a property
     *
     * @param \stdClass $property Property
     */
    public function add($property);
}
