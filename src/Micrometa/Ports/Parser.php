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

namespace Jkphl\Micrometa\Ports;

use Jkphl\Micrometa\Application\Service\ExtractorService;
use Jkphl\Micrometa\Infrastructure\Factory\DocumentFactory;
use Jkphl\Micrometa\Infrastructure\Factory\ItemFactory;
use Jkphl\Micrometa\Infrastructure\Factory\ParserFactory;
use Jkphl\Micrometa\Ports\Item\ItemObjectModel;
use Jkphl\Micrometa\Ports\Item\ItemObjectModelInterface;
use League\Uri\Schemes\Http;

/**
 * Parser
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class Parser
{
    /**
     * Micro information formats
     *
     * @var int
     */
    protected $formats;

    /**
     * Parser constructor
     *
     * @param int $formats Micro information formats to extract
     * @api
     */
    public function __construct($formats = null)
    {
        $this->formats = $formats;
    }

    /**
     * Extract micro information items out of a URI or piece of source
     *
     * @param string $uri URI
     * @param string $source Source code
     * @param int $formats Micro information formats to extract
     * @return ItemObjectModelInterface Item object model
     */
    public function __invoke($uri, $source = null, $formats = null)
    {
        // If source code has been passed in
        $dom = (($source !== null) && strlen(trim($source))) ?
            DocumentFactory::createFromString($source) : DocumentFactory::createFromUri($uri);

        // Run through all format parsers
        $items = [];
        $extractor = new ExtractorService();
        foreach (ParserFactory::createParsersFromFormats(
            intval($formats ?: $this->formats),
            Http::createFromString($uri)
        ) as $parser) {
            $results = $extractor->extract($dom, $parser);
            $items += ItemFactory::createFromApplicationItems($results->getItems());
        }

        return new ItemObjectModel($items);
    }
}
