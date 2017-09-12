<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure\Parser
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

namespace Jkphl\Micrometa\Infrastructure\Parser;

use Jkphl\Micrometa\Application\Contract\ParsingResultInterface;
use Jkphl\Micrometa\Infrastructure\Parser\JsonLD\CachingContextLoader;
use Jkphl\Micrometa\Infrastructure\Parser\JsonLD\VocabularyCache;
use Jkphl\Micrometa\Ports\Format;
use ML\JsonLD\Exception\JsonLdException;
use ML\JsonLD\JsonLD as JsonLDParser;
use ML\JsonLD\LanguageTaggedString;
use ML\JsonLD\Node;
use ML\JsonLD\NodeInterface;
use ML\JsonLD\TypedValue;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * JsonLD parser
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 * @see https://jsonld-examples.com/
 * @see http://www.dr-chuck.com/csev-blog/2016/04/json-ld-performance-sucks-for-api-specs/
 */
class JsonLD extends AbstractParser
{
    /**
     * Format
     *
     * @var int
     */
    const FORMAT = Format::JSON_LD;
    /**
     * Regex pattern for matching leading comments in a JSON string
     *
     * @var string
     */
    const JSON_COMMENT_PATTERN = '#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#';
    /**
     * Vocabulary cache
     *
     * @var VocabularyCache
     */
    protected $vocabularyCache;
    /**
     * Context loader
     *
     * @var CachingContextLoader
     */
    protected $contextLoader;

    /**
     * JSON-LD parser constructor
     *
     * @param UriInterface $uri Base URI
     * @param LoggerInterface $logger Logger
     */
    public function __construct(UriInterface $uri, LoggerInterface $logger)
    {
        parent::__construct($uri, $logger);
        $this->vocabularyCache = new VocabularyCache();
        $this->contextLoader = new CachingContextLoader($this->vocabularyCache);
    }

    /**
     * Parse a DOM document
     *
     * @param \DOMDocument $dom DOM Document
     * @return ParsingResultInterface Micro information items
     */
    public function parseDom(\DOMDocument $dom)
    {
        $this->logger->info('Running parser: '.(new \ReflectionClass(__CLASS__))->getShortName());
        $items = [];

        // Find and process all JSON-LD documents
        $xpath = new \DOMXPath($dom);
        $jsonLDDocs = $xpath->query('//*[local-name(.) = "script"][@type = "application/ld+json"]');
        $this->logger->debug('Processing '.$jsonLDDocs->length.' JSON-LD documents');

        // Run through all JSON-LD documents
        foreach ($jsonLDDocs as $jsonLDDoc) {
            $jsonLDDocSource = preg_replace(self::JSON_COMMENT_PATTERN, '', $jsonLDDoc->textContent);
            $i = $this->parseDocument($jsonLDDocSource);
            $items = array_merge($items, $i);
        }

        return new ParsingResult(self::FORMAT, $items);
    }

    /**
     * Parse a JSON-LD document
     *
     * @param string $jsonLDDocSource JSON-LD document
     * @return array Items
     */
    protected function parseDocument($jsonLDDocSource)
    {
        // Unserialize the JSON-LD document
        $jsonLDDoc = @json_decode($jsonLDDocSource);

        // If this is not a valid JSON document: Return
        if (!is_object($jsonLDDoc) && !is_array($jsonLDDoc)) {
            $this->logger->error('Skipping invalid JSON-LD document');
            return [];
        }

        // Parse the document
        return array_filter(
            is_array($jsonLDDoc) ?
                array_map([$this, 'parseRootNode'], $jsonLDDoc) : [$this->parseRootNode($jsonLDDoc)]
        );
    }

    /**
     * Parse a JSON-LD root node
     *
     * @param \stdClass $jsonLDRoot JSON-LD root node
     */
    protected function parseRootNode($jsonLDRoot)
    {
        $item = null;

        try {
            $jsonDLDocument = JsonLDParser::getDocument($jsonLDRoot, ['documentLoader' => $this->contextLoader]);

            // Run through all nodes to parse the first one
            /** @var Node $node */
            foreach ($jsonDLDocument->getGraph()->getNodes() as $node) {
                $item = $this->parseNode($node);
                break;
            }
        } catch (JsonLdException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        return $item;
    }

    /**
     * Parse a JSON-LD node
     *
     * @param NodeInterface $node Node
     * @return \stdClass Item
     */
    protected function parseNode(NodeInterface $node)
    {
        return (object)[
            'type' => $this->parseNodeType($node),
            'id' => $node->getId() ?: null,
            'properties' => $this->parseNodeProperties($node),
        ];
    }

    /**
     * Parse the type of a JSON-LD node
     *
     * @param NodeInterface $node Node
     * @return array Item type
     */
    protected function parseNodeType(NodeInterface $node)
    {
        /** @var Node $itemType */
        return ($itemType = $node->getType()) ? [$this->vocabularyCache->expandIRI($itemType->getId())] : [];
    }

    /**
     * Parse the properties of a JSON-LD node
     *
     * @param NodeInterface $node Node
     * @return array Item properties
     */
    protected function parseNodeProperties(NodeInterface $node)
    {
        $properties = [];

        // Run through all node properties
        foreach ($node->getProperties() as $name => $property) {
            // Skip the node type
            if ($name === Node::TYPE) {
                continue;
            }

            // Initialize the property (if necessary)
            $this->initializeNodeProperty($name, $properties);

            // Parse and process the property value
            $this->processNodeProperty($name, $this->parse($property), $properties);
        }

        return $properties;
    }

    /**
     * Initialize a JSON-LD node property (if necessary)
     *
     * @param string $name Property name
     * @param array $properties Item properties
     */
    protected function initializeNodeProperty($name, array &$properties)
    {
        if (empty($properties[$name])) {
            $properties[$name] = $this->vocabularyCache->expandIRI($name);
            $properties[$name]->values = [];
        }
    }

    /**
     * Process a property value
     *
     * @param string $name Property name
     * @param \stdClass|array|string $value Property value
     * @param array $properties Item properties
     */
    protected function processNodeProperty($name, $value, array &$properties)
    {
        // If this is a nested item
        if (is_object($value)) {
            $this->processNodePropertyObject($name, $value, $properties);

            // Else: If this is a value list
        } elseif (is_array($value)) {
            foreach ($value as $listValue) {
                $this->processNodeProperty($name, $listValue, $properties);
            }

            // Else: If the value is not empty
        } elseif ($value) {
            $properties[$name]->values[] = $value;
        }
    }

    /**
     * Process a property value object
     *
     * @param string $name Property name
     * @param \stdClass $value Property value
     * @param array $properties Properties
     */
    protected function processNodePropertyObject($name, $value, array &$properties)
    {
        if (!empty($value->type) || !empty($value->lang)) {
            $properties[$name]->values[] = $value;

            // @type = @id
        } elseif (!empty($value->id)) {
            $properties[$name]->values[] = $value->id;
        }
    }

    /**
     * Parse a JSON-LD fragment
     *
     * @param NodeInterface|LanguageTaggedString|TypedValue|array $jsonLD JSON-LD fragment
     * @return \stdClass|string|array Parsed fragment
     */
    protected function parse($jsonLD)
    {
        // If it's a node object
        if ($jsonLD instanceof NodeInterface) {
            return $this->parseNode($jsonLD);

            // Else if it's a language tagged string
        } elseif ($jsonLD instanceof LanguageTaggedString) {
            return $this->parseLanguageTaggedString($jsonLD);

            // Else if it's a typed value
        } elseif ($jsonLD instanceof TypedValue) {
            return $this->parseTypedValue($jsonLD);
        }

        // Else if it's a list of items
        //elseif (is_array($jsonLD)) {
        return array_map([$this, 'parse'], $jsonLD);
//      }
    }

    /**
     * Parse a language tagged string
     *
     * @param LanguageTaggedString $value Language tagged string
     * @return \stdClass Value
     */
    protected function parseLanguageTaggedString(LanguageTaggedString $value)
    {
        return (object)['value' => $value->getValue(), 'lang' => $value->getLanguage()];
    }

    /**
     * Parse a typed value
     *
     * @param TypedValue $value Typed value
     * @return string Value
     */
    protected function parseTypedValue(TypedValue $value)
    {
        return $value->getValue();
    }
}
