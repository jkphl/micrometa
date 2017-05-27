<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Rdfalite
 * @subpackage Jkphl\Micrometa\Infrastructure
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

namespace Jkphl\Micrometa\Infrastructure\Parser\JsonLD;

use Jkphl\Micrometa\Ports\Cache;
use ML\JsonLD\RemoteDocument;

/**
 * Vocabulary cache
 *
 * @package Jkphl\Rdfalite
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class VocabularyCache
{
    /**
     * Documents
     *
     * @var RemoteDocument[]
     */
    protected $documents = [];
    /**
     * Vocabularies
     *
     * @var array
     */
    protected $vocabularies = [];
    /**
     * Vocabulary prefices
     *
     * @var array
     */
    protected $prefices = [];
    /**
     * Document cache slot
     *
     * @var string
     */
    const SLOT_DOC = 'jsonld.doc';
    /**
     * Vocabulary cache slot
     *
     * @var string
     */
    const SLOT_VOCABS = 'jsonld.vocabs';

    /**
     * Return a cached document
     *
     * @param string $url URL
     * @return RemoteDocument|null Cached document
     */
    public function getDocument($url)
    {
        $urlHash = $this->getCacheHash($url, self::SLOT_DOC);

        // Try to retrieve the document from the cache
        if (Cache::getAdapter()->hasItem($urlHash)) {
            return Cache::getAdapter()->getItem($urlHash)->get();
        }

        return null;
    }

    /**
     * Cache a document
     *
     * @param string $url URL
     * @param RemoteDocument $document Document
     * @return RemoteDocument Document
     */
    public function setDocument($url, RemoteDocument $document)
    {
        // Process the context
        if (isset($document->document) && is_object($document->document)) {
            if (isset($document->document->{'@context'}) && is_object($document->document->{'@context'})) {
                $this->processContext((array)$document->document->{'@context'});
            }
        }

        // Save the document to the cache
        $docUrlHash = $this->getCacheHash($url, self::SLOT_DOC);
        $cachedDocument = Cache::getAdapter()->getItem($docUrlHash);
        $cachedDocument->set($document);
        Cache::getAdapter()->save($cachedDocument);

        // Return the document
        return $document;
    }

    /**
     * Process a context vocabulary
     *
     * @param array $context Context
     */
    protected function processContext(array $context)
    {
        $prefices = [];
        $vocabularyCache = Cache::getAdapter()->getItem(self::SLOT_VOCABS);
        $vocabularies = $vocabularyCache->isHit() ? $vocabularyCache->get() : [];

        // Run through all vocabulary terms
        foreach ($context as $name => $definition) {
            // Skip JSON-LD reserved terms
            if (!strncmp('@', $name, 1) || (is_string($definition) && !strncmp('@', $definition, 1))) {
                continue;
            }

            // Register a prefix (and vocabulary)
            if (is_string($definition) && !isset($prefices[$name])) {
                $prefices[$name] = $definition;

                // Register the vocabulary
                if (!isset($vocabularies[$definition])) {
                    $vocabularies[$definition] = [];
                }

                // Else: Register vocabulary term
            } elseif (is_object($definition) && isset($definition->{'@id'})) {
                $prefixName = explode(':', $definition->{'@id'}, 2);
                if (count($prefixName) == 2) {
                    if (isset($prefices[$prefixName[0]])) {
                        $vocabularies[$prefices[$prefixName[0]]][$prefixName[1]] = true;
                    }
                }
            }
        }

        $vocabularyCache->set($vocabularies);
        Cache::getAdapter()->save($vocabularyCache);
    }

    /**
     * Create an IRI from a name considering the known vocabularies
     *
     * @param string $name Name
     * @return \stdClass IRI
     */
    public function expandIRI($name)
    {
        $iri = (object)['name' => $name, 'profile' => ''];
        $vocabularies = Cache::getAdapter()->getItem(self::SLOT_VOCABS);

        // Run through all vocabularies
        if ($vocabularies->isHit()) {
            foreach ($vocabularies->get() as $profile => $terms) {
                $profileLength = strlen($profile);
                if (!strncasecmp($profile, $name, $profileLength) && !empty($terms[substr($name, $profileLength)])) {
                    $iri->profile = $profile;
                    $iri->name = substr($name, $profileLength);
                }
            }
        }

        return $iri;
    }

    /**
     * Create a cache hash
     *
     * @param string $str String
     * @param string $slot Slot
     * @return string URL hash
     */
    protected function getCacheHash($str, $slot)
    {
        return $slot.'.'.md5($str);
    }
}
