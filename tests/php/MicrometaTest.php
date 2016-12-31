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

/**
 * Tests for the main Micrometa parser class
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class MicrometaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Prefix for test URLs
     *
     * @var \string
     */
    protected $_urlPrefix = 'https://cdn.rawgit.com/sandeepshetty/authorship-test-cases/master/';

    /**
     * Test a document without an h-card element
     *
     * @return void
     * @see http://indiewebcamp.com/authorship
     */
    public function testNoHcard()
    {
        $micrometa = \Jkphl\Micrometa::instance($this->_urlPrefix.'no_h-card.html');
        $this->assertNull($micrometa->author(), 'hallo');
    }

    /**
     * Test a document with an h-entry + embedded p-author / h-card
     *
     * @return void
     * @see http://indiewebcamp.com/authorship			Case 2
     */
    public function testPAuthor()
    {
        $micrometa = \Jkphl\Micrometa::instance($this->_urlPrefix.'h-entry_with_p-author.html');
        $author = $micrometa->author();
        $this->assertInstanceOf('\Jkphl\Micrometa\Parser\Microformats2\Item', $author);
        $this->assertJsonStringEqualsJsonString(
            $author->toJSON(),
            '{"id":null,"types":["h-card"],"value":"John Doe","properties":{"name":["John Doe"],"url":["http:\/\/example.com\/johndoe\/"],"photo":["http:\/\/www.gravatar.com\/avatar\/fd876f8cd6a58277fc664d47ea10ad19.jpg?s=80&d=mm"]},"children":[],"parser":"mf2"}'
        );
    }

    /**
     * Test a document with an h-entry + rel-author -> h-card with u-url == u-uid == URL
     *
     * @return void
     * @see http://indiewebcamp.com/authorship			Case 3.1
     */
    public function testRelAuthorUrlUidSelf()
    {
        $micrometa = \Jkphl\Micrometa::instance(
            $this->_urlPrefix.'h-entry_with_rel-author_pointing_to_h-card_with_u-url_equal_to_u-uid_equal_to_self.html'
        );
        $author = $micrometa->author();
        $this->assertInstanceOf('\Jkphl\Micrometa\Parser\Microformats2\Item', $author);
        $this->assertJsonStringEqualsJsonString(
            $author->toJSON(),
            '{"id":null,"types":["h-card"],"value":null,"properties":{"name":["John Doe"],"url":["https:\/\/cdn.rawgit.com\/sandeepshetty\/authorship-test-cases\/master\/h-card_with_u-url_equal_to_u-uid_equal_to_self.html"],"uid":["https:\/\/cdn.rawgit.com\/sandeepshetty\/authorship-test-cases\/master\/h-card_with_u-url_equal_to_u-uid_equal_to_self.html"],"photo":["http:\/\/www.gravatar.com\/avatar\/fd876f8cd6a58277fc664d47ea10ad19.jpg?s=80&d=mm"]},"children":[],"parser":"mf2"}'
        );
    }

    /**
     * Test a document with an h-entry + rel-author -> h-card with u-url == rel-me
     *
     * @return void
     * @see http://indiewebcamp.com/authorship			Case 3.1
     */
    public function testRelAuthorUrlMe()
    {
        $micrometa = \Jkphl\Micrometa::instance(
            $this->_urlPrefix.'h-entry_with_rel-author_pointing_to_h-card_with_u-url_that_is_also_rel-me.html'
        );
        $author = $micrometa->author();
        $this->assertInstanceOf('\Jkphl\Micrometa\Parser\Microformats2\Item', $author);
        $this->assertJsonStringEqualsJsonString(
            $author->toJSON(),
            '{"id":null,"types":["h-card"],"value":null,"properties":{"name":["John Doe"],"url":["https:\/\/cdn.rawgit.com\/sandeepshetty\/authorship-test-cases\/master\/h-card_with_u-url_that_is_also_rel-me.html"],"photo":["http:\/\/www.gravatar.com\/avatar\/fd876f8cd6a58277fc664d47ea10ad19.jpg?s=80&d=mm"]},"children":[],"parser":"mf2"}'
        );
    }

    /**
     * Test a document with an h-entry + h-card with u-url == rel-author
     *
     * @return void
     * @see http://indiewebcamp.com/authorship			Case 3.1
     */
    public function testRelAuthorHcardUrl()
    {
        $micrometa = \Jkphl\Micrometa::instance(
            $this->_urlPrefix.'h-entry_with_rel-author_and_h-card_with_u-url_pointing_to_rel-author_href.html'
        );
        $author = $micrometa->author();
        $this->assertInstanceOf('\Jkphl\Micrometa\Parser\Microformats2\Item', $author);
        $this->assertJsonStringEqualsJsonString(
            $author->toJSON(),
            '{"id":null,"types":["h-card"],"value":null,"properties":{"name":["John Doe"],"url":["https:\/\/cdn.rawgit.com\/sandeepshetty\/authorship-test-cases\/master\/no_h-card.html"],"photo":["http:\/\/www.gravatar.com\/avatar\/fd876f8cd6a58277fc664d47ea10ad19.jpg?s=80&d=mm"]},"children":[],"parser":"mf2"}'
        );
    }
}
