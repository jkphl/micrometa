<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa\Tests\Application;

use Jkphl\Micrometa\Application\Service\ExtractorService;
use Jkphl\Micrometa\Infrastructure\Factory\DocumentFactory;
use Jkphl\Micrometa\Infrastructure\Parser\RdfaLite;
use League\Uri\Schemes\Http;

/**
 * Extractor tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * RDFa Lite 1.1 HTML document
     *
     * @var string
     */
    const RDFA_LITE_HTML_URL = 'http://localhost:1349/article-rdfa-lite.html';

    /**
     * Test the RDFa Lite 1.1 extraction
     */
    public function testRdfaLiteExtraction()
    {
        // Create a DOM with RDFa Lite 1.1 markup
        $rdfaLite = file_get_contents(dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'article-rdfa-lite.html');
        $rdfaLiteDom = DocumentFactory::createFromString($rdfaLite);
        $this->assertInstanceOf(\DOMDocument::class, $rdfaLiteDom);

        // Create an RDFa Lite 1.1 parser
        $rdfaLiteUri = Http::createFromString(self::RDFA_LITE_HTML_URL);
        $rdfaLiteParser = new RdfaLite($rdfaLiteUri);
        $this->assertEquals($rdfaLiteUri, $rdfaLiteParser->getUri());

        // Create an extractor service
        $extractorService = new ExtractorService();
        $rdfaLiteItems = $extractorService->extract($rdfaLiteDom, $rdfaLiteParser);
        $this->assertTrue(is_array($rdfaLiteItems));
    }
}
