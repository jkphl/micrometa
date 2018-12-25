<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports\Exceptions
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
 * Invalid argument Exception
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class InvalidArgumentException extends \InvalidArgumentException implements MicrometaExceptionInterface
{
    /**
     * Invalid data source
     *
     * @var string
     */
    const INVALID_DATA_SOURCE_STR = 'Invalid data source (%s)';
    /**
     * Invalid data source
     *
     * @var int
     */
    const INVALID_DATA_SOURCE = 1488228437;
    /**
     * Invalid type / property name
     *
     * @var string
     */
    const INVALID_TYPE_PROPERTY_NAME_STR = 'Invalid type / property name';
    /**
     * Invalid type / property name
     *
     * @var int
     */
    const INVALID_TYPE_PROPERTY_NAME = 1489528854;
    /**
     * Invalid profiled type / property array definition
     *
     * @var string
     */
    const INVALID_TYPE_PROPERTY_ARRAY_STR = 'Invalid profiled type / property array definition';
    /**
     * Invalid profiled type / property array definition
     *
     * @var int
     */
    const INVALID_TYPE_PROPERTY_ARRAY = 1491063221;
    /**
     * Invalid item index
     *
     * @var string
     */
    const INVALID_ITEM_INDEX_STR = 'Invalid item index "%s"';
    /**
     * Invalid item index
     *
     * @var int
     */
    const INVALID_ITEM_INDEX = 1492418709;
}
