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

use Jkphl\Micrometa\Ports\Cache;use Jkphl\Micrometa\Ports\Format;use Jkphl\Micrometa\Ports\Item\ItemInterface;use Jkphl\Micrometa\Ports\Parser;use Monolog\Formatter\LineFormatter;use Monolog\Handler\TestHandler;use Monolog\Logger;use Symfony\Component\Cache\Adapter\FilesystemAdapter;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$parserSuffices = [
    Format::MICROFORMATS => 'mf2',
    Format::MICRODATA => 'microdata',
    Format::RDFA_LITE => 'rdfa-lite',
    Format::JSON_LD => 'json-ld',
    Format::LINK_REL => 'link-rel',
];

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

        }, $item->getType()
    );

    $html = '<li><details>';
    $html .= '<summary class="item-type-'.$GLOBALS['parserSuffices'][$item->getFormat()].'">';
    $html .= '<h3><span class="item-type">'.implode('</span> + <span class="item-type">', $types).'</span>';
    $html .= '<span class="item-id">[ID = '.htmlspecialchars($item->getId() ?: 'NULL');
    $html .= ' | LANGUAGE = '.htmlspecialchars(strtoupper($item->getLanguage()) ?: 'NULL').']</span></h3>';
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

            // TODO: Clunky! Values of getProperties() and getProperty() should be identical (IRI object > Ports item / string / array)
            $values = $item->getProperty($property);
            $html .= '<dt><abbr title="'.htmlspecialchars($property->profile.$property->name).'">'.htmlspecialchars(
                    $property->name
                ).'</abbr></dt>';
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
    if ($value instanceof ItemInterface) {
        return renderItem($value);
    } elseif (is_string($value)) {
        if ((strpos($value, '://') !== false) && filter_var($value, FILTER_VALIDATE_URL)) {
            return '<li><a href="'.htmlspecialchars($value).'" target="_blank">'.htmlspecialchars($value).'</a></li>';
        }

        return '<li>'.htmlspecialchars($value).'</li>';
    } elseif (is_array($value)) {
        $html = '<li><dt>';
        foreach ($value as $key => $alternateValue) {
            $html .= '<dt>'.htmlspecialchars($key).'</dt>';
            $html .= '<dd>'.htmlspecialchars($alternateValue).'</dd>';
        }
        $html .= '</dt></ul>';
        return $html;
    } else {
        return '';
    }
}

$params = array_merge($_GET, $_POST);
$url = empty($params['url']) ? '' : $params['url'];
$data = empty($params['data']) ? '' : $params['data'];
$output = empty($params['output']) ? 'tree' : $params['output'];

// Aggregate the parsers to use
if (empty($params['parser'])) {
    $formats = Format::ALL;
} else {
    $formats = 0;
    foreach ((empty($params['parser']) ? [] : (array)$params['parser']) as $parser) {
        $formats |= intval($parser);
    }
}

// Prepare the cache adapter
$cacheAdapter = new FilesystemAdapter('micrometa', 0, __DIR__.DIRECTORY_SEPARATOR.'cache');
Cache::setAdapter($cacheAdapter);

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Micrometa 2 demo page</title>
        <link rel="stylesheet" href="micrometa.css"/>
    </head>
    <body>
        <article>
            <h1>Micrometa 2 demo page</h1>
            <p>This demo page is part of the <a href="https://github.com/jkphl/micrometa" target="_blank">micrometa
                    parser</a> package and can be used to fetch a remote document and parse it for embedded micro
                information in several formats.</p>
            <p>JSON-LD parsing turns out to be rather time consuming, so a session
                persistent file cache is used to avoid fetching the involved contexts over and over again. Feel free to
                clear the cache in case of vocabulary updates.</p>
            <form method="post">
                <fieldset>
                    <legend> Enter an URL to be fetched &amp; examined</legend>
                    <div>
                        <label><span> URL</span><input type="url" name="url" value="<?= htmlspecialchars($url); ?>"
                                                       placeholder="https://"/></label>
                        <label><span> Format</span><select name="output">
                                <option value="tree" <?= ($output == 'tree') ? ' selected="selected"' : ''; ?>>Tree
                                </option>
                                <option value="json"<?= ($output == 'json') ? ' selected="selected"' : ''; ?>>JSON
                                </option>
                            </select></label>
                    </div>
                    <div>
                        <span>Parsers</span>
                        <label class="legend item-type-mf2"><input type="checkbox" name="parser[mf2]"
                                                                   value="<?= Format::MICROFORMATS ?>"
                                <?= ($formats & Format::MICROFORMATS) ? ' checked="checked"' : ''; ?>/> Microformats 1+2</label>
                        <label class="legend item-type-microdata"><input type="checkbox" name="parser[microdata]"
                                                                         value="<?= Format::MICRODATA; ?>"<?= ($formats & Format::MICRODATA) ? ' checked="checked"' : ''; ?>/>
                            HTML
                            Microdata</label>
                        <label class="legend item-type-rdfa-lite"><input type="checkbox" name="parser[rdfalite]"
                                                                         value="<?= Format::RDFA_LITE; ?>"<?= ($formats & Format::RDFA_LITE) ? ' checked="checked"' : ''; ?>/>
                            RDFa Lite 1.1</label>
                        <label class="legend item-type-link-rel"><input type="checkbox" name="parser[link-rel]"
                                                                        value="<?= Format::LINK_REL; ?>"
                                <?= ($formats & Format::LINK_REL) ? ' checked="checked"' : ''; ?>/>
                            LinkRel</label>
                        <label class="legend item-type-json-ld"><input type="checkbox" name="parser[json-ld]"
                                                                       value="<?= Format::JSON_LD; ?>"
                                <?= ($formats & Format::JSON_LD) ? ' checked="checked"' : ''; ?>/>
                            JSON-LD</label>
                    </div>
                    <div>
                        <input type="submit" name="microdata" value="Fetch &amp; parse URL" class="parse"/>
                        <input type="submit" name="clearcache" value="Clear JSON-LD vocabulary cache"/>
                    </div>
                </fieldset><?php

                if (!empty($params['microdata']) && strlen($url)):

                    ?>
                    <fieldset>
                        <legend>Micro information embedded into <a href="<?= htmlspecialchars($url); ?>"
                                                                   target="_blank"><?= htmlspecialchars($url); ?></a>
                        </legend><?php
                        if (version_compare(PHP_VERSION, '5.4', '<')):
                            ?><p class="hint">Unfortunately JSON pretty-printing is only available with PHP
                            5.4+.</p><?php
                        endif;

                        flush();

                        // Prepare the logger
                        $logHandler = new TestHandler();
                        $logHandler->setFormatter(new LineFormatter("%datetime% > %level_name% > %message%\n"));
                        $logger = new Logger('DEMO', [$logHandler]);

                        try {
                            // Create the parser
                            $micrometa = new Parser($formats, $logger);
                            $itemObjectModel = $micrometa($url);
                            $items = $itemObjectModel->getItems();

                            if (!count($items)):
                                ?>The document doesn't seem to have embedded micro information.<?php
                            elseif ($output == 'json'):
                                ?>
                                <pre><?= htmlspecialchars(
                                json_encode($itemObjectModel->toObject(), JSON_PRETTY_PRINT)
                            ); ?></pre><?php
                            else:

                                $micro = $rel = [];
                                /** @var ItemInterface $item */
                                foreach ($items as $item) {
                                    if ($item->getFormat() == Format::LINK_REL) {
                                        $rel[] = $item;
                                    } else {
                                        $micro[] = $item;
                                    }
                                }

                                // Micro information items
                                if (count($micro)):

                                    ?>
                                    <details class="items main" open="open">
                                    <summary><h2>Items</h2></summary><?php

                                    echo renderItems($micro);

                                    ?></details><?php

                                endif;

                                // LinkRel items
                                if (count($rel)):

                                    ?>
                                    <details class="items main" open="open">
                                    <summary><h2>LinkRel</h2></summary><?php

                                    echo renderItems($rel);

                                    ?></details><?php

                                endif;

                            endif;
                        } catch (\Exception $e) {
                            ?><h2 class="error"
                                  title="<?= htmlspecialchars(get_class($e).' ('.$e->getCode().')'); ?>"><?= htmlspecialchars($e->getMessage()); ?></h2>
                            <div class="error">
                            <pre class="error"><?= $e->getTraceAsString(); ?></pre></div><?php
                        }

                        ?></fieldset>
                    <fieldset>
                    <legend>Parsing &amp; processing log</legend>
                    <pre><?= htmlspecialchars($logHandler->getFormatter()
                            ->formatBatch($logHandler->getRecords())); ?></pre>
                    </fieldset><?php

                elseif (!empty($params['clearcache'])):

                    ?>
                    <fieldset>
                    <legend>Cache management</legend>
                    <p><?= $cacheAdapter->clear() ? 'The JSON-LD vocabulary cache has successfully been cleared.' : 'The JSON-LD vocabulary cache could not be cleared.'; ?></p>
                    </fieldset><?php

                endif;
                ?></form>
        </article>
        <footer>
            <p>
                Copyright © 2017 Joschi Kuphal &lt;<a href="mailto:joschi@kuphal.net">joschi@kuphal.net</a>&gt; / <a
                    href="https://twitter.com/jkphl" target="_blank">@jkphl</a></p>
        </footer>
    </body>
</html>
