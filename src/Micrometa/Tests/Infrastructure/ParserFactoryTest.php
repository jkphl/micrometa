<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests\Domain
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

namespace Jkphl\Micrometa\Tests\Infrastructure;

use Jkphl\Micrometa\Infrastructure\Factory\ParserFactory;
use Jkphl\Micrometa\Infrastructure\Parser\AbstractParser;
use Jkphl\Micrometa\Infrastructure\Parser\JsonLD;
use Jkphl\Micrometa\Infrastructure\Parser\LinkType;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use Jkphl\Micrometa\Tests\AbstractTestBase;
use League\Uri\Http;

/**
 * Parser factory tests
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ParserFactoryTest extends AbstractTestBase
{
    /**
     * Test the parser factory
     */
    public function testParserFactory()
    {
        $formats = Microformats::FORMAT | Microdata::FORMAT | JsonLD::FORMAT | RdfaLite::FORMAT | LinkType::FORMAT;
        $uri     = 'http://localhost/example.html';
        $parsers = ParserFactory::createParsersFromFormats($formats, Http::createFromString($uri), self::getLogger());

        /**
         * @var int $parserFormat
         * @var AbstractParser $parser
         */
        foreach ($parsers as $parserFormat => $parser) {
            $this->assertInstanceOf(ParserFactory::$parsers[$parserFormat], $parser);
            $this->assertEquals($uri, $parser->getUri());
            $formats &= ~$parserFormat;
        }

        $this->assertEquals(0, $formats);
    }
}
