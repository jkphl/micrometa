<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests\Domain
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

namespace Jkphl\Micrometa\Tests\Infrastructure;

use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Item\Iri;
use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use Jkphl\Micrometa\Infrastructure\Parser\JsonLD;
use Jkphl\Micrometa\Infrastructure\Parser\LinkType;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use Jkphl\Micrometa\Tests\AbstractTestBase;

/**
 * Parser tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ParserTest extends AbstractTestBase
{
    /**
     * Test the JSON-LD parser with multiple languages
     */
    public function testLanguageJsonLDParser()
    {
        list($uri, $dom) = $this->getUriFixture('json-ld/jsonld-languages.html');
        $parser = new JsonLD($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(JsonLD::FORMAT, $items[0]->getFormat());
        $this->assertEquals('http://example.com/id1', $items[0]->getId());

        /** @var StringValue[] $propertyValues */
        $propertyValues = $items[0]->getProperty('http://example.com/term6');
        $this->assertTrue(is_array($propertyValues));
        foreach ([null, null, 'en', 'de'] as $index => $language) {
            $this->assertInstanceOf(StringValue::class, $propertyValues[$index]);
            $this->assertEquals(strval($index + 1), strval($propertyValues[$index]));
            $this->assertEquals($language, $propertyValues[$index]->getLanguage());
        }
    }

    /**
     * Test the JSON-LD parser with multiple documents and file cache
     */
    public function testMultipleJsonLDParser()
    {
        list($uri, $dom) = $this->getUriFixture('json-ld/jsonld-examples.html');
        $parser = new JsonLD($uri, new ExceptionLogger(0));
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(4, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(JsonLD::FORMAT, $items[0]->getFormat());
        $this->assertEquals('https://jsonld-examples.com/#header_website', $items[0]->getId());
    }

    /**
     * Test the JSON-LD parser with an invalid document
     */
    public function testInvalidJsonLDParser()
    {
        list($uri, $dom) = $this->getUriFixture('json-ld/jsonld-invalid.html');
        $parser = new JsonLD($uri, new ExceptionLogger(0));
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(0, count($items));
    }

    /**
     * Test the Microformats parser
     */
    public function testMicroformatsParser()
    {
        list($uri, $dom) = $this->getUriFixture('microformats/entry.html');
        $parser = new Microformats($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(Microformats::FORMAT, $items[0]->getFormat());
        $this->assertEquals('en', $items[0]->getLanguage());
    }

    /**
     * Test the Microformats parser with nested items
     */
    public function testNestedMicroformatsParser()
    {
        list($uri, $dom) = $this->getUriFixture('microformats/nested-events.html');
        $parser = new Microformats($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(Microformats::FORMAT, $items[0]->getFormat());
        $this->assertEquals(2, count($items[0]->getChildren()));
    }

    /**
     * Test the HTML Microdata parser
     */
    public function testMicrodataParser()
    {
        list($uri, $dom) = $this->getUriFixture('html-microdata/article-microdata.html');
        $parser = new Microdata($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(Microdata::FORMAT, $items[0]->getFormat());
        $this->assertEquals([new Iri('http://schema.org/', 'NewsArticle')], $items[0]->getType());
    }

    /**
     * Test the RDFa Lite 1.1 parser
     */
    public function testRdfaLiteParser()
    {
        list($uri, $dom) = $this->getUriFixture('rdfa-lite/article-rdfa-lite.html');
        $parser = new RdfaLite($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(RdfaLite::FORMAT, $items[0]->getFormat());
        $this->assertEquals([new Iri('http://schema.org/', 'NewsArticle')], $items[0]->getType());
    }

    /**
     * Test the LinkType parser
     */
    public function testLinkTypeParser()
    {
        list($uri, $dom) = $this->getUriFixture('link-type/valid-test.html');
        $parser = new LinkType($uri, self::$logger);
        $items = $parser->parseDom($dom)->getItems();
        $this->assertTrue(is_array($items));
        $this->assertEquals(4, count($items));
        $this->assertInstanceOf(Item::class, $items[0]);
        $this->assertEquals(LinkType::FORMAT, $items[0]->getFormat());
        $this->assertEquals([new Iri(LinkType::HTML_PROFILE_URI, 'icon')], $items[0]->getType());
    }
}
