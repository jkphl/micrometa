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
     * Return a cached document
     *
     * @param string $url URL
     * @return RemoteDocument|null Cached document
     */
    public function getDocument($url)
    {
        return isset($this->documents[$url]) ? $this->documents[$url] : null;
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

        return $this->documents[$url] = $document;
    }

    /**
     * Process a context vocabulary
     *
     * @param array $context Context
     */
    protected function processContext(array $context)
    {
        // Run through all vocabulary terms
        foreach ($context as $name => $definition) {
            // Skip JSON-LD reserved terms
            if (!strncmp('@', $name, 1) || (is_string($definition) && !strncmp('@', $definition, 1))) {
                continue;
            }

            // Register vocabularies
            if (is_string($definition) && !isset($this->vocabularies[$definition])) {
                $this->prefices[$name] = $definition;
                $this->vocabularies[$definition] = [];

                // Else: Register vocabulary term
            } elseif (is_object($definition) && isset($definition->{'@id'})) {
                $prefixName = explode(':', $definition->{'@id'}, 2);
                if (count($prefixName) == 2) {
                    if (isset($this->prefices[$prefixName[0]])) {
                        $this->vocabularies[$this->prefices[$prefixName[0]]][$prefixName[1]] = true;
                    }
                }
            }
        }
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

        // Run through all vocabularies
        foreach ($this->vocabularies as $profile => $terms) {
            $profileLength = strlen($profile);
            if (!strncasecmp($profile, $name, $profileLength) && !empty($terms[substr($name, $profileLength)])) {
                $iri->profile = $profile;
                $iri->name = substr($name, $profileLength);
            }
        }

        return $iri;
    }
}
