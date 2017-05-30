<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
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

use Jkphl\Micrometa\Application\Value\AlternateValues;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Ports\Item\ItemInterface;

/**
 * Render a list of items
 *
 * @param ItemInterface[] $items Items
 * @return string Rendered list of items
 */
function renderItems(array $items)
{
    $html = '<ol>';
    $html .= implode('', array_map('renderItem', $items));
    $html .= '</ol>';
    return $html;
}

/**
 * Recursively render an item
 *
 * @param ItemInterface $item Item
 * @return string Rendered item
 */
function renderItem(ItemInterface $item)
{
    $types = array_map(
        function ($type) {
            return '<abbr title="'.htmlspecialchars($type->profile.$type->name).'">'.
                htmlspecialchars($type->name).'</abbr>';
        },
        $item->getType()
    );

    $html = '<li><details>';
    $html .= '<summary class="item-type-'.$GLOBALS['parserSuffices'][$item->getFormat()].'">';
    $html .= '<h3><span class="item-type">'.implode('</span> + <span class="item-type">', $types).'</span>';
    $html .= '<span class="item-id">[ ID = '.htmlspecialchars($item->getId() ?: 'NULL');
    if ($item->getLanguage()) {
        $html .= ' | LANG = '.htmlspecialchars(strtoupper($item->getLanguage()) ?: 'NULL');
    }
    $html .= ' ]</span></h3>';
    $html .= '</summary>';


    // Item value
    $value = $item->getValue();
    if (strlen($value)) {
        $html .= '<div class="item-value">'.htmlspecialchars($value).'</div>';
    }

    // Item properties
    $properties = $item->getProperties();
    if (count($properties)) {
        $html .= '<dl class="item-properties">';
        foreach ($properties as $property => $values) {
            $html .= '<dt><abbr title="'.htmlspecialchars($property).'">';
            $html .= htmlspecialchars($property->name).'</abbr></dt>';
            $html .= '<dd>'.renderPropertyValues($values).'</dd>';
        }
        $html .= '</dl>';
    }

    // Nested children
    $children = $item->getItems();
    if (count($children)) {
        $html .= '<dl class="item-children">';
        $html .= '<dt title="children">children</dt>';
        $html .= '<dd>'.renderItems($children).'</dd>';
        $html .= '</dl>';
    }

    $html .= '</details></li>';
    return $html;
}

/**
 * Render a list of property values
 *
 * @param array $values Property values
 * @return string Rendered property values
 */
function renderPropertyValues(array $values)
{
    $html = '<ol>';
    $html .= implode('', array_map('renderPropertyValue', $values));
    $html .= '</ol>';
    return $html;
}

/**
 * Render a single property value
 *
 * @param string $value Property value
 * @return string Rendered property value
 */
function renderPropertyValue($value)
{
    // If the value is a nested item
    if ($value instanceof ItemInterface) {
        return renderItem($value);

        // Else: If the value is a string
    } elseif ($value instanceof StringValue) {
        return renderStringValue($value);

        // Else: If the value is alternating
    } elseif ($value instanceof AlternateValues) {
        return renderAltValues($value);
    }

    // Else: Empty value
    return '';
}

/**
 * Render a string value
 *
 * @param StringValue $value String Value
 * @return string Rendered string value
 */
function renderStringValue(StringValue $value)
{
    $language = strtoupper($value->getLanguage());
    $language = $language ?
        '<span class="item-id">[ LANG = '.htmlspecialchars($language ?: 'NULL').' ]</span> ' : '';
    if ((strpos($value, '://') !== false) && filter_var($value, FILTER_VALIDATE_URL)) {
        return '<li>'.$language.'<a href="'.htmlspecialchars($value)
            .'" target="_blank">'.htmlspecialchars($value).'</a></li>';
    }

    return '<li>'.$language.htmlspecialchars($value).'</li>';
}

/**
 * Render alternate values
 *
 * @param AlternateValues $value Alternate values
 * @return string Rendered alternate values
 */
function renderAltValues(AlternateValues $value)
{
    $html = '<li><dt>';
    foreach ($value as $key => $alternateValue) {
        $language = strtoupper($alternateValue->getLanguage());
        $language = $language ?
            '<span class="item-id">[ LANG = '.htmlspecialchars($language ?: 'NULL').' ]</span> ' : '';
        $html .= '<dt>'.htmlspecialchars($key).'</dt>';
        $html .= '<dd>'.$language.htmlspecialchars($alternateValue).'</dd>';
    }
    $html .= '</dt></ul>';
    return $html;
}
