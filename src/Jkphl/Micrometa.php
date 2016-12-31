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

namespace Jkphl;

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
use Jkphl\Micrometa\Parser\JsonLD;
use Jkphl\Micrometa\Parser\Microdata;
use Jkphl\Micrometa\Parser\Microformats2;
use Jkphl\Micrometa\Parser\Microformats2\Exception as Mf2Exception;
use Jkphl\Micrometa\Parser\Microformats2\Item as Mf2Item;
use Jkphl\Utility\Url;
use Jkphl\Micrometa\Item;

/**
 * Micrometa main parser class
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @method array|Item hEntry(int $index = null) Return a nested h-entry
 * @method array|Item hCard(int $index = null) Return a nested h-card
 */
class Micrometa
{
    /**
     * Resource document DOM
     *
     * @var \DOMDocument
     */
    public $dom = null;
    /**
     * Resource document base URL
     *
     * @var Url
     */
    public $baseUrl = null;
    /**
     * Resource document URL
     *
     * @var Url
     */
    protected $_url = null;
    /**
     * Resource document source code
     *
     * @var \string
     */
    protected $_source = null;
    /**
     * Focus node
     *
     * @var \DOMElement
     */
    protected $_focus = null;
    /**
     * Document has been parsed
     *
     * @var \boolean
     */
    protected $_parsed = false;
    /**
     * Extracted micro information
     *
     * @var \stdClass
     */
    protected $_result = null;

    /************************************************************************************************
     * PUBLIC METHODS
     ***********************************************************************************************/

    /**
     * Constructor
     *
     * @param \string $url Resource document URL
     * @param \string $source Resource document source code
     */
    public function __construct($url, $source = null)
    {
        $this->_url = new Url($url, true);
        $this->_source = ($source === null) ? $this->_getUrl($url) : $source;
        $this->_source = mb_convert_encoding($this->_source, 'HTML-ENTITIES', mb_detect_encoding($this->_source));
        $this->dom = Document::fromHTMLSource($this->_source);
        $this->_focus = $this->dom->documentElement;

        // Determine and resolve the base URL
        $this->baseUrl = $this->_url;
        /** @var \DOMElement $base */
        foreach ($this->dom->xpath()->query('//base[@href]') as $base) {
            $this->baseUrl = new Url($base->getAttribute('href'), true);
            $this->baseUrl->absolutize($this->_url);
            break;
        }
    }

    /**
     * Request an URL via GET (HTTP 1.1)
     *
     * @param \string $url Remote URL
     * @return \string                    Response content
     */
    protected function _getUrl($url)
    {
        // If cURL is available
        if (extension_loaded('curl')) {
            $curl = curl_init($url);
            curl_setopt_array(
                $curl, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.466.4 Safari/534.3',
                    CURLOPT_AUTOREFERER => true,
                    CURLOPT_CONNECTTIMEOUT => 120,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                )
            );
            $response = curl_exec($curl);
            curl_close($curl);

            // Else: Try via stream wrappers
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'GET',
                    'protocol_version' => 1.1,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.466.4 Safari/534.3',
                    'max_redirects' => 10,
                    'timeout' => 120,
                    'header' => "Accept-language: en\r\n",
                )
            );
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
        }

        return $response;
    }

    /**
     * Instance constructor
     *
     * @param \string $url Resource document URL
     * @param \string $source Resource document source code
     * @return \Jkphl\Micrometa Micrometa parser object
     */
    public static function instance($url, $source = null)
    {
        return new self($url, $source);
    }

    /**
     * Restrict the parsing of Microformats2 markup to a specific element node of the resource document DOM (and it's descendants)
     *
     * @param \DOMElement $element Focus element node (must be a descendant of the resource document DOM)
     * @return \DOMElement Focus element
     */
    public function focus(\DOMElement $element)
    {
        if ($element->ownerDocument === $this->dom) {
            $this->_focus = $element;
            $this->_parsed = false;
        }
        return $this->_focus;
    }

    /**
     * Return all alternative resources
     *
     * @return \array                        Alternative resources
     */
    public function alternates()
    {
        if (!$this->_parsed) {
            $this->parse();
        }

        return (array)$this->_result->alternates;
    }

    /**
     * Parse the document for embedded micro information (all supported formats)
     */
    public function parse()
    {
        // Parse with the microformats2 parser
        $this->_result = $this->parseMicroformats2();

        // Parse with the microdata parser
//        $this->_result->items = array_merge($this->_result->items, $this->parseMicrodata());

        // Parse with the JSON-LD parser
        $this->_result->items = array_merge($this->_result->items, $this->parseJsonLD());

        // Set the "parsed" flag
        $this->_parsed = true;
    }

    /**
     * Parse Microformats2
     *
     * @return \stdClass Result items
     */
    protected function parseMicroformats2()
    {
        $microformatsParser = new Microformats2($this->dom, $this->baseUrl);
        $microformats = $microformatsParser->parse(true, $this->_focus);
        $result = (object)array_merge(
            array(
                'items' => array(),
                'rels' => array(),
                'alternates' => array(),
            ), $microformats
        );
        $result->rels = (object)$result->rels;
        foreach ($result->alternates as $index => $alternate) {
            $result->alternates[$index] = (object)$alternate;
        }
        return $result;
    }

    /**
     * Parse Microdata
     *
     * @return array Microdata items
     */
    protected function parseMicrodata()
    {
        $microdataParser = new Microdata(strval($this->_url), $this->dom->saveXML());
        return $microdataParser->items();
    }

    /**
     * Parse JSON-LD
     *
     * @return array JSON-LD items
     */
    protected function parseJsonLD()
    {
        $jsonLDParser = new JsonLD($this->dom, $this->_url);
        return $jsonLDParser->items();
    }

    /**
     * Convenienve method extracting author data according to the microformats authorship algorithm
     *
     * @param \int $entry h-entry index
     * @return Mf2Item Author micro information item
     * @see http://indiewebcamp.com/authorship
     */
    public function author($entry = 0)
    {
        try {

            // 1. Look for the appropriate h-entry within the document
            $hEntry = $this->hEntry($entry);

            // 2. Try to find p-author property respectively a nested h-card
            $authorItem = $hEntry->author;
            if (($authorItem instanceof Mf2Item) && $authorItem->isOfType('h-card')) {
                return $authorItem;
            }

            // 3. If there's no p-author property / nested h-card
            $authorUrl = trim($this->rel('author', 0));

            // 3.1. If there's an author page
            if (strlen($authorUrl)) {
                $authorProfileUrl = Url::instance($authorUrl, true, $this->baseUrl);
                $authorUrl = "$authorProfileUrl";
                $authorProfile = new self($authorProfileUrl);
                $hCards = array();

                // Run through all h-cards on the author profile page
                foreach ((array)$authorProfile->hCard() as $hCard) {
                    if (($hCard instanceof Mf2Item) && $hCard->isOfType('h-card')) {

                        // Run through all uid properties (there should only be one!)
                        try {
                            foreach ((array)$hCard->uid() as $uidUrl) {
                                if (strval(
                                        Url::instance($uidUrl, true, $authorProfileUrl)
                                    ) == $authorUrl
                                ) {

                                    // Compare with each URL registered with the h-card
                                    foreach ((array)$hCard->url() as $hCardUrl) {

                                        // 3.1.2. In case of a match: Use this h-card as the author
                                        if (Url::instance(
                                                $hCardUrl, true, $authorProfileUrl
                                            ) == $authorUrl
                                        ) {
                                            return $hCard;
                                        }
                                    }
                                }
                            }
                        } catch (Mf2Exception $e) {
                        }

                        $hCards[] = $hCard;
                    }
                }

                // Gather all rel-me URLs on the author profile page
                $meUrls = array();
                foreach ((array)$authorProfile->rel('me') as $meUrl) {
                    if (strlen($meUrl)) {
                        $meUrls[] = strval(Url::instance($meUrl, true, $authorProfileUrl));
                    }
                }

                // Run through all the h-cards on the author profile page again
                foreach ($hCards as $hCard) {

                    // Compare the rel-me URLS with every h-card URL
                    foreach ((array)$hCard->url() as $hCardUrl) {

                        // 3.1.3. In case of a match: Use this h-card as the author
                        if (in_array(
                            strval(Url::instance($hCardUrl, true, $authorProfileUrl)), $meUrls
                        )) {
                            return $hCard;
                        }
                    }
                }

                // Final try: Run through all h-cards on the h-entry's page
                foreach ((array)$this->hCard() as $hCard) {

                    // Compare the rel-author URL with every h-card URL
                    foreach ((array)$hCard->url() as $hCardUrl) {

                        // 3.1.4. In case of a match: Use this h-card as the author
                        if ($authorUrl == strval(Url::instance($hCardUrl, true, $authorProfileUrl))) {
                            return $hCard;
                        }
                    }
                }
            }

        } catch (Mf2Exception $exception) {
        }

        return null;
    }

    /**
     * Return a list of related resources of a particular type (or a single item out of this list)
     *
     * @param \string $type Resource type
     * @param \int|NULL $index Optional: list index
     * @return \mixed                        Resource or resource list
     */
    public function rel($type, $index = null)
    {
        $rels = $this->rels();
        if ($type && array_key_exists($type, $rels)) {
            if ($index !== null) {
                if (is_array($rels[$type])) {
                    $index = intval($index);
                    return (($index < 0) || ($index > count($rels[$type]) - 1)) ? null : $rels[$type][$index];
                }
            } else {
                return $rels[$type];
            }
        }
        return null;
    }

    /**
     * Return all related resources
     *
     * @return \array                        Related resources
     */
    public function rels()
    {
        if (!$this->_parsed) {
            $this->parse();
        }

        return (array)$this->_result->rels;
    }

    /**
     * Load and extract an external author definition
     *
     * @return NULL|Item    Author micro information item
     * @deprecated                            Will be dropped in favour of the authorship algorithm, see author()
     */
    public function externalAuthor()
    {
        $author = null;
        $rels = $this->rels();
        if (!empty($rels['author']) && is_array($rels['author'])) {
            foreach ($rels['author'] as $authorProfileUrl) {
                $authorProfile = new self($authorProfileUrl);
                $authorItem = $authorProfile->item(
                    'http://schema.org/Person', 'http://data-vocabulary.org/Person', 'h-card'
                );
                if ($authorItem instanceof Item) {
                    $author = $authorItem;
                    break;
                }
            }
        }
        return $author;
    }

    /**
     * Return the first micro information item (of a specific type)
     *
     * @param \string $type1 Optional: Arbitrary number of item types
     * @param \string $type2
     * ...
     * @return Item        First micro information item of the resulting list
     */
    public function item()
    {
        $items = call_user_func_array(array($this, 'items'), func_get_args());
        return count($items) ? $items[0] : null;
    }

    /**
     * Return a JSON representation of the embedded micro information
     *
     * @param \boolean $beautify Beautify the JSON output (available since PHP 5.4)
     * @return \string                    JSON representation
     */
    public function toJSON($beautify = false)
    {
        $options = 0;
        if ($beautify && version_compare(PHP_VERSION, '5.4', '>=')) {
            $options |= JSON_PRETTY_PRINT;
        }
        return json_encode($this->toObject(), $options);
    }

    /************************************************************************************************
     * PRIVATE METHODS
     ***********************************************************************************************/

    /**
     * Return an object representation of the embedded micro information
     *
     * @return \stdClass                Object representation
     */
    public function toObject()
    {
        if (!$this->_parsed) {
            $this->parse();
        }

        $result = (object)array(
            'items' => array(),
            'rels' => $this->_result->rels,
            'alternates' => $this->_result->alternates,
        );

        /* @var $item Item */
        foreach ($this->_result->items as $item) {
            $result->items[] = $item->toObject();
        }

        return $result;
    }

    /************************************************************************************************
     * MAGIC METHODS
     ***********************************************************************************************/

    /**
     * Generic caller
     *
     * Returns all microformats2 items of a spefic type (or a particular index out of this list)
     *
     * @param \string $method Method name (lowerCamelCased microformats2 item type)
     * @param \array $arguments List of arguments, of which the first is interpreted as list index (NULL = return the complete list)
     * @return \array|Item List of microformats2 items or a single microformats2 item
     * @throws Mf2Exception If it's not a valid microformats2 vocable
     * @throws Mf2Exception If the item index is out of range
     */
    public function __call($method, array $arguments)
    {
        $mf2ItemType = Microformats2::decamelize($method);
        $mf2Items = $this->items($mf2ItemType);
        $mf2ItemIndex = count($arguments) ? intval($arguments[0]) : null;

        // If the complete item list is to be returned
        if ($mf2ItemIndex === null) {
            return $mf2Items;

            // Else: If the requested item index is out of range: Error
        } elseif (($mf2ItemIndex < 0) || ($mf2ItemIndex > count($mf2Items) - 1)) {
            throw new Mf2Exception(
                sprintf(Mf2Exception::INDEX_OUT_OF_RANGE_STR, $mf2ItemIndex),
                Mf2Exception::INDEX_OUT_OF_RANGE
            );

            // Else: Return the requested item index
        } else {
            return $mf2Items[$mf2ItemIndex];
        }
    }

    /**
     * Return a list of top level micro information items
     *
     * @param \string $type Optional: Arbitrary number of item types
     * @return \array Micro information item list
     */
    public function items()
    {
        if (!$this->_parsed) {
            $this->parse();
        }

        $items = array();
        if (!empty($this->_result->items)) {
            if (func_num_args()) {
                $itemTypes =
                $itemsByType = array();
                foreach (func_get_args() as $itemType) {
                    $itemType = trim($itemType);
                    if (strlen($itemType) && !array_key_exists($itemType, $itemsByType)) {
                        $itemTypes[] = $itemType;
                        $itemsByType[$itemType] = array();
                    }
                }

                /* @var $item Item */
                foreach ($this->_result->items as $item) {
                    foreach ($itemTypes as $itemType) {
                        if ($item->isOfType($itemType)) {
                            $itemsByType[$itemType][] = $item;
                            continue 2;
                        }
                    }
                }
                foreach ($itemsByType as $typedItems) {
                    if (count($typedItems)) {
                        $items = array_merge($items, $typedItems);
                    }
                }

            } else {
                $items = $this->_result->items;
            }
        }
        return $items;
    }

    /************************************************************************************************
     * STATIC METHODS
     ***********************************************************************************************/

    /**
     * Generic getter
     *
     * Returns the first microformats2 item of a specific type. Calling this method is equivalent to
     *
     * - calling the generic caller with 0 as the first argument
     * - calling item() with the specific microformats2 item type
     *
     * Example: All of the following are equivalent
     *
     * $micrometa->item('h-card')
     * $micrometa->hCard
     * $micrometa->hCard(0)
     *
     * @param \string $key Property name (lowerCamelCased microformats2 item type)
     * @return Item    microformats2 item
     */
    public function __get($key)
    {
        return $this->$key(0);
    }
}
