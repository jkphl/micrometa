<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
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

namespace Jkphl\Micrometa\Domain\Factory;

use Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Domain\Item\Iri;

/**
 * IRI factory
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
class IriFactory
{
    /**
     * Validate and sanitize an IRI
     *
     * @param Iri|\stdClass|string $iri IRI
     *
     * @return Iri Sanitized IRI
     * @throws InvalidArgumentException If the IRI is invalid
     */
    public static function create($iri)
    {
        // Cast as item type object if only a string is given
        if (is_string($iri)) {
            $iri = (object)['profile' => '', 'name' => $iri];
        }

        // If the IRI is invalid
        if (!self::validateIriStructure($iri)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_IRI_STR,
                InvalidArgumentException::INVALID_IRI
            );
        }

        return new Iri($iri->profile, $iri->name);
    }

    /**
     * Test if an object has a valid IRI structure
     *
     * @param \stdClass $iri IRI
     *
     * @return bool Is a valid IRI
     */
    protected static function validateIriStructure($iri)
    {
        return is_object($iri) && isset($iri->profile) && isset($iri->name);
    }
}
