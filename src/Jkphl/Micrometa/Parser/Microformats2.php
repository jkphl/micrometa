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

namespace Jkphl\Micrometa\Parser;

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

use Jkphl\Utility\Url;
use Jkphl\Micrometa\Parser\Microformats2\Exception;
use Jkphl\Micrometa\Parser\Microformats2\Item;

/**
 * Extended Microformats2 parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link https://github.com/indieweb/php-mf2
 */
class Microformats2 extends \Mf2\Parser
{
    /**
     * Original resource URL
     *
     * @var Url
     */
    protected $_url = null;
    /**
     * Parser name
     *
     * @var string
     */
    const NAME = 'mf2';
    /**
     * Microformats parse flag
     *
     * @var int
     */
    const PARSE = 1;

    /************************************************************************************************
     * PUBLIC METHODS
     ***********************************************************************************************/

    /**
     * Constructor
     *
     * @param \DOMDocument|string $source The data to parse. A string of HTML or a DOMDocument
     * @param Url|\string $url Optional: The URL of the parsed document, for relative URL resolution
     */
    public function __construct($source, $url = null)
    {
        $this->_url = ($url instanceof Url) ? $url : new Url($url);
        parent::__construct($source, strval($url));
    }

    /**
     * Decamelize a lower- or UpperCameCase microformats2 vocable (has no effect on regular vocables)
     *
     * @param \string $vocable Vocable
     * @param \string $separator Separation char / vocable
     * @return \string Decamelized vocable
     * @throws Exception If it's not a valid microformats2 vocable
     */
    public static function decamelize($vocable, $separator = '-')
    {
        if (!self::isValidVocable($vocable)) {
            throw new Exception(
                sprintf(Exception::INVALID_MICROFORMAT_VOCABLE_STR, $vocable),
                Exception::INVALID_MICROFORMAT_VOCABLE
            );
        }
        return strtolower(preg_replace("%[A-Z]%", "$separator$0", $vocable));
    }

    /************************************************************************************************
     * PRIVATE METHODS
     ***********************************************************************************************/

    /**
     * Check if a string is a valid microformats2 vocable (regular or camelCased)
     *
     * @param \string $str String
     * @return \boolean                                Whether it's a valid microformats2 vocable
     */
    public static function isValidVocable($str)
    {
        return preg_match("%^[a-z]+([A-Z][a-z]*)*$%", $str) || preg_match("%^[a-z]+(\-[a-z]+)*$%", $str);
    }

    /************************************************************************************************
     * STATIC METHODS
     ***********************************************************************************************/

    /**
     * Kicks off the parsing routine
     *
     * If `$convertClassic` is set, any angle brackets in the results from non e-* properties
     * will be HTML-encoded, bringing all output to the same level of encoding.
     *
     * If a DOMElement is set as the $context, only descendants of that element will
     * be parsed for microformats.
     *
     * @param bool $convertClassic Whether or not to html-encode non e-* properties. Defaults to false
     * @param \DOMElement $context Optional: An element from which to parse microformats
     * @return array                                An array containing all the µfs found in the current document
     */
    public function parse($convertClassic = true, \DOMElement $context = null)
    {
        $results = parent::parse($convertClassic, $context);
        $results['items'] = $this->_refineResults($results['items']);
        return $results;
    }

    /**
     * Refine micro information items
     *
     * @param \array $results Micro information items
     * @return \array                                Refined micro information items
     */
    protected function _refineResults(array $results)
    {
        $refined = array();

        // Run through all original parsing results
        foreach ($results as $data) {
            $refined[] = new Item($data, $this->_url);
        }

        return $refined;
    }
}
