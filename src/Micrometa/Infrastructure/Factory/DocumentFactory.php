<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure\Factory
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

namespace Jkphl\Micrometa\Infrastructure\Factory;

use Guzzle\Common\Exception\InvalidArgumentException as GuzzleInvalidArgumentException;
use Guzzle\Common\Exception\RuntimeException as GuzzleRuntimeException;
use Guzzle\Http\Client;
use Guzzle\Http\Url;
use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Ports\Exceptions\RuntimeException;

/**
 * DOM document factory
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class DocumentFactory
{
    /**
     * HTML5 elements
     *
     * @var array
     */
    const HTML5 = [
        'a',
        'abbr',
        'acronym',
        'address',
        'applet',
        'area',
        'article',
        'aside',
        'audio',
        'b',
        'base',
        'basefont',
        'bdi',
        'bdo',
        'bgsound',
        'big',
        'blink',
        'blockquote',
        'body',
        'br',
        'button',
        'canvas',
        'caption',
        'center',
        'cite',
        'code',
        'col',
        'colgroup',
        'content',
        'data',
        'datalist',
        'dd',
        'decorator',
        'del',
        'details',
        'dfn',
        'dir',
        'div',
        'dl',
        'dt',
        'element',
        'em',
        'embed',
        'fieldset',
        'figcaption',
        'figure',
        'font',
        'footer',
        'form',
        'frame',
        'frameset',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'header',
        'hgroup',
        'hr',
        'html',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'isindex',
        'kbd',
        'keygen',
        'label',
        'legend',
        'li',
        'link',
        'listing',
        'main',
        'map',
        'mark',
        'marquee',
        'menu',
        'menuitem',
        'meta',
        'meter',
        'nav',
        'nobr',
        'noframes',
        'noscript',
        'object',
        'ol',
        'optgroup',
        'option',
        'output',
        'p',
        'param',
        'plaintext',
        'pre',
        'progress',
        'q',
        'rp',
        'rt',
        'ruby',
        's',
        'samp',
        'script',
        'section',
        'select',
        'shadow',
        'small',
        'source',
        'spacer',
        'span',
        'strike',
        'strong',
        'style',
        'sub',
        'summary',
        'sup',
        'table',
        'tbody',
        'td',
        'template',
        'textarea',
        'tfoot',
        'th',
        'thead',
        'time',
        'title',
        'tr',
        'track',
        'tt',
        'u',
        'ul',
        'var',
        'video',
        'wbr',
        'xmp'
    ];

    /**
     * Create a DOM document from a URI
     *
     * @param string $url HTTP / HTTPS URL
     * @return \DOMDocument DOM document
     */
    public static function createFromUri($url)
    {
        return extension_loaded('curl') ? self::createViaHttpClient($url) : self::createViaStreamWrapper($url);
    }

    /**
     * Create a DOM document using a HTTP client implementation
     *
     * @param string $url HTTP / HTTPS URL
     * @return \DOMDocument DOM document
     * @throws RuntimeException If the request wasn't successful
     * @throws InvalidArgumentException If an argument was invalid
     * @throws RuntimeException If a runtime exception occurred
     */
    protected static function createViaHttpClient($url)
    {
        try {
            $guzzleUrl = Url::factory($url);
            $client = new Client($guzzleUrl, ['timeout' => 10.0]);
            $request = $client->get($guzzleUrl);
            $response = $client->send($request);
            return self::createFromString(strval($response->getBody()));

            // If an argument was invalid
        } catch (GuzzleInvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode());

            // If a runtime exception occurred
        } catch (GuzzleRuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a DOM document from a string
     *
     * @param string $str String
     * @return \DOMDocument DOM document
     */
    public static function createFromString($str)
    {
        $source = mb_convert_encoding($str, 'HTML-ENTITIES', mb_detect_encoding($str));
        $dom = new \DOMDocument();

        // Try to load the source as XML document first, then as HTML document
        if (!$dom->loadXML($source, LIBXML_NOWARNING | LIBXML_NOERROR)) {
            libxml_use_internal_errors(true);
            $dom->loadHTML($source, LIBXML_NOWARNING);
            $errors = libxml_get_errors();
            libxml_use_internal_errors(false);

            // Run through all errors
            /** @var \LibXMLError $error */
            foreach ($errors as $error) {
                if (($error->code != 801) ||
                    (
                        preg_match('/^tag\s+(\S+)\s+invalid$/', strtolower($error->message), $tag) &&
                        !in_array($tag[1], self::HTML5)
                    )
                ) {
                    throw new InvalidArgumentException(
                        sprintf(InvalidArgumentException::INVALID_DATA_SOURCE_STR, trim($error->message)),
                        InvalidArgumentException::INVALID_DATA_SOURCE
                    );
                }
            }
        }

        return $dom;
    }

    /**
     * Create a DOM document via the PHP stream wrapper
     *
     * @param string $url URL
     * @return \DOMDocument DOM document
     */
    protected static function createViaStreamWrapper($url)
    {
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
        return self::createFromString($response);
    }
}
