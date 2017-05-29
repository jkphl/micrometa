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

namespace Jkphl\Micrometa\Tests\Domain;

use Jkphl\Micrometa\Domain\Item\Iri;

/**
 * IRI tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class IriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test IRIs
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1495895152
     */
    public function testIri()
    {
        $profile = md5(rand());
        $name = md5(rand());
        $iri = new Iri($profile, $name);
        $this->assertInstanceOf(Iri::class, $iri);
        $this->assertTrue(isset($iri->profile));
        $this->assertTrue(isset($iri->name));
        $this->assertFalse(isset($iri->invalid));
        $this->assertEquals($profile, $iri->profile);
        $this->assertEquals($name, $iri->name);
        $this->assertEquals($profile.$name, strval($iri));
        $iri->invalid;
    }

    /**
     * Test IRI immutability
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\ErrorException
     * @expectedExceptionCode 1495895278
     */
    public function testIriImmutability() {
        $iri = new Iri('', '');
        $iri->profile = 'abc';
    }
}
