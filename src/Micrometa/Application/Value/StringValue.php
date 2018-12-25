<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application\Value
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

namespace Jkphl\Micrometa\Application\Value;

use Jkphl\Micrometa\Application\Contract\ValueInterface;

/**
 * String value
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 */
class StringValue implements ValueInterface
{
    /**
     * String value
     *
     * @var string
     */
    protected $value;
    /**
     * Language
     *
     * @var string|null
     */
    protected $language;

    /**
     * String value constructor
     *
     * @param string $value         String value
     * @param string|null $language Language
     */
    public function __construct($value, $language = null)
    {
        $this->value    = $value;
        $this->language = $language;
    }

    /**
     * Return whether the value should be considered empty
     *
     * @return boolean Value is empty
     */
    public function isEmpty()
    {
        return !strlen(trim($this->value));
    }

    /**
     * String serialization
     *
     * @return string Value
     */
    public function __toString()
    {
        return strval($this->value);
    }

    /**
     * Export the object
     *
     * @return mixed
     */
    public function export()
    {
        return strval($this);
    }

    /**
     * Return the language of this value
     *
     * @return string Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
