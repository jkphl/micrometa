<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 * @author     Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

/**
 * Microformats factory
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class MicroformatsFactory
{
    /**
     * Microformats 2 profile URI
     *
     * @var string
     * @link http://microformats.org/wiki/microformats-2#
     */
    const MF2_PROFILE_URI = 'http://microformats.org/profile/';

    /**
     * Refine an item
     *
     * @param array $item Item
     *
     * @return \stdClass Refined Microformats item
     */
    protected static function createItem(array $item)
    {
        $microformatItem = ['type' => self::createTypes($item['type']), 'lang' => null];
        self::processProperties($item, $microformatItem);
        self::processValue($item, $microformatItem);
        self::processChildren($item, $microformatItem);

        return (object)$microformatItem;
    }

    /**
     * Refine the item types
     *
     * @param array $types Types
     *
     * @return array Refined types
     */
    protected static function createTypes(array $types)
    {
        return array_map(
            function($type) {
                return (object)['profile' => self::MF2_PROFILE_URI, 'name' => $type];
            },
            $types
        );
    }

    /**
     * Process the item properties
     *
     * @param array $item            Item
     * @param array $microformatItem Refined Microformats item
     */
    protected static function processProperties(array $item, array &$microformatItem)
    {
        // Create the properties (if any)
        if (isset($item['properties']) && is_array($item['properties'])) {
            $microformatItem['properties'] = self::createProperties($item['properties'], $microformatItem['lang']);
        }
    }

    /**
     * Refine the item properties
     *
     * @param array $properties Properties
     * @param string $lang      Item language
     *
     * @return array Refined properties
     */
    protected static function createProperties(array $properties, &$lang)
    {
        // Extract the language (if present)
        $properties = self::createLanguage($properties, $lang);

        $mfProperties = [];
        foreach ($properties as $propertyName => $propertyValues) {
            // Process property values
            if (is_array($propertyValues)) {
                $mfProperties[] = (object)[
                    'profile' => self::MF2_PROFILE_URI,
                    'name'    => $propertyName,
                    'values'  => self::createProperty($propertyValues)
                ];
            }
        }

        return $mfProperties;
    }

    /**
     * Extract a language value from a value list
     *
     * @param array $values Value list
     * @param string $lang  Language
     *
     * @return array Remaining values
     */
    protected static function createLanguage(array $values, &$lang)
    {
        // If this is an alternate values list
        if (isset($values['lang'])) {
            if (is_string($values['lang'])) {
                $lang = trim($values['lang']) ?: null;
            }
            unset($values['lang']);
        }

        return $values;
    }

    /**
     * Refine the item property values
     *
     * @param array $propertyValues Property values
     *
     * @return array Refined property values
     */
    protected static function createProperty(array $propertyValues)
    {
        return array_map(
            function($propertyValue) {
                if (is_array($propertyValue)) {
                    return isset($propertyValue['type']) ?
                        self::createItem($propertyValue) : self::tagLanguage($propertyValue);
                }

                return $propertyValue;
            },
            $propertyValues
        );
    }

    /**
     * Tag values with a language (if possible)
     *
     * @param array $values Values
     *
     * @return array Language tagged values
     */
    protected static function tagLanguage(array $values)
    {
        $lang   = null;
        $values = self::createLanguage($values, $lang);

        return $lang ? array_map(function($value) use ($lang) {
            return (object)['value' => $value, 'lang' => $lang];
        }, $values) : $values;
    }

    /**
     * Process the item value
     *
     * @param array $item            Item
     * @param array $microformatItem Refined Microformats item
     */
    protected static function processValue(array $item, array &$microformatItem)
    {
        if (isset($item['value'])) {
            $microformatItem['value'] = self::createValue($item['value']);
        }
    }

    /**
     * Refine the item value
     *
     * @param string $value Value
     *
     * @return string Refined value
     */
    protected static function createValue($value)
    {
        return $value;
    }

    /**
     * Process the nested item children
     *
     * @param array $item            Item
     * @param array $microformatItem Refined Microformats item
     */
    protected static function processChildren(array $item, array &$microformatItem)
    {
        if (isset($item['children']) && is_array($item['children'])) {
            $microformatItem['children'] = self::createFromParserResult($item['children']);
        }
    }

    /**
     * Refine and convert the Microformats parser result
     *
     * @param array $items Items
     *
     * @return array Refined items
     */
    public static function createFromParserResult(array $items)
    {
        return array_map([self::class, 'createItem'], $items);
    }
}
