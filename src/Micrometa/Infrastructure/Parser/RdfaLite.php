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
use Jkphl\Micrometa\Ports\Format;
use Jkphl\RdfaLiteMicrodata\Ports\Parser\RdfaLite as RdfaLiteParser;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * Rdfa Lite 1.1 parser
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class RdfaLite extends AbstractParser
{
    /**
     * Format
     *
     * @var int
     */
    const FORMAT = Format::RDFA_LITE;
    /**
     * Parser
     *
     * @var RdfaLiteParser
     */
    protected $parser;

    /**
     * RDFa Lite 1.1 parser constructor
     *
     * @param UriInterface $uri       Base URI
     * @param LoggerInterface $logger Logger
     */
    public function __construct(UriInterface $uri, LoggerInterface $logger)
    {
        parent::__construct($uri, $logger);
        $this->parser = new RdfaLiteParser(true);
    }

    /**
     * Parse a DOM document
     *
     * @param \DOMDocument $dom DOM Document
     *
     * @return ParsingResultInterface Micro information items
     */
    public function parseDom(\DOMDocument $dom)
    {
        $this->logger->info('Running parser: '.(new \ReflectionClass(__CLASS__))->getShortName());
        $rdfaItems = $this->parser->parseDom($dom);

        return new ParsingResult(self::FORMAT, $rdfaItems->items);
    }
}
