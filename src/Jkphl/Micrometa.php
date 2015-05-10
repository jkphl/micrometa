<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 */

namespace Jkphl;

/***********************************************************************************
 *  The MIT License (MIT)
 *  
 *  Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

// Include the Composer autoloader
if (@is_file(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
	require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
	
// Exit on failure
} else {
	die ((PHP_SAPI == 'cli') ?
		"\nPlease follow the instructions at https://github.com/jkphl/micrometa#dependencies to install the additional libraries that micrometa is depending on.\n\n" :
		'<p style="font-weight:bold;color:red">Please follow the <a href="https://github.com/jkphl/micrometa#dependencies" target="_blank">instructions</a> to install the additional libraries that micrometa is depending on</p>'
	);
}

require_once __DIR__.DIRECTORY_SEPARATOR.'Utility'.DIRECTORY_SEPARATOR.'Url.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Item.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Parser'.DIRECTORY_SEPARATOR.'Microformats2.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Parser'.DIRECTORY_SEPARATOR.'Microdata.php';

/**
 * Micrometa main parser class
 * 
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 */
class Micrometa {
	/**
	 * Resource document URL
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	/**
	 * Resource document source code
	 * 
	 * @var \string
	 */
	protected $_source = null;
	/**
	 * Resource document DOM
	 * 
	 * @var \DOMDocument
	 */
	public $dom = null;
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
	/**
	 * Resource document base URL
	 *
	 * @var \Jkphl\Utility\Url
	 */
	public $baseUrl = null;
	/**
	 * XPath operator
	 * 
	 * @var \DOMXPath
	 */
	public $xpath = null;
	
	/************************************************************************************************
	 * PUBLIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Constructor
	 * 
	 * @param \string $url				Resource document URL
	 * @param \string $source			Resource document source code
	 * @return \Jkphl\Micrometa			Micrometa parser object
	 */
	public function __construct($url, $source = null) {
		$this->_url						= new \Jkphl\Utility\Url($url, true);
		$this->_source					= ($source === null) ? $this->_getUrl($url) : $source;
		
		// Instanciate the source document as DOM object
		libxml_use_internal_errors(true);
		$this->_source					= mb_convert_encoding($this->_source, 'HTML-ENTITIES', mb_detect_encoding($this->_source));
		$this->dom						= new \DOMDocument();
		@$this->dom->loadHTML($this->_source);
		$this->xpath					= new \DOMXPath($this->dom);
		
		// Determine and resolve the base URL
		$this->baseUrl					= $this->_url;
		foreach ($this->xpath->query('//base[@href]') as $base) {
			$this->baseUrl				= new \Jkphl\Utility\Url($base->getAttribute('href'), true);
			$this->baseUrl->absolutize($this->_url);
			break;
		}
		
		// Set the focus node
		$this->_focus					= $this->dom->documentElement;
		libxml_use_internal_errors(false);
	}
	
	/**
	 * Restrict the parsing of Microformats2 markup to a specific element node of the resource document DOM (and it's descendants)
	 * 
	 * @param \DOMElement $element		Focus element node (must be a descendant of the resource document DOM)
	 * @return \DOMElement				Focus element
	 */
	public function focus(\DOMElement $element) {
		if ($element->ownerDocument === $this->dom) {
			$this->_focus				= $element;
			$this->_parsed				= false;
		}
		return $this->_focus;
	}
	
	/**
	 * Parse the document for embedded micro information (all supported formats)
	 * 
	 * @return \Jkphl\Micrometa			Micrometa parser object
	 */
	public function parse() {
		
		// Parse with the microformats2 parser
		$microformatsParser				= new \Jkphl\Micrometa\Parser\Microformats2($this->dom, $this->baseUrl);
		$microformats					= $microformatsParser->parse(true, $this->_focus);
		$this->_result					= (object)array_merge(array(
			'items'						=> array(),
			'rels'						=> array(),
			'alternates'				=> array(),
		), $microformats);
		$this->_result->rels			= (object)$this->_result->rels;
		foreach ($this->_result->alternates as $index => $alternate) {
			$this->_result->alternates[$index]				= (object)$alternate;
		}
		
		// Parse with the microdata parser
		$microdataParser				= new \Jkphl\Micrometa\Parser\Microdata(strval($this->_url), $this->dom->saveXML());
		$this->_result->items			= array_merge($this->_result->items, $microdataParser->items());
		
		// Set the "parsed" flag
		$this->_parsed					= true;
	}

	/**
	 * Return a list of top level micro information items
	 * 
	 * @param \string $type ...			Optional: Arbitrary number of item types
	 * @return \array					Micro information item list
	 */
	public function items() {
		if (!$this->_parsed) {
			$this->parse();
		}
		
		$items							= array();
		if (!empty($this->_result->items)) {
			if (func_num_args()) {
				$itemTypes				=
				$itemsByType			= array();
				foreach (func_get_args() as $itemType) {
					$itemType								= trim($itemType);
					if (strlen($itemType) && !array_key_exists($itemType, $itemsByType)) {
						$itemTypes[]						= $itemType;
						$itemsByType[$itemType]				= array();
					} 
				}
				
				/* @var $item \Jkphl\Micrometa\Item */
				foreach ($this->_result->items as $item) {
					foreach ($itemTypes as $itemType) {
						if ($item->isOfType($itemType)) {
							$itemsByType[$itemType][]		= $item;
							continue 2;
						}
					}
				}
				foreach ($itemsByType as $typedItems) {
					if (count($typedItems)) {
						$items			= array_merge($items, $typedItems);
					}
				}
				
			} else {
				$items					= $this->_result->items;
			}
		}
		return $items;
	}
	
	/**
	 * Return the first micro information item (of a specific type)
	 * 
	 * @param \string $type1				Optional: Arbitrary number of item types
	 * @param \string $type2
	 * ...
	 * @return \Jkphl\Micrometa\Item		First micro information item of the resulting list
	 */
	public function item() {
		$items							= call_user_func_array(array($this, 'items'), func_get_args());
		return count($items) ? $items[0] : null;
	}
	
	/**
	 * Return all related resources
	 * 
	 * @return \array						Related resources
	 */
	public function rels() {
		if (!$this->_parsed) {
			$this->parse();
		}
		
		return (array)$this->_result->rels;
	}
	
	/**
	 * Return a list of related resources of a particular type (or a single item out of this list)
	 * 
	 * @param \string $type					Resource type
	 * @param \int|NULL $index				Optional: list index
	 * @return \mixed						Resource or resource list
	 */
	public function rel($type, $index = null) {
		$rels							= $this->rels();
		if ($type && array_key_exists($type, $rels)) {
			if ($index !== null) {
				if (is_array($rels[$type])) {
					$index				= intval($index);
					return (($index < 0) || ($index > count($rels[$type]) - 1)) ? null : $rels[$type][$index];
				}
			} else {
				return $rels[$type];
			}
		}
		return null;
	}
	
	/**
	 * Return all alternative resources
	 *
	 * @return \array						Alternative resources
	 */
	public function alternates() {
		if (!$this->_parsed) {
			$this->parse();
		}
	
		return (array)$this->_result->alternates;
	}
	
	/**
	 * Convenienve method extracting author data according to the microformats authorship algorithm
	 * 
	 * @param \int $entry										h-entry index
	 * @return \Jkphl\Micrometa\Parser\Microformats2\Item		Author micro information item
	 * @see http://indiewebcamp.com/authorship
	 */
	public function author($entry = 0) {
		try {
			
			// 1. Look for the appropriate h-entry within the document
			$hEntry					= $this->hEntry($entry);
			
			// 2. Try to find p-author property respectively a nested h-card
 			$authorItem				= $hEntry->author;
 			if (($authorItem instanceof \Jkphl\Micrometa\Parser\Microformats2\Item) && $authorItem->isOfType('h-card')) {
 				return $authorItem;
 			}
 			
 			// 3. If there's no p-author property / nested h-card
			$authorUrl				= trim($this->rel('author', 0));
			
			// 3.1. If there's an author page
			if (strlen($authorUrl)) {
				$authorProfileUrl	= \Jkphl\Utility\Url::instance($authorUrl, true, $this->baseUrl);
				$authorUrl			= "$authorProfileUrl";
				$authorProfile		= new self($authorProfileUrl);
				$hCards				= array();
				
				// Run through all h-cards on the author profile page
				foreach ((array)$authorProfile->hCard() as $hCard) {
					if (($hCard instanceof \Jkphl\Micrometa\Parser\Microformats2\Item) && $hCard->isOfType('h-card')) {
						
						// Run through all uid properties (there should only be one!)
						try {
							foreach ((array)$hCard->uid() as $uidUrl) {
								if (strval(\Jkphl\Utility\Url::instance($uidUrl, true, $authorProfileUrl)) == $authorUrl) {

									// Compare with each URL registered with the h-card
									foreach ((array)$hCard->url() as $hCardUrl) {

										// 3.1.2. In case of a match: Use this h-card as the author
										if (\Jkphl\Utility\Url::instance($hCardUrl, true, $authorProfileUrl) == $authorUrl) {
											return $hCard;
										}
									}
								}
							}
						} catch(\Jkphl\Micrometa\Parser\Microformats2\Exception $e) {}
						
						$hCards[]						= $hCard;
					}
				}
				
				// Gather all rel-me URLs on the author profile page
				$meUrls					= array();
				foreach ((array)$authorProfile->rel('me') as $meUrl) {
					if (strlen($meUrl)) {
						$meUrls[]		= strval(\Jkphl\Utility\Url::instance($meUrl, true, $authorProfileUrl)); 
					}
				}
				
				// Run through all the h-cards on the author profile page again
				foreach ($hCards as $hCard) {
					
					// Compare the rel-me URLS with every h-card URL
					foreach ((array)$hCard->url() as $hCardUrl) {
							
						// 3.1.3. In case of a match: Use this h-card as the author
						if (in_array(strval(\Jkphl\Utility\Url::instance($hCardUrl, true, $authorProfileUrl)), $meUrls)) {
							return $hCard;
						}
					}
				}
				
				// Final try: Run through all h-cards on the h-entry's page
				foreach ((array)$this->hCard() as $hCard) {
				
					// Compare the rel-author URL with every h-card URL
					foreach ((array)$hCard->url() as $hCardUrl) {
						
						// 3.1.4. In case of a match: Use this h-card as the author
						if ($authorUrl == strval(\Jkphl\Utility\Url::instance($hCardUrl, true, $authorProfileUrl))) {
							return $hCard;
						}
					}
				}
			}
			
		} catch (\Jkphl\Micrometa\Parser\Microformats2\Exception $exception) {}
		
		return null;
	}
	
	/**
	 * Load and extract an external author definition
	 * 
	 * @return NULL|\Jkphl\Micrometa\Item	Author micro information item
	 * @deprecated 							Will be dropped in favour of the authorship algorithm, see author()
	 */
	public function externalAuthor() {
		$author							= null;
		$rels							= $this->rels();
		if (!empty($rels['author']) && is_array($rels['author'])) {
			foreach ($rels['author'] as $authorProfileUrl) {
				$authorProfile			= new self($authorProfileUrl);
				$authorItem				= $authorProfile->item('http://schema.org/Person', 'http://data-vocabulary.org/Person', 'h-card');
				if ($authorItem instanceof \Jkphl\Micrometa\Item) {
					$author				= $authorItem;
					break;
				}
			}
		}
		return $author;
	}
	
	/**
	 * Return an object representation of the embedded micro information
	 * 
	 * @return \stdClass				Object representation
	 */
	public function toObject() {
		if (!$this->_parsed) {
			$this->parse();
		}
		
		$result							= (object)array(
			'items'						=> array(),
			'rels'						=> $this->_result->rels,
			'alternates'				=> $this->_result->alternates,
		);
		
		/* @var $item \Jkphl\Micrometa\Item */
		foreach ($this->_result->items as $item) {
			$result->items[]			= $item->toObject();
		}
		
		return $result;
	}
	
	/**
	 * Return a JSON representation of the embedded micro information
	 * 
	 * @param \boolean $beautify		Beautify the JSON output (available since PHP 5.4)
	 * @return \string					JSON representation
	 */
	public function toJSON($beautify = false) {
		$options						= 0;
		if ($beautify && version_compare(PHP_VERSION, '5.4', '>=')) {
			$options					|= JSON_PRETTY_PRINT;
		}
		return json_encode($this->toObject(), $options);
	}
	
	/************************************************************************************************
	 * PRIVATE METHODS
	 ***********************************************************************************************/
	
	/**
	 * Request an URL via GET (HTTP 1.1)
	 * 
	 * @param \string $url				Remote URL
	 * @return \string					Response content
	 */
	protected function _getUrl($url) {
		
		// If cURL is available
		if (extension_loaded('curl')) {
			$curl						= curl_init($url);
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_ENCODING		=> '',
				CURLOPT_USERAGENT		=> 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.466.4 Safari/534.3',
				CURLOPT_AUTOREFERER		=> true,
				CURLOPT_CONNECTTIMEOUT	=> 120,
				CURLOPT_TIMEOUT			=> 120,
				CURLOPT_MAXREDIRS		=> 10,
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_FOLLOWLOCATION	=> true,
				CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
			));
			$response					= curl_exec($curl);
			curl_close($curl);
			
		// Else: Try via stream wrappers
		} else {
			$opts						= array(
				'http'					=> array(
					'method'			=> 'GET',
					'protocol_version'	=> 1.1,
					'user_agent'		=> 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.466.4 Safari/534.3',
					'max_redirects'		=> 10,
					'timeout'			=> 120,
					'header'			=> "Accept-language: en\r\n",
				)
			);
			$context					= stream_context_create($opts);
			$response					= @file_get_contents($url, false, $context);
		}
		
		return $response;
	}
	
	/************************************************************************************************
	 * MAGIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Generic caller
	 * 
	 * Returns all microformats2 items of a spefic type (or a particular index out of this list)
	 * 
	 * @param \string $method										Method name (lowerCamelCased microformats2 item type)
	 * @param \array $arguments										List of arguments, of which the first is interpreted as list index (NULL = return the complete list)
	 * @return \array|\Jkphl\Micrometa\Item							List of microformats2 items or a single microformats2 item
	 * @throws \Jkphl\Micrometa\Parser\Microformats2\Exception		If it's not a valid microformats2 vocable
	 * @throws \Jkphl\Micrometa\Parser\Microformats2\Exception		If the item index is out of range
	 */
	public function __call($method, array $arguments) {
		$mf2ItemType					= \Jkphl\Micrometa\Parser\Microformats2::decamelize($method);
		$mf2Items						= $this->items($mf2ItemType);
		$mf2ItemIndex					= count($arguments) ? intval($arguments[0]) : null;
		
		// If the complete item list is to be returned
		if ($mf2ItemIndex === null) {
			return $mf2Items;
			
		// Else: If the requested item index is out of range: Error
		} elseif (($mf2ItemIndex < 0) || ($mf2ItemIndex > count($mf2Items) - 1)) {
			throw new \Jkphl\Micrometa\Parser\Microformats2\Exception(sprintf(\Jkphl\Micrometa\Parser\Microformats2\Exception::INDEX_OUT_OF_RANGE_STR, $mf2ItemIndex), \Jkphl\Micrometa\Parser\Microformats2\Exception::INDEX_OUT_OF_RANGE);
			
		// Else: Return the requested item index
		} else {
			return $mf2Items[$mf2ItemIndex];
		}
	}
	
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
	 * @param \string $key				Property name (lowerCamelCased microformats2 item type)
	 * @return \Jkphl\Micrometa\Item	microformats2 item
	 */
	public function __get($key) {
		return $this->$key(0);
	}
	
	/************************************************************************************************
	 * STATIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Instance constructor
	 * 
	 * @param \string $url				Resource document URL
	 * @param \string $source			Resource document source code
	 * @return \Jkphl\Micrometa			Micrometa parser object
	 */
	public static function instance($url, $source = null) {
		return new self($url, $source);
	}
}