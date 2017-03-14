<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Infrastructure
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

namespace Jkphl\Micromoeta\Tests\Infrastructure {

    use Jkphl\Micrometa\Infrastructure\Factory\DocumentFactory;

    /**
     * Document factory test
     *
     * @package Jkphl\Micrometa
     * @subpackage Jkphl\Micromoeta\Tests
     */
    class DocumentFactoryTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * Valid local test HTML document
         *
         * @var string
         */
        const VALID_HTML_URL = 'http://localhost:1349/valid-test.html';
        /**
         * Invalid local test document
         *
         * @var string
         */
        const INVALID_DOCUMENT_URL = 'http://localhost:1349/invalid-test.html';
        /**
         * Non-existing local test document
         *
         * @var string
         */
        const NONEXISTING_DOCUMENT_URL = 'http://localhost:1349/none';

        /**
         * Test the HTML document instantiation via a HTTP client
         */
        public function testDocumentCreationViaHttpClient()
        {
            $dom = DocumentFactory::createFromUri(self::VALID_HTML_URL);
            $this->assertInstanceOf(\DOMDocument::class, $dom);
        }

        /**
         * Test the HTML document instantiation via the PHP stream wrapper
         */
        public function testDocumentCreationViaStreamWrapper()
        {
            putenv('MOCK_EXTENSION_LOADED=1');
            $dom = DocumentFactory::createFromUri(self::VALID_HTML_URL);
            $this->assertInstanceOf(\DOMDocument::class, $dom);
            putenv('MOCK_EXTENSION_LOADED=');
        }

        /**
         * Test an invalid document
         *
         * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
         */
        public function testDocumentCreationWithInvalidDocument()
        {
            DocumentFactory::createFromUri(self::INVALID_DOCUMENT_URL);
        }

        /**
         * Test a malformed URL
         *
         * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
         */
        public function testDocumentCreationWithMalformedUrl()
        {
            DocumentFactory::createFromUri('test://');
        }

        /**
         * Test a non-existing document
         *
         * @expectedException \Jkphl\Micrometa\Ports\Exceptions\RuntimeException
         */
        public function testDocumentCreationWithNonExistingDocument()
        {
            DocumentFactory::createFromUri(self::NONEXISTING_DOCUMENT_URL);
        }
    }
}

namespace Jkphl\Micrometa\Infrastructure\Factory {

    /**
     * Find out whether an extension is loaded
     *
     * @link http://php.net/manual/en/function.extension-loaded.php
     * @param string $name The extension name
     * @return bool true if the extension identified by name is loaded, false otherwise
     */
    function extension_loaded($name)
    {
        return getenv('MOCK_EXTENSION_LOADED') ? false : \extension_loaded($name);
    }
}
