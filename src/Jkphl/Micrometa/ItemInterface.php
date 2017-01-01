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

namespace Jkphl\Micrometa;

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

namespace Jkphl\Micrometa;

use Jkphl\Utility\Url;

/**
 * Micro information item interface
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
interface ItemInterface
{
    /**
     * Constructor
     *
     * @param \array $data Item data
     * @param string|Url $url Item base URL
     */
    public function __construct(array $data, Url $url);

    /**
     * Check if this item is of a specific type
     *
     * @param \string $type List of type names (arbitrary length)
     * @return \boolean                If this item is of a specific type
     */
    public function isOfType();

    /**
     * Return the first available property in a list of properties
     *
     * @param \string $property1 First property
     * @param \string $property2 Second property
     * ...
     * @return \mixed                Property value
     */
    public function firstOf();

    /**
     * Return a list of properties or a single property
     *
     * @param \string $key Property (list) name
     * @return \mixed                Property (list) value(s)
     */
    public function __get($key);

    /**
     * String serialization as JSON object
     *
     * @return \string                String serialization as JSON object
     */
    public function __toString();

    /**
     * Return a JSON representation of the embedded micro information
     *
     * @param \boolean $beautify Beautify the JSON output (available since PHP 5.4)
     * @return \string                    JSON representation
     */
    public function toJSON($beautify = false);

    /**
     * Return a vanilla object representation of this item
     *
     * @return \stdClass            Vanilla object item representation
     */
    public function toObject();

    /**
     * Return the parser name
     *
     * @return string Parser name
     */
    public function parser();
}
