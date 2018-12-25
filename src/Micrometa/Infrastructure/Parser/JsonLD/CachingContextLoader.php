<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Rdfalite
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

namespace Jkphl\Micrometa\Infrastructure\Parser\JsonLD;

use ML\JsonLD\FileGetContentsLoader;
use ML\JsonLD\RemoteDocument;

/**
 * Cached loader for JSON-LD context
 *
 * @package    Jkphl\Rdfalite
 * @subpackage Jkphl\Micrometa\Infrastructure\Parser\JsonLD
 */
class CachingContextLoader extends FileGetContentsLoader
{
    /**
     * Vocabulary cache
     *
     * @var VocabularyCache
     */
    protected $vocabularyCache;

    /**
     * Constructor
     *
     * @param VocabularyCache $vocabularyCache Vocabulary cache
     */
    public function __construct(VocabularyCache $vocabularyCache)
    {
        $this->vocabularyCache = $vocabularyCache;
    }

    /**
     * Load (and cache) a context document
     *
     * @param string $url Context URL
     *
     * @return \ML\JsonLD\RemoteDocument Remote document
     */
    public function loadDocument($url)
    {
        // Try to return a cached document
        $document = $this->vocabularyCache->getDocument($url);
        if ($document instanceof RemoteDocument) {
            return $document;
        }

        return $this->vocabularyCache->setDocument($url, parent::loadDocument($url));
    }
}
