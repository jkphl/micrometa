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

namespace Jkphl\Micrometa\Tests\Ports;

use Jkphl\Micrometa\Infrastructure\Parser\JsonLD;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemObjectModelInterface;
use Jkphl\Micrometa\Ports\Parser;

/**
 * Parser tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Valid local test HTML document
     *
     * @var string
     */
    const VALID_HTML_URL = 'http://localhost:1349/valid-test.html';

    /**
     * Test the parser facade
     */
    public function testParser()
    {
        $microformatsHtml = \ComposerLocator::getPath('microformats/test').DIRECTORY_SEPARATOR.'tests'.
            DIRECTORY_SEPARATOR.'microformats-v2'.DIRECTORY_SEPARATOR.'h-product'.DIRECTORY_SEPARATOR.'aggregate.html';
        $formats = Microformats::FORMAT | Microdata::FORMAT | JsonLD::FORMAT | RdfaLite::FORMAT;
        $parser = new Parser(Microformats::FORMAT);
        $itemObjectModel = $parser(self::VALID_HTML_URL, file_get_contents($microformatsHtml), $formats);
        $this->assertInstanceOf(ItemObjectModelInterface::class, $itemObjectModel);
        $this->assertEquals(1, count($itemObjectModel->items()));
        $item = $itemObjectModel->item();
        $this->assertInstanceOf(Item::class, $item);
//        $this->assertTrue($item->isOfType('invalid', MicroformatsFactory::MF2_PROFILE_URI.'h-product'));
    }
}
