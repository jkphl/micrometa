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

use Jkphl\Micrometa\Ports\Cache;
use ML\JsonLD\RemoteDocument;

/**
 * Vocabulary cache
 *
 * @package    Jkphl\Rdfalite
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class VocabularyCache
{
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
     * Return a cached document
     *
     * @param string $url URL
     *
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
     * Create a cache hash
     *
     * @param string $str  String
     * @param string $slot Slot
     *
     * @return string URL hash
     */
    protected function getCacheHash($str, $slot)
    {
        return $slot.'.'.md5($str);
    }

    /**
     * Cache a document
     *
     * @param string $url              URL
     * @param RemoteDocument $document Document
     *
     * @return RemoteDocument Document
     */
    public function setDocument($url, RemoteDocument $document)
    {
        // Process the context
        if (isset($document->document->{'@context'}) && is_object($document->document->{'@context'})) {
            $this->processContext((array)$document->document->{'@context'});
        }

        // Save the document to the cache
        $docUrlHash     = $this->getCacheHash($url, self::SLOT_DOC);
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
        $prefices        = [];
        $vocabularyCache = Cache::getAdapter()->getItem(self::SLOT_VOCABS);
        $vocabularies    = $vocabularyCache->isHit() ? $vocabularyCache->get() : [];

        // Run through all vocabulary terms
        foreach ($context as $name => $definition) {
            // Skip JSON-LD reserved terms
            if ($this->isReservedTokens($name, $definition)) {
                continue;
            }

            // Process this prefix / vocabulary term
            $this->processPrefixVocabularyTerm($name, $definition, $prefices, $vocabularies);
        }

        $vocabularyCache->set($vocabularies);
        Cache::getAdapter()->save($vocabularyCache);
    }

    /**
     * Test if a vocabulary name or definition is a reserved term
     *
     * @param string $name       Name
     * @param string $definition Definition
     *
     * @return boolean Is reserved term
     */
    protected function isReservedTokens($name, $definition)
    {
        return !strncmp('@', $name, 1) || (is_string($definition) && !strncmp('@', $definition, 1));
    }

    /**
     * Process a prefix / vocabulary term
     *
     * @param string $name                 Prefix name
     * @param string|\stdClass $definition Definition
     * @param array $prefices              Prefix register
     * @param array $vocabularies          Vocabulary register
     */
    protected function processPrefixVocabularyTerm($name, $definition, array &$prefices, array &$vocabularies)
    {
        // Register a prefix (and vocabulary)
        if ($this->isPrefix($name, $definition, $prefices)) {
            $this->processPrefix($name, strval($definition), $prefices, $vocabularies);

            // Else: Register vocabulary term
        } elseif ($this->isTerm($definition)) {
            $this->processVocabularyTerm((object)$definition, $prefices, $vocabularies);
        }
    }

    /**
     * Test whether this is a prefix and vocabulary definition
     *
     * @param string $name                 Prefix name
     * @param string|\stdClass $definition Definition
     * @param array $prefices              Prefix register
     *
     * @return bool Is a prefix and vocabulary definition
     */
    protected function isPrefix($name, $definition, array &$prefices)
    {
        return is_string($definition) && !isset($prefices[$name]);
    }

    /**
     * Process a vocabulary prefix
     *
     * @param string $name        Prefix name
     * @param string $definition  Prefix definition
     * @param array $prefices     Prefix register
     * @param array $vocabularies Vocabulary register
     */
    protected function processPrefix($name, $definition, array &$prefices, array &$vocabularies)
    {
        $prefices[$name] = $definition;

        // Register the vocabulary
        if (!isset($vocabularies[$definition])) {
            $vocabularies[$definition] = [];
        }
    }

    /**
     * Test whether this is a term definition
     *
     * @param string|\stdClass $definition Definition
     *
     * @return bool Is a term definition
     */
    protected function isTerm($definition)
    {
        return is_object($definition) && isset($definition->{'@id'});
    }

    /**
     * Process a vocabulary term
     *
     * @param \stdClass $definition Term definition
     * @param array $prefices       Prefix register
     * @param array $vocabularies   Vocabulary register
     */
    protected function processVocabularyTerm($definition, array &$prefices, array &$vocabularies)
    {
        $prefixName = explode(':', $definition->{'@id'}, 2);
        if (count($prefixName) == 2) {
            if (isset($prefices[$prefixName[0]])) {
                $vocabularies[$prefices[$prefixName[0]]][$prefixName[1]] = true;
            }
        }
    }

    /**
     * Create an IRI from a name considering the known vocabularies
     *
     * @param string $name Name
     *
     * @return \stdClass IRI
     */
    public function expandIRI($name)
    {
        $iri          = (object)['name' => $name, 'profile' => ''];
        $vocabularies = Cache::getAdapter()->getItem(self::SLOT_VOCABS);

        // Run through all vocabularies
        if ($vocabularies->isHit()) {
            $this->matchVocabularies($name, $vocabularies->get(), $iri);
        }

        return $iri;
    }

    /**
     * Match a name with the known vocabularies
     *
     * @param string $name        Name
     * @param array $vocabularies Vocabularies
     * @param \stdClass $iri      IRI
     */
    protected function matchVocabularies($name, array $vocabularies, &$iri)
    {
        // Run through all vocabularies
        foreach ($vocabularies as $profile => $terms) {
            $profileLength = strlen($profile);

            // If the name matches the profile and the remaining string is a registered term
            if (!strncasecmp($profile, $name, $profileLength) && !empty($terms[substr($name, $profileLength)])) {
                $iri->profile = $profile;
                $iri->name    = substr($name, $profileLength);

                return;
            }
        }
    }
}
