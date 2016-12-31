<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Jkphl\Micrometa\Parser;

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Jkphl\Micrometa\Document;
use Jkphl\Micrometa\Parser\JsonLD\Item;
use Jkphl\Utility\Url;
use ML\JsonLD\Exception\JsonLdException;
use ML\JsonLD\JsonLD as JsonLDParser;
use ML\JsonLD\Node;
use ML\JsonLD\NodeInterface;
use ML\JsonLD\TypedValue;
use ML\JsonLD\Value;

/**
 * JSON-LD parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link https://github.com/lanthaler/JsonLD
 */
class JsonLD
{
    /**
     * Original resource URL
     *
     * @var Url
     */
    protected $_url = null;
    /**
     * DOM
     *
     * @var Document
     */
    protected $_dom = null;
    /**
     * Top-level JSON-LD items
     *
     * @var \array
     */
    protected $_items = null;
    /**
     * Parser name
     *
     * @var string
     */
    const NAME = 'json-ld';

    /**
     * Constructor
     *
     * @param Document|string $source The data to parse. A string of HTML or a DOMDocument
     * @param Url|\string $url Optional: The URL of the parsed document, for relative URL resolution
     */
    public function __construct($source, $url)
    {
        $this->_dom = ($source instanceof Document) ? $source : Document::fromHTMLSource($source);
        $this->_url = ($url instanceof Url) ? $url : new Url($url);
    }

    /**
     * Retrieve and refine the contained micro information items
     *
     * @return \array                                Refined items
     */
    public function items()
    {
        if ($this->_items === null) {
            $this->_items = array();

            // Run through all embedded JSON-LD scripts
            /** @var \DOMElement $jsonLD */
            foreach ($this->_dom->xpath()->query('//script[@type = "application/ld+json"]') as $jsonLD) {
                $jsonLDInline = trim($jsonLD->textContent);
                if (strlen($jsonLDInline)) {
                    $this->_items = $this->_items + $this->parseBlock($jsonLDInline);
                }
            }
        }

        return $this->_items;
    }

    /**
     * Create an new item
     *
     * @param array $data Item data
     * @return Item Item
     */
    protected function toItem(array $data)
    {
        $data = array_filter($data, [$this, 'filter']);

        // Simplify the type (if any)
        if (!empty($data['type']) && is_array($data['type'])) {
            /**
             * @var int $typeIndex
             * @var Item $typeItem
             */
            foreach ($data['type'] as $typeIndex => $typeItem) {
                $data['type'][$typeIndex] = $typeItem->id;
            }
        }

        return new Item($data, $this->_url);
    }

    /**
     * Parse a JSON-LD block
     *
     * @param string $source JSON-LD Block
     * @return array Items
     */
    protected function parseBlock($source)
    {
        $items = array();
        $jsonLD = @json_decode($source);

        // If there are several root nodes
        if (is_array($jsonLD)) {
            foreach ($jsonLD as $root) {
                $item = $this->parseRootNode($root);
                if ($item instanceof Item) {
                    $items[] = $item;
                }
            }
        } elseif (is_object($jsonLD)) {
            $item = $this->parseRootNode($jsonLD);
            if ($item instanceof Item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Parse the root node of a JSON-LD block
     *
     * @param \stdClass $root JSON-LD root node
     * @return Item
     */
    protected function parseRootNode($root)
    {
        $item = null;

        try {
            // Run through all nodes to parse the first one
            /** @var Node $node */
            foreach (JsonLDParser::getDocument($root)->getGraph()->getNodes() as $node) {
                $item = $this->parseNode($node);
                break;
            }

        } catch (JsonLdException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return $item;
    }

    /**
     * Parse a JSON-LD fragment
     *
     * @param Node|TypedValue $jsonLD JSON-LD fragment
     * @return mixed Parsed fragment
     */
    protected function parse($jsonLD)
    {
        if ($jsonLD instanceof NodeInterface) {
            return $this->parseNode($jsonLD);
        } elseif ($jsonLD instanceof Value) {
            return $this->parseValue($jsonLD);
        } else {
            echo 'Unknown JSON-LD item: '.get_class($jsonLD).PHP_EOL;
            return null;
        }
    }

    /**
     * Parse a JSON-LD node
     *
     * @param Node $node Node
     * @return Item Item
     */
    protected function parseNode(Node $node)
    {
        $data = array(
            'type' => array(),
            'properties' => array(),
            'children' => array(),
        );

        // Add the item type(s)
        if ($itemType = $node->getType()) {
            $type = $this->parse($itemType);
            if ($type) {
                $data['type'][] = $type;
            }
        }

        // Add the item ID
        if ($itemId = $node->getId()) {
            $data['id'] = $itemId;
        }

        // Run through all node properties
        foreach ($node->getProperties() as $name => $property) {

            // Skip the node type
            if ($name === Node::TYPE) {
                continue;
            }

            $value = $this->parse($property);

            // If this is a nested item
            if ($value instanceof Item) {
                if (count($value->types)) {
                    $data['children'][] = $value;

                    // @type = @id
                } else {
                    $data['properties'][$name][] = $value->id;
                }

                // Else
            } elseif ($value) {
                $data['properties'][$name][] = $value;
            }
        }

        return $this->toItem($data);
    }

    /**
     * Parse a typed value
     *
     * @param TypedValue $value Typed value
     * @return string Value
     */
    protected function parseValue(TypedValue $value)
    {
        return $value->getValue();
    }

    /**
     * Filter empty values
     *
     * @param array|string $value Value
     * @return bool Value is not empty
     */
    protected function filter($value)
    {
        return is_array($value) ? !!count($value) : strlen($value);
    }
}
