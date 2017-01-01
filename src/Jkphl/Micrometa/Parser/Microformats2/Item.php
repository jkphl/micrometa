<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Jkphl\Micrometa\Parser\Microformats2;

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

use Jkphl\Micrometa\Parser\Microformats2;
use Jkphl\Micrometa\Parser\Microformats2\Exception;

/**
 * Extended microformats2 item
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link https://github.com/indieweb/php-mf2
 */
class Item extends \Jkphl\Micrometa\Item
{
    /**
     * Yielding parser
     *
     * @var string
     */
    const PARSER = Microformats2::NAME;

    /**
     * Generic caller
     *
     * Returns all nested microformats2 items of a spefic type (or a particular index out of this list)
     *
     * @param \string $method Method name (lowerCamelCased microformats2 item type)
     * @param \array $arguments List of arguments, of which the first is interpreted as list index (NULL = return the complete list)
     * @return \array|\Jkphl\Micrometa\Parser\Microformats2\Item    List of microformats2 items or a single microformats2 item
     * @throws \Jkphl\Micrometa\Parser\Microformats2\Exception        If it's not a valid microformats2 vocable
     * @throws \Jkphl\Micrometa\Parser\Microformats2\Exception        If the item index is out of range
     */
    public function __call($method, array $arguments)
    {
        $method = Microformats2::decamelize($method);

        // If a property of this name exists
        if (isset($this->_properties->$method)) {
            $property =& $this->_properties->$method;
            $itemIndex = count($arguments) ? intval($arguments[0]) : null;

            // If the whole property value list should be returned
            if ($itemIndex === null) {
                return $property;

                // Else: If the requested property value index is out of range: Error
            } elseif (($itemIndex < 0) || ($itemIndex > count($property) - 1)) {
                throw new Exception(
                    sprintf(Exception::INDEX_OUT_OF_RANGE_STR, $itemIndex),
                    Exception::INDEX_OUT_OF_RANGE
                );

                // Else: Return the requested property value index
            } else {
                return $property[$itemIndex];
            }

            // Else: Unknown property
        } else {
            return null;
        }
    }

    /**
     * Return a list of properties or a single property
     *
     * @param \string $key Property (list) name
     * @return \mixed                Property (list) value(s)
     */
    public function __get($key)
    {
        $property = Microformats2::decamelize($key);

        // If a single property value was requested
        if (isset($this->_properties->$property)) {
            return $this->$key(0);

            // Else: If a property value list was requested
        } elseif ((substr($property, -1) == 's') && isset($this->_properties->{substr($property, 0, -1)})) {
            return $this->$key(null);

            // Else: Unknown property
        } else {
            return null;
        }
    }
}
