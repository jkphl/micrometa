<?php

namespace Jkphl;

/***********************************************************************************
 *  The MIT License (MIT)
 *  
 *  Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

require_once __DIR__.DIRECTORY_SEPARATOR.'Utility'.DIRECTORY_SEPARATOR.'Url.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Item.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Parser'.DIRECTORY_SEPARATOR.'Microformats2.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Micrometa'.DIRECTORY_SEPARATOR.'Parser'.DIRECTORY_SEPARATOR.'Microdata.php';

/**
 * Micrometa parser
 * 
 * @author joschi
 * @package jkphl_micrometa
 * @license http://opensource.org/licenses/MIT	The MIT License (MIT)
 */
class Micrometa {
	/**
	 * Document URL
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	/**
	 * Document source code
	 * 
	 * @var \string
	 */
	protected $_source = null;
	/**
	 * DOM
	 * 
	 * @var \DOMDocument
	 */
	public $dom = null;
	/**
	 * XPath operator
	 * 
	 * @var \DOMXPath
	 */
	public $xpath = null;
	/**
	 * Document base URL
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	public $baseUrl = null;
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
	 * Contained microdata
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
	 * @param \string $url				Document URL
	 * @param \string $source			Document source code
	 * @return \Jkphl\Micrometa			Micrometa parser
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
			$this->baseUrl->resolve($this->_url);
			break;
		}
		
		// Set the focus node
		$this->_focus					= $this->dom->documentElement;
		libxml_use_internal_errors(false);
	}
	
	/**
	 * Set the focus element
	 * 
	 * @param \DOMElement $element		Focus element
	 * @return \DOMElement				Focus element
	 */
	public function focus(\DOMElement $element) {
		$this->_focus					= $element;
		$this->_parsed					= false;
		return $this->_focus;
	}
	
	/**
	 * Parse the document for contained microdata
	 * 
	 * @return \Jkphl\JkphlBlog\Utility\Microcontent		Self reference
	 */
	public function parse() {
		
		// Parse with the microformats2 parser
		$microformatsParser				= new \Jkphl\Micrometa\Parser\Microformats2($this->dom);
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
		
		$this->_parsed					= true;
	}

	/**
	 * Return a list of items
	 * 
	 * @param \string $type ...			Optional: Arbitrary number of item types
	 * @return \array					Item list
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
	 * Return the first item (of a specific type)
	 * 
	 * @param \string $type1				Optional: Arbitrary number of item types
	 * @param \string $type2
	 * ...
	 * @return \Jkphl\Micrometa\Item		First item
	 */
	public function item() {
		$items							= call_user_func_array(array($this, 'items'), func_get_args());
		return count($items) ? $items[0] : null;
	}
	
	/**
	 * Return all rel-values
	 * 
	 * @return \array						Rel-values			
	 */
	public function rels() {
		if (!$this->_parsed) {
			$this->parse();
		}
		
		return (array)$this->_result->rels;
	}
	
	/**
	 * Return all alternate-values
	 *
	 * @return \array						Alternate-values
	 */
	public function alternates() {
		if (!$this->_parsed) {
			$this->parse();
		}
	
		return (array)$this->_result->alternates;
	}
	
	/**
	 * Load and extract an external author definition
	 * 
	 * @return NULL|\Jkphl\Micrometa\Item	Author
	 */
	public function externalAuthor() {
		$author							= null;
		$rels							= $this->rels();
		if (!empty($rels['author']) && is_array($rels['author'])) {
			foreach ($rels['author'] as $authorProfileUrl) {
				$authorProfile			= new self($authorProfileUrl);
				$authorItem				= $authorProfile->item('http://schema.org/Person', 'h-card');
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
	 * @param \string $url				URL
	 * @return \string					Response
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
	 * STATIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Instance constructor
	 * 
	 * @param \string $url				Document URL
	 * @param \string $source			Document source code
	 * @return \Jkphl\Micrometa			Micrometa parser
	 */
	public static function instance($url, $source = null) {
		return new self($url, $source);
	}
}