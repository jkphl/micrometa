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

namespace Jkphl\Micrometa\Parser\Microdata;

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

/**
 * Extended DOM document
 *
 * @category	Jkphl
 * @package		Jkphl_Micrometa
 * @author		Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright	Copyright © 2015 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license		http://opensource.org/licenses/MIT	The MIT License (MIT)
 * @link		https://github.com/euskadi31/Microdata
 */
class Document extends \DOMDocument {
    /**
     * XPath operator
     * 
     * @var \DOMXPath
     */
    protected $_xpath = null;
    
    /************************************************************************************************
     * PUBLIC METHODS
     ***********************************************************************************************/
    
    /**
     * Instanciate and return an XPath operator for this document
     * 
     * @return \DOMXPath            XPath operator
     */
    public function xpath() {
        if ($this->_xpath === null) {
            $this->_xpath = new \DOMXPath($this);
        }
        return $this->_xpath;
    }
    
    /**
     * Return top level microdata elements
     * 
     * @return \DOMNodeList         Top level microdata elements
     */
    public function topLevelElements() {
        return $this->xpath()->query('//*[@itemscope and not(@itemprop)][count(ancestor::*[@itemscope]) = 0]');
    }
}