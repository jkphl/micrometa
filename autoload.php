<?php

/**
 * micrometa
 *
 * @category    Jkphl
 * @package     Jkphl\Micrometa
 * @author      Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright   Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
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

/**
 * Register a minimal PSR-4 compatible autoloader
 *
 * @see http://www.php-fig.org/psr/psr-4/
 */
spl_autoload_register(
    function ($class) {
        $namespace = 'Jkphl';
        $prefixes = [
            "{$namespace}\\" => [
                __DIR__.'/src',
            ],
        ];
        foreach ($prefixes as $prefix => $dirs) {
            $prefixLength = strlen($prefix);
            if (substr($class, 0, $prefixLength) !== $prefix) {
                continue;
            }
            $class = substr($class, $prefixLength);
            $part = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            foreach ($dirs as $dir) {
                $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
                $file = $dir.DIRECTORY_SEPARATOR.$part;
                if (is_readable($file)) {
                    require $file;
                    return;
                }
            }
        }
    }
);
