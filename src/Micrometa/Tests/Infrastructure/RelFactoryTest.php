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

use Jkphl\Micrometa\Infrastructure\Factory\RelFactory;
use Jkphl\Micrometa\Ports\Rel\Rel;

/**
 * Rel factory tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class RelFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the rel factory
     */
    public function testRelFactory()
    {
        $rels = RelFactory::createFromParserResult(
            [
                'me' => ['https://twitter.com/example', 'https://github.com/example'],
                'webmention' => ['https://example.com/webmention'],
            ]
        );
        $this->assertTrue(is_array($rels));
        $this->assertEquals(2, count($rels));
        $this->assertEquals(['me', 'webmention'], array_keys($rels));
        $this->assertTrue(is_array($rels['me']));
        $this->assertEquals(2, count($rels['me']));
        $this->assertInstanceOf(Rel::class, $rels['me'][0]);
        $this->assertInstanceOf(Rel::class, $rels['me'][1]);
        $this->assertTrue(is_array($rels['webmention']));
        $this->assertEquals(1, count($rels['webmention']));
        $this->assertInstanceOf(Rel::class, $rels['webmention'][0]);
    }
}
