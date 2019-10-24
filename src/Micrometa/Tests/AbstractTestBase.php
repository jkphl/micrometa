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

namespace Jkphl\Micrometa\Tests;

use Jkphl\Domfactory\Ports\Dom;
use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Abstract test base
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
abstract class AbstractTestBase extends TestCase
{
    /**
     * Fixture base path
     *
     * @var string
     */
    protected static $fixture =  __DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private static $logger;

    protected static function getLogger(int $threshold = 400) : LoggerInterface
    {
        return self::$logger[$threshold] ?? self::$logger[$threshold] = new ExceptionLogger($threshold);
    }

    /**
     * Read and return a particular fixture file
     *
     * @param string $file File name
     *
     * @return array URI and DOM document
     */
    protected function getUriFixture($file)
    {
        return [
            Http::createFromString('http://localhost:1349/'.$file),
            Dom::createFromString($this->getFixture($file))
        ];
    }

    /**
     * Return the contents of a fixture file
     *
     * @param string $file File name relative to fixtures directory
     *
     * @return string Fixture content
     */
    protected function getFixture($file)
    {
        $file = strtr($file, ['/' => DIRECTORY_SEPARATOR]);
        if (!file_exists($file)) {
            $file = self::$fixture.$file;
        }

        return strval(file_get_contents($file));
    }
}
