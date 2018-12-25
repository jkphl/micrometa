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

namespace Jkphl\Micrometa\Tests\Ports;

use Jkphl\Micrometa\Ports\Format;
use Jkphl\Micrometa\Ports\Item\ItemObjectModelInterface;
use Jkphl\Micrometa\Ports\Parser;
use Jkphl\Micrometa\Tests\AbstractTestBase;

/**
 * Parser tests
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ParserTest extends AbstractTestBase
{
    /**
     * Test the LinkType parser
     */
    public function testLinkTypeParser()
    {
        $parser          = new Parser(Format::LINK_TYPE);
        $itemObjectModel = $parser('http://localhost:1349/link-type/valid-test.html');
        $this->assertInstanceOf(ItemObjectModelInterface::class, $itemObjectModel);
        $this->assertEquals(4, count($itemObjectModel->getItems()));
    }

    /**
     * Test the JSON-LD parser with an invalid JSON-LD document
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\RuntimeException
     * @expectedExceptionCode 400
     */
    public function testJsonLDParser()
    {
        $parser          = new Parser(Format::JSON_LD);
        $itemObjectModel = $parser('http://localhost:1349/json-ld/jsonld-invalid.html');
        $this->assertInstanceOf(ItemObjectModelInterface::class, $itemObjectModel);
        $this->assertEquals(1, count($itemObjectModel->getItems()));
    }
}
