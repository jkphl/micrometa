<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure\Parser
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

namespace Jkphl\Micrometa\Infrastructure\Parser;

use Jkphl\Micrometa\Application\Contract\ParsingResultInterface;
use Jkphl\Micrometa\Ports\Format;

/**
 * Link rel parser
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class LinkRel extends AbstractParser
{
    /**
     * Format
     *
     * @var int
     */
    const FORMAT = Format::LINK_REL;

    /**
     * Parse a DOM document
     *
     * @param \DOMDocument $dom DOM Document
     * @return ParsingResultInterface Micro information items
     */
    public function parseDom(\DOMDocument $dom)
    {
        $this->logger->info('Running parser: '.(new \ReflectionClass(__CLASS__))->getShortName());
        $items = [];

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('html', self::HTML_PROFILE_URI);

        // Run through all <link> elements with a `rel` attribute
        /** @var \DOMElement $linkRel */
        foreach ($xpath->query('//*[local-name(.) = "link" or local-name(.) = "a"][@rel]') as $linkRel) {
            $item = new \stdClass();

            // Collect the item types
            $item->type = [];
            foreach (preg_split('/\040+/', $linkRel->getAttribute('rel')) as $rel) {
                $item->type[] = (object)['name' => $rel, 'profile' => self::HTML_PROFILE_URI];
            }

            // Get the item ID (if any)
            if ($linkRel->hasAttribute('id')) {
                $item->id = $linkRel->getAttribute('id');
            }

            // Run through all item attributes
            $item->properties = [];
            /**
             * @var string $attributeName Attribute name
             * @var \DOMAttr $attribute Attribute
             */
            foreach ($linkRel->attributes as $attributeName => $attribute) {
                if (!in_array($attributeName, ['rel', 'id'])) {
                    $profile = $attribute->lookupNamespaceUri($attribute->prefix ?: null);
                    $item->properties[] = (object)[
                        'name' => $attributeName,
                        'profile' => $profile,
                        'values' => $this->parseAttributeValue($profile, $attributeName, $attribute->value),
                    ];
                }
            }

            $items[] = $item;
        }

        return new ParsingResult(self::FORMAT, $items);
    }

    /**
     * Parse an attribute value
     *
     * @param string $profile Profile
     * @param string $attribute Attribute name
     * @param string $value Attribute value
     * @return array Attribute values
     */
    protected function parseAttributeValue($profile, $attribute, $value)
    {
        // If it's a HTML attribute
        if ($profile == LinkRel::HTML_PROFILE_URI) {
            switch ($attribute) {
                // Space delimited lists
                case 'sizes':
                    return array_filter(preg_split('/\040+/', $value));
                // Space or comma delimited lists
                case 'charset':
                    return array_filter(preg_split('/[,\040]+/', $value));
            }
        }

        return [$value];
    }
}
