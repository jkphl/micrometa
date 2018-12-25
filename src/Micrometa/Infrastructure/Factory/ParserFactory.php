<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure\Factory
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

namespace Jkphl\Micrometa\Infrastructure\Factory;

use Jkphl\Micrometa\Infrastructure\Parser\JsonLD;
use Jkphl\Micrometa\Infrastructure\Parser\LinkType;
use Jkphl\Micrometa\Infrastructure\Parser\Microdata;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * Parser factory
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class ParserFactory
{
    /**
     * Format parsers
     *
     * @var array
     */
    public static $parsers = [
        Microformats::FORMAT => Microformats::class,
        Microdata::FORMAT    => Microdata::class,
        JsonLD::FORMAT       => JsonLD::class,
        RdfaLite::FORMAT     => RdfaLite::class,
        LinkType::FORMAT     => LinkType::class,
    ];

    /**
     * Create a list of parsers from a formats bitmask
     *
     * @param int $formats            Parser format bitmask
     * @param UriInterface $uri       Base Uri
     * @param LoggerInterface $logger Logger
     *
     * @return \Generator Parser generator
     */
    public static function createParsersFromFormats($formats, UriInterface $uri, LoggerInterface $logger)
    {
        $formatBits = intval($formats);

        // Run through all registered parsers and yield the requested instances
        foreach (self::$parsers as $parserFormat => $parserClass) {
            if ($parserFormat & $formatBits) {
                yield $parserFormat => new $parserClass($uri, $logger);
            }
        }
    }
}
