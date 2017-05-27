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
use Jkphl\Micrometa\Infrastructure\Parser\JsonLD;
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
     * Test the JSON-LD parser
     */
    public function testJsonLDParser()
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
}
