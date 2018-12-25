<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure\Parser
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

namespace Jkphl\Micrometa\Infrastructure\Parser;

use Jkphl\Micrometa\Application\Contract\ParsingResultInterface;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Ports\Format;
use Mf2\Parser;

/**
 * Microformats parsere
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class Microformats extends AbstractParser
{
    /**
     * Format
     *
     * @var int
     */
    const FORMAT = Format::MICROFORMATS;

    /**
     * Parse a DOM document
     *
     * @param \DOMDocument $dom DOM Document
     *
     * @return ParsingResultInterface Micro information items
     * @throws \ReflectionException
     */
    public function parseDom(\DOMDocument $dom)
    {
        $this->logger->info('Running parser: '.(new \ReflectionClass(__CLASS__))->getShortName());
        $parser       = new Parser($dom, strval($this->uri));
        $parser->lang = true; // Enable experimental language parsing
        $microformats = $parser->parse();

        return new ParsingResult(
            self::FORMAT,
            isset($microformats['items']) ?
                MicroformatsFactory::createFromParserResult((array)$microformats['items']) : []
        );
    }
}
