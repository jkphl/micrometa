<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa\Application;

use Jkphl\Domfactory\Ports\Dom;
use Jkphl\Micrometa\Application\Contract\ParsingResultInterface;
use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Service\ExtractorService;
use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use Jkphl\Micrometa\AbstractTestBase;
use League\Uri\Http;

/**
 * Extractor tests
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ExtractorTest extends AbstractTestBase
{
    /**
     * Test the RDFa Lite 1.1 extraction
     */
    public function testRdfaLiteExtraction()
    {
        // Create a DOM with RDFa Lite 1.1 markup
        list($rdfaLiteUri, $rdfaLiteDom) = $this->getUriFixture('rdfa-lite/article-rdfa-lite.html');
        $this->assertInstanceOf(\DOMDocument::class, $rdfaLiteDom);

        // Create an RDFa Lite 1.1 parser
        $rdfaLiteParser = new RdfaLite($rdfaLiteUri, self::getLogger());
        $this->assertEquals($rdfaLiteUri, $rdfaLiteParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $rdfaLiteItems    = $extractorService->extract($rdfaLiteDom, $rdfaLiteParser);
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
        list($microdataUri, $microdataDom) = $this->getUriFixture('html-microdata/article-microdata.html');
        $this->assertInstanceOf(\DOMDocument::class, $microdataDom);

        // Create an HTML microdata parser
        $microdataParser = new Microdata($microdataUri, new ExceptionLogger());
        $this->assertEquals($microdataUri, $microdataParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $microdataItems   = $extractorService->extract($microdataDom, $microdataParser);
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
        $microformatsTests = \ComposerLocator::getPath('mf2/tests').DIRECTORY_SEPARATOR.'tests'.
            DIRECTORY_SEPARATOR.'microformats-v2'.DIRECTORY_SEPARATOR;

        $this->getAndTestMicroformatsExtractionBase(
            $microformatsTests.'h-product'.DIRECTORY_SEPARATOR.'aggregate.html'
        );
    }

    /**
     * Run a microformats base test on a file and return the items
     *
     * @param string $file File name
     *
     * @return ParsingResultInterface
     */
    protected function getAndTestMicroformatsExtractionBase($file)
    {
        // Create a DOM with Microformats markup
        $microformats    = file_get_contents($file);
        $microformatsDom = Dom::createFromString($microformats);
        $this->assertInstanceOf(\DOMDocument::class, $microformatsDom);

        // Create a Microformats 2 parser
        $microformatsUri    = Http::createFromString('http://localhost:1349/aggregate.html');
        $microformatsParser = new Microformats($microformatsUri, new ExceptionLogger());
        $this->assertEquals($microformatsUri, $microformatsParser->getUri());

        // Create and run an extractor service
        $extractorService  = new ExtractorService();
        $microformatsItems = $extractorService->extract($microformatsDom, $microformatsParser);
        $this->assertInstanceOf(ParsingResultInterface::class, $microformatsItems);
        $this->assertEquals(1, count($microformatsItems->getItems()));
        $this->assertInstanceOf(Item::class, $microformatsItems->getItems()[0]);
        $this->assertEquals(Microformats::FORMAT, $microformatsItems->getItems()[0]->getFormat());

        return $microformatsItems;
    }

    /**
     * Test the Microformats extraction
     */
    public function testNestedMicroformatsExtraction()
    {
        $microformatsItems = $this->getAndTestMicroformatsExtractionBase(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.
            'microformats'.DIRECTORY_SEPARATOR.'nested-events.html'
        );
        $this->assertEquals(2, count($microformatsItems->getItems()[0]->getChildren()));
    }
}
