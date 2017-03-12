<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
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

namespace Jkphl\Micrometa\Ports\Exceptions;

/**
 * Out of bounds exception
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class OutOfBoundsException extends \OutOfBoundsException implements MicrometaExceptionInterface
{
    /**
     * Invalid rel tyle
     *
     * @var string
     */
    const INVALID_REL_TYPE_STR = 'Rel type "%s" is out of bounds';
    /**
     * Invalid rel type
     *
     * @var int
     */
    const INVALID_REL_TYPE = 1489269267;
    /**
     * Invalid rel type index
     *
     * @var string
     */
    const INVALID_REL_INDEX_STR = 'Index "%s" for rel type "%s" is out of bounds';
    /**
     * Invalid rel type index
     *
     * @var int
     */
    const INVALID_REL_INDEX = 1489268571;
    /**
     * Invalid alternate type
     *
     * @var string
     */
    const INVALID_ALTERNATE_TYPE_STR = 'Alternate type "%s" is out of bounds';
    /**
     * Invalid alternate type
     *
     * @var int
     */
    const INVALID_ALTERNATE_TYPE = 1489268753;
}
