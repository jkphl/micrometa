<?php

namespace Jkphl\Micrometa\Parser;

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

if (!@class_exists('Mf2::Parser')) {
	$include	= dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'php-mf2'.DIRECTORY_SEPARATOR.'Mf2'.DIRECTORY_SEPARATOR.'Parser.php';
	if (!@is_file($include) || !@is_readable($include)) {
		die('Please see https://github.com/jkphl/micrometa/blob/master/lib/README.md for instructions on installing the "IndieWeb microformats-2 parser for PHP"');
	}
	require_once $include;
	unset($include);
}

/**
 * Extended Microformats2 parser
 * 
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @package jkphl_micrometa
 * @license http://opensource.org/licenses/MIT	The MIT License (MIT)
 */
class Microformats2 extends \Mf2\Parser {
	/**
	 * Original URL
	 *
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	
	/************************************************************************************************
	 * PUBLIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Constructor
	 *
	 * @param \DOMDocument|string $input			The data to parse. A string of HTML or a DOMDocument
	 * @param \Jkphl\Utility\Url|\string $url		Optional: The URL of the parsed document, for relative URL resolution
	 */
	public function __construct($input, $url = null) {
		$this->_url							= ($url instanceof \Jkphl\Utility\Url) ? $url : new \Jkphl\Utility\Url($url);
		$url								= strval($url);
		parent::__construct($input, $url);
	}
	
	/**
	 * Kicks off the parsing routine
	 * 
	 * If `$convertClassic` is set, any angle brackets in the results from non e-* properties
	 * will be HTML-encoded, bringing all output to the same level of encoding.
	 * 
	 * If a DOMElement is set as the $context, only descendants of that element will
	 * be parsed for microformats.
	 * 
	 * @param bool $convertClassic					Whether or not to html-encode non e-* properties. Defaults to false
	 * @param DOMElement $context					Optionall: An element from which to parse microformats
	 * @return array								An array containing all the µfs found in the current document
	 */
	public function parse($convertClassic = true, \DOMElement $context = null) {
		$results				= parent::parse($convertClassic, $context);
		$results['items']		= $this->_refineResults($results['items']);
		return $results;
	}
	
	/************************************************************************************************
	 * PRIVATE METHODS
	 ***********************************************************************************************/
	
	/**
	 * Refine micro information items
	 * 
	 * @param \array $results						Micro information items
	 * @return \array								Refined micro information items
	 */
	protected function _refineResults(array $results) {
		$refined				= array();
		
		// Run through all original parsing results
		foreach ($results as $data) {
			$refined[]			= new \Jkphl\Micrometa\Item($data, $this->_url);
		}
		
		return $refined;
	}
}