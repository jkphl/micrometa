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

namespace Jkphl\Micrometa\Ports\Exceptions;

/**
 * Out of bounds exception
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class OutOfBoundsException extends \OutOfBoundsException implements MicrometaExceptionInterface
{
    /**
     * Invalid link type index
     *
     * @var string
     */
    const INVALID_LINK_TYPE_INDEX_STR = 'Index "%s" for link type "%s" is out of bounds';
    /**
     * Invalid link type index
     *
     * @var int
     */
    const INVALID_LINK_TYPE_INDEX = 1489268571;
    /**
     * Invalid property value index
     *
     * @var string
     */
    const INVALID_PROPERTY_VALUE_INDEX_STR = 'Invalid property index "%s"';
    /**
     * Invalid property value index
     *
     * @var int
     */
    const INVALID_PROPERTY_VALUE_INDEX = 1491672553;
    /**
     * No matching properties
     *
     * @var string
     */
    const NO_MATCHING_PROPERTIES_STR = 'No matching properties';
    /**
     * No matching properties
     *
     * @var int
     */
    const NO_MATCHING_PROPERTIES = 1492022860;
    /**
     * No matching items
     *
     * @var string
     */
    const NO_MATCHING_ITEMS_STR = 'No matching items';
    /**
     * No matching items
     *
     * @var int
     */
    const NO_MATCHING_ITEMS = 1492030227;
    /**
     * Invalid item index
     *
     * @var string
     */
    const INVALID_ITEM_INDEX_STR = 'Item index "%s" is out of bounds';
    /**
     * Invalid item index
     *
     * @var int
     */
    const INVALID_ITEM_INDEX = 1492418999;
}
