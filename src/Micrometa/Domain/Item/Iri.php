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

use Jkphl\Micrometa\Domain\Exceptions\ErrorException;
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException;

/**
 * IRI
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 * @see        https://tools.ietf.org/html/rfc3987
 * @property string $profile Profile
 * @property string $name    Name
 */
class Iri
{
    /**
     * Profile
     *
     * @var string
     */
    protected $immutableProfile;
    /**
     * Name
     *
     * @var string
     */
    protected $immutableName;

    /**
     * Constructor
     *
     * @param string $profile Profile
     * @param string $name    Name
     */
    public function __construct($profile, $name)
    {
        $this->immutableProfile = strval($profile);
        $this->immutableName    = strval($name);
    }

    /**
     * Property getter
     *
     * @param string $name Property name
     *
     * @return string Property value
     * @throws OutOfBoundsException If the requested IRI property is unknown
     */
    public function __get($name)
    {
        if ($name == 'profile') {
            return $this->immutableProfile;
        }
        if ($name == 'name') {
            return $this->immutableName;
        }

        throw new OutOfBoundsException(
            sprintf(OutOfBoundsException::UNKNOWN_IRI_PROPERTY_NAME_STR, $name),
            OutOfBoundsException::UNKNOWN_IRI_PROPERTY_NAME
        );
    }

    /**
     * Property setter
     *
     * @param string $name Property name
     * @param mixed $value Property value
     *
     * @throws ErrorException If a property should be set
     */
    public function __set($name, $value)
    {
        throw new ErrorException(ErrorException::IMMUTABLE_IRI_STR, ErrorException::IMMUTABLE_IRI);
    }

    /**
     * Check the existence of a property
     *
     * @param string $name Property name
     *
     * @return bool Property exists
     */
    public function __isset($name)
    {
        return ($name == 'profile') || ($name == 'name');
    }

    /**
     * Serialize the IRI
     *
     * @return string Serialized IRI
     */
    public function __toString()
    {
        return $this->profile.$this->name;
    }
}
