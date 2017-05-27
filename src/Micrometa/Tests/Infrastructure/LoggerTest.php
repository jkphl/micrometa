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

namespace Jkphl\Micrometa\Tests\Infrastructure;

use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use Jkphl\Micrometa\Tests\AbstractTestBase;

/**
 * Logger tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class LoggerTest extends AbstractTestBase
{
    /**
     * Test the exception logger
     */
    public function testExceptionLogger()
    {
        $logger = new ExceptionLogger();
        $this->assertTrue($logger->debug('DEBUG'));
    }

    /**
     * Test the exception logger
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\RuntimeException
     * @expectedExceptionMessage CRITICAL
     * @expectedExceptionCode 500
     */
    public function testNoContextExceptionLogger()
    {
        $logger = new ExceptionLogger();
        $logger->critical('CRITICAL');
    }

    /**
     * Test the exception logger with a context
     *
     * @expectedException \ErrorException
     * @expectedExceptionMessage ERROR
     * @expectedExceptionCode 1234
     */
    public function testContextExceptionLogger()
    {
        $exception = new \ErrorException('ERROR', 1234);
        $logger = new ExceptionLogger();
        $logger->critical('CRITICAL', ['exception' => $exception]);
    }
}
