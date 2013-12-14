<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 */

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

// Include the Composer autoloader
if (@is_file(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
	require_once dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
}

// Exit on failure
if (!@class_exists('\MicrodataPhp')) {
	die ((PHP_SAPI == 'cli') ?
		"\nPlease follow the instructions at https://github.com/jkphl/micrometa#dependencies to install the library containing the PHP class \"\MicrodataPhp\".\n\n" :
		'<p style="font-weight:bold;color:red">Please follow the <a href="https://github.com/jkphl/micrometa#dependencies" target="_blank">instructions</a> to install the library containing the PHP class "MicrodataPhp"</p>'
	);
}

/**
 * Extended MicrodataPHP parser
 * 
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 * @link		https://github.com/linclark/MicrodataPHP
 * @deprecated	Replaced with the euskadi31/microdata package
 */
class MicrodataPhp extends \MicrodataPhp {
	/**
	 * Original resource URL
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
	 * @param \Jkphl\Utility\Url|\string $url		Document URL
	 * @param \string $source						Optional: Document source code
	 * @return void
	 */
	public function __construct($url, $source = null) {
		$this->_url							= ($url instanceof \Jkphl\Utility\Url) ? $url : new \Jkphl\Utility\Url($url);
		$url								= strval($url);
		
		// If the source document has to be loaded by URL: Use the parent constructor
		if ($source === null) {
			parent::__construct($url);
			
		// Else: Load it from a HTML string
		} else {
			$dom							= new \MicrodataPhpDOMDocument($url);
			$dom->registerNodeClass('DOMDocument', 'MicrodataPhpDOMDocument');
			$dom->registerNodeClass('DOMElement', 'MicrodataPhpDOMElement');
			$dom->preserveWhiteSpace		= false;
			@$dom->loadHTML($source);
			$this->dom						= $dom;
		}
	}
	
	/**
	 * Retrieve and refine the contained micro information items
	 * 
	 * @return \array								Refined items
	 */
	public function items() {
		$items								= array();
		$microdata							= $this->obj();
		if (!empty($microdata->items) && is_array($microdata->items)) {
			foreach ($microdata->items as $data) {
				$items[]					= new \Jkphl\Micrometa\Item((array)$data, $this->_url);
			}
		}
		return $items;
	}
}