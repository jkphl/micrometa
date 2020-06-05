<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
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

namespace Jkphl\Micrometa\Infrastructure\Logger;

use Jkphl\Micrometa\Ports\Exceptions\RuntimeException;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

/**
 * Exception logger
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class ExceptionLogger extends Logger
{
    /**
     * Exception threshold
     *
     * @var int
     */
    protected $threshold;

    /**
     * Constructor
     */
    public function __construct($threshold = Logger::ERROR)
    {
        $this->threshold = $threshold;
        parent::__construct('exception', [new NullHandler()]);
    }

    /**
     * Throws an exception for all messages with error level or higher
     *
     * @param  mixed $level    The log level
     * @param  string $message The log message
     * @param  array $context  The log context
     *
     * @return Boolean Whether the record has been processed
     * @throws \Exception Exception that occured
     * @throws \RuntimeException Log message as exception
     */
    public function addRecord(int $level, string $message, array $context = []): bool
    {
        if ($this->isTriggered($level)) {
            throw $this->getContextException($context) ?: new RuntimeException($message, $level);
        }

        return parent::addRecord($level, $message, $context);
    }

    /**
     * Return whether an exception should be triggered
     *
     * @param int $level Log level
     *
     * @return bool Exception should be triggered
     */
    protected function isTriggered($level)
    {
        return $this->threshold && ($level >= $this->threshold);
    }

    /**
     * Return the context exception (if any)
     *
     * @param array $context Context
     *
     * @return \Exception|null Context exception
     */
    protected function getContextException(array $context)
    {
        return (isset($context['exception']) && ($context['exception'] instanceof \Exception)) ?
            $context['exception'] : null;
    }
}
