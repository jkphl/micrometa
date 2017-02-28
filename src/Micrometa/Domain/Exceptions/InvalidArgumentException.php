<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain\Exceptions
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

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

namespace Jkphl\Micrometa\Domain\Exceptions;

/**
 * Invalid argument exception
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
class InvalidArgumentException extends \InvalidArgumentException implements MicrometaExceptionInterface
{
    /**
     * An empty type list is not allowed
     *
     * @var string
     */
    const EMPTY_TYPES_STR = 'Empty type list is not allowed';
    /**
     * An empty type list is not allowed
     *
     * @var int
     */
    const EMPTY_TYPES = 1488314667;
    /**
     * An empty property name is not allowed
     *
     * @var string
     */
    const EMPTY_PROPERTY_NAME_STR = 'Empty property name is not allowed';
    /**
     * An empty property name is not allowed
     *
     * @var int
     */
    const EMPTY_PROPERTY_NAME = 1488314921;
    /**
     * Invalid property value
     *
     * @var string
     */
    const INVALID_PROPERTY_VALUE_STR = 'Invalid property value of type "%s"';
    /**
     * Invalid property value
     *
     * @var int
     */
    const INVALID_PROPERTY_VALUE = 1488315339;
}
