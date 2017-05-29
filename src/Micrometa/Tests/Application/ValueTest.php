<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
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

namespace Jkphl\Micrometa\Tests\Application;

use Jkphl\Micrometa\Application\Value\AlternateValues;
use Jkphl\Micrometa\Application\Value\StringValue;

/**
 * Value tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the string value
     */
    public function testStringValue()
    {
        $string = md5(rand());
        $stringValue = new StringValue($string, 'en');
        $this->assertInstanceOf(StringValue::class, $stringValue);
        $this->assertFalse($stringValue->isEmpty());
        $this->assertEquals($string, strval($stringValue));
        $this->assertEquals($string, $stringValue->export());
        $this->assertEquals('en', $stringValue->getLanguage());
    }

    /**
     * Test the alternate value
     */
    public function testAlternateValue()
    {
        $alternate1 = md5(rand());
        $alternate2 = md5(rand());
        $alternates = ['one' => $alternate1, 'two' => $alternate2];
        $keys = ['one', 'two'];
        $alternateValue = new AlternateValues($alternates);
        $this->assertInstanceOf(AlternateValues::class, $alternateValue);
        $this->assertFalse($alternateValue->isEmpty());
        foreach ($alternateValue as $index => $value) {
            $this->assertEquals($index, array_shift($keys));
            $this->assertEquals($alternates[$index], $value);
        }
        $this->assertEquals($alternates, $alternateValue->export());
        $this->assertEquals($alternate1, $alternates['one']);
        $this->assertEquals($alternate2, $alternates['two']);
    }
}
