<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
namespace Jkphl\Micrometa\Parser\Microdata;

/**
 * *********************************************************************************
 * The MIT License (MIT)
 *
 * Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * *********************************************************************************
 */

use Jkphl\Micrometa\Parser\Microdata\Item;

/**
 * Extended DOM element
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link https://github.com/euskadi31/Microdata
 * @property Document $ownerDocument Owner document
 */
class Element extends \DOMElement
{
    /************************************************************************************************
     * PUBLIC METHODS
     ***********************************************************************************************/

    /**
     * Return an item representation of this element
     *
     * @param \string $url URL
     * @param \array $register Registry of parsed item nodes
     * @return Item Item
     */
    public function toItem($url, array &$register)
    {
        $register[$this->getNodePath()] = $this;
        $data = array(
            'properties' => array(),
        );

        // Add itemtype.
        if ($itemType = $this->itemType()) {
            $data['type'] = $itemType;
        }
        // Add itemid.
        if ($itemId = $this->itemId()) {
            $data['id'] = $itemId;
        }

        // Run through all properties
        /* @var $property Element */
        foreach ($this->properties() as $property) {
            $value = null;

            // If it's a nested item
            if ($property->itemScope()) {

                // If it has already been parsed: Reference it
                if (!empty($register[$property->getNodePath()])) {
                    $value &= $register[$property->getNodePath()];

                    // Else: Parse it
                } else {
                    $value = $property->toItem($url, $register);
                }

                // Else: Register as property
            } else {
                $value = $property->propertyValue();
            }

            if ($value !== null) {
                foreach ($property->itemProp() as $propertyName) {
                    $data['properties'][$propertyName][] = $value;
                }
            }
        }

        return new Item($data, $url);
    }

    /**
     * Return the item's types
     *
     * @return \array|NULL          Item's types
     */
    public function itemType()
    {
        $itemtype = $this->getAttribute('itemtype');
        return empty($itemtype) ? null : $this->_tokenList($itemtype);
    }

    /**
     * Split an attribute value by whitespace and return as token list
     *
     * @param \string $str Attribute value
     * @return array                 Token list
     */
    protected function _tokenList($string)
    {
        return preg_split("%\s+%", trim($string));
    }

    /**
     * Return the item's ID
     *
     * @return \string|NULL         Item ID
     */
    public function itemId()
    {
        return $this->getAttribute('itemid') ?: null;
    }

    /**
     * Retrieve the item's properties
     *
     * Attention: nested items are registered but not parsed
     *
     * @return \array             Properties
     */
    public function properties()
    {
        $properties = array();

        // If this element creates a new scope
        if ($this->itemScope()) {
            $toTraverse = array(
                $this
            );

            foreach ($this->itemRef() as $itemref) {
                foreach ($this->ownerDocument->xpath()->query('//*[@id="'.$itemref.'"]') as $child) {
                    $this->_traverse($child, $toTraverse, $properties, $this);
                }
            }

            while (count($toTraverse)) {
                $this->_traverse($toTraverse[0], $toTraverse, $properties, $this);
            }
        }

        return $properties;
    }

    /**
     * Test if this item creates a new scope
     *
     * @return \boolean             Item creates a new scope
     */
    public function itemScope()
    {
        return $this->hasAttribute('itemscope');
    }

    /**
     * Retrieve the IDs of other items which this item references
     *
     * @return \array               Referenced IDs
     */
    public function itemRef()
    {
        $itemref = $this->getAttribute('itemref');
        return empty($itemref) ? array() : $this->_tokenList($itemref);
    }

    /**
     * Traverses the DOM tree
     *
     * @param Element $node Node to be traversed
     * @param \array $toTraverse Complete list of nodes to be traversed
     * @param \array $properties Gathered properties
     * @param Element $root Root element
     * @return \void
     */
    protected function _traverse(Element $node, array &$toTraverse, array &$properties, Element $root)
    {
        // Remove the current node from the list of still to be traversed elements
        /* @var $element Element */
        $filteredToTraverse = array();
        foreach ($toTraverse as $element) {
            if (!$element->isSameNode($node)) {
                $filteredToTraverse[] = $element;
            }
        }
        $toTraverse = $filteredToTraverse;

        // If the current node is not the root node
        if (!$root->isSameNode($node)) {

            // If it has at least one property name
            if (count($node->itemProp())) {

                // Register it as property
                $properties[] = $node;
            }

            // If the node itself creates a new scope: Break
            if ($node->itemScope()) {
                return;
            }
        }

        // Recursively descend into the DOM tree and search for nested properties
        foreach ($this->ownerDocument->xpath()->query('*', $node) as $child) {
            $this->_traverse($child, $toTraverse, $properties, $root);
        }
    }

    /************************************************************************************************
     * PRIVATE METHODS
     ***********************************************************************************************/

    /**
     * Return the item's property names
     *
     * @return \array               Property names
     */
    public function itemProp()
    {
        $itemprop = $this->getAttribute('itemprop');
        return empty($itemprop) ? array() : $this->_tokenList($itemprop);
    }

    /**
     * Retrieve a property's value (type and tagname dependent)
     *
     * @return NULL|Element|\string       Property value
     */
    public function propertyValue()
    {
        // If this is not a property: Don't return a value
        if (!count($this->itemProp())) {
            return null;
        }

        // If the property creates a new scope: Return the node itself
        if ($this->itemScope()) {
            return $this;
        }

        // Else: Depend on the tag name
        switch (strtoupper($this->tagName)) {
            case 'META':
                return $this->getAttribute('content');
                break;

            case 'AUDIO':
            case 'EMBED':
            case 'IFRAME':
            case 'IMG':
            case 'SOURCE':
            case 'TRACK':
            case 'VIDEO':
                return $this->getAttribute('src');
                break;

            case 'A':
            case 'AREA':
            case 'LINK':
                return $this->getAttribute('href');
                break;

            case 'OBJECT':
                return $this->getAttribute('data');
                break;

            case 'DATA':
                return $this->getAttribute('value');
                break;

            case 'TIME':
                $datetime = $this->getAttribute('datetime');
                if (!empty($datetime)) {
                    return $datetime;
                }

            default:
//              trigger_error(sprintf('Microdata parser: Unhandled tag name "%s"', $this->tagName), E_USER_WARNING);
                return $this->textContent;
        }
    }
}
