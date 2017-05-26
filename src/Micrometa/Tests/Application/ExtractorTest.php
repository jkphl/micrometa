<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa\Tests\Application;

use Jkphl\Domfactory\Ports\Dom;
use Jkphl\Micrometa\Application\Contract\ParsingResultInterface;
use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Service\ExtractorService;
use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use League\Uri\Schemes\Http;

/**
 * Extractor tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * RDFa Lite 1.1 HTML document
     *
     * @var string
     */
    const RDFA_LITE_HTML_URL = 'http://localhost:1349/article-rdfa-lite.html';
    /**
     * Microdata HTML document
     *
     * @var string
     */
    const MICRODATA_HTML_URL = 'http://localhost:1349/article-microdata.html';
    /**
     * Microformats HTML document
     *
     * @var string
     */
    const MICROFORMATS_HTML_URL = 'http://localhost:1349/aggregate.html';
    /**
     * Microformats tests root path
     *
     * @var string
     */
    protected static $microformatsTests;

    /**
     * Setup before all tests
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$microformatsTests = \ComposerLocator::getPath('microformats/test').DIRECTORY_SEPARATOR.'tests'.
            DIRECTORY_SEPARATOR.'microformats-v2'.DIRECTORY_SEPARATOR;
    }

    /**
     * Test the RDFa Lite 1.1 extraction
     */
    public function testRdfaLiteExtraction()
    {
        // Create a DOM with RDFa Lite 1.1 markup
        $rdfaLite = file_get_contents(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'article-rdfa-lite.html'
        );
        $rdfaLiteDom = Dom::createFromString($rdfaLite);
        $this->assertInstanceOf(\DOMDocument::class, $rdfaLiteDom);

        // Create an RDFa Lite 1.1 parser
        $rdfaLiteUri = Http::createFromString(self::RDFA_LITE_HTML_URL);
        $rdfaLiteParser = new RdfaLite($rdfaLiteUri, new ExceptionLogger());
        $this->assertEquals($rdfaLiteUri, $rdfaLiteParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $rdfaLiteItems = $extractorService->extract($rdfaLiteDom, $rdfaLiteParser);
        $this->assertInstanceOf(ParsingResultInterface::class, $rdfaLiteItems);
        $this->assertEquals(1, count($rdfaLiteItems->getItems()));
        $this->assertInstanceOf(Item::class, $rdfaLiteItems->getItems()[0]);
        $this->assertEquals(RdfaLite::FORMAT, $rdfaLiteItems->getItems()[0]->getFormat());
    }

    /**
     * Test the HTML Microdata extraction
     */
    public function testMicrodataExtraction()
    {
        // Create a DOM with HTML Microdata markup
        $microdata = file_get_contents(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'article-microdata.html'
        );
        $microdataDom = Dom::createFromString($microdata);
        $this->assertInstanceOf(\DOMDocument::class, $microdataDom);

        // Create an RDFa Lite 1.1 parser
        $microdataUri = Http::createFromString(self::MICRODATA_HTML_URL);
        $microdataParser = new Microdata($microdataUri, new ExceptionLogger());
        $this->assertEquals($microdataUri, $microdataParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $microdataItems = $extractorService->extract($microdataDom, $microdataParser);
        $this->assertInstanceOf(ParsingResultInterface::class, $microdataItems);
        $this->assertEquals(1, count($microdataItems->getItems()));
        $this->assertInstanceOf(Item::class, $microdataItems->getItems()[0]);
        $this->assertEquals(microdata::FORMAT, $microdataItems->getItems()[0]->getFormat());
    }

    /**
     * Test the Microformats extraction
     */
    public function testMicroformatsExtraction()
    {
        // Create a DOM with Microformats markup
        $microformats = file_get_contents(
            self::$microformatsTests.'h-product'.DIRECTORY_SEPARATOR.'aggregate.html'
        );
        $microformatsDom = Dom::createFromString($microformats);
        $this->assertInstanceOf(\DOMDocument::class, $microformatsDom);

        // Create a Microformats 2 parser
        $microformatsUri = Http::createFromString(self::MICROFORMATS_HTML_URL);
        $microformatsParser = new Microformats($microformatsUri, new ExceptionLogger());
        $this->assertEquals($microformatsUri, $microformatsParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $microformatsItems = $extractorService->extract($microformatsDom, $microformatsParser);
        $this->assertInstanceOf(ParsingResultInterface::class, $microformatsItems);
        $this->assertEquals(1, count($microformatsItems->getItems()));
        $this->assertInstanceOf(Item::class, $microformatsItems->getItems()[0]);
        $this->assertEquals(Microformats::FORMAT, $microformatsItems->getItems()[0]->getFormat());
    }

    /**
     * Test the Microformats extraction
     */
    public function testNestedMicroformatsExtraction()
    {
        // Create a DOM with Microformats markup
        $microformats = file_get_contents(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'nested-events.html'
        );
        $microformatsDom = Dom::createFromString($microformats);
        $this->assertInstanceOf(\DOMDocument::class, $microformatsDom);

        // Create a Microformats 2 parser
        $microformatsUri = Http::createFromString(self::MICROFORMATS_HTML_URL);
        $microformatsParser = new Microformats($microformatsUri, new ExceptionLogger());
        $this->assertEquals($microformatsUri, $microformatsParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $microformatsItems = $extractorService->extract($microformatsDom, $microformatsParser);
        $this->assertInstanceOf(ParsingResultInterface::class, $microformatsItems);
        $this->assertEquals(1, count($microformatsItems->getItems()));
        $this->assertInstanceOf(Item::class, $microformatsItems->getItems()[0]);
        $this->assertEquals(Microformats::FORMAT, $microformatsItems->getItems()[0]->getFormat());
        $this->assertEquals(2, count($microformatsItems->getItems()[0]->getChildren()));
    }
}
