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

namespace Jkphl\Micrometa\Parser;

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

require_once __DIR__.DIRECTORY_SEPARATOR.'Microdata'.DIRECTORY_SEPARATOR.'Item.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Microdata'.DIRECTORY_SEPARATOR.'Document.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Microdata'.DIRECTORY_SEPARATOR.'Element.php';

/**
 * Extended Microdata parser
 * 
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 * @link		https://github.com/euskadi31/Microdata
 */
class Microdata {
	/**
	 * Original resource URL
	 * 
	 * @var \Jkphl\Utility\Url
	 */
	protected $_url = null;
	/**
	 * DOM
	 * 
	 * @var \Jkphl\Micrometa\Parser\Microdata\Document
	 */
	protected $_dom = null;
	/**
	 * Top-level microdata items
	 * 
	 * @var \array
	 */
	protected $_items = null;
	
	/************************************************************************************************
	 * PUBLIC METHODS
	 ***********************************************************************************************/
	
	/**
	 * Constructor
	 *
	 * @param \Jkphl\Utility\Url|\string $url		Document URL
	 * @param \string $source						Optional: Document source code
	 * @return void
	 */
	public function __construct($url, $source = null) {
		$this->_url							= ($url instanceof \Jkphl\Utility\Url) ? $url : new \Jkphl\Utility\Url($url);
		$url		    					= strval($url);
		$this->_dom                         = new \Jkphl\Micrometa\Parser\Microdata\Document();
		$this->_dom->registerNodeClass('DOMDocument', '\Jkphl\Micrometa\Parser\Microdata\Document');
		$this->_dom->registerNodeClass('DOMElement', '\Jkphl\Micrometa\Parser\Microdata\Element');
		$this->_dom ->preserveWhiteSpace	= false;
				
		// Load document from an URL ...
		if ($source === null) {
			@$this->_dom->loadHTMLFile($url);
			
		// ... or from an HTML string
		} else {
			@$this->_dom->loadHTML($source);
		}
	}
	
	/**
	 * Retrieve and refine the contained micro information items
	 * 
	 * @return \array								Refined items
	 */
	public function items() {
	    if ($this->_items === null) {
	       $this->_items                   = array();
	       $register                       = array();
	       
	       /* @var $element \Jkphl\Micrometa\Parser\Microdata\Element */
	       foreach ($this->_dom->topLevelElements() as $element) {
	           $this->_items[]             = $element->toItem($this->_url, $register);
	       }
	    }
	    
	    return $this->_items;
	}
}