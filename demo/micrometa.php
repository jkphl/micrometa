<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package Jkphl_Micrometa
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

use Jkphl\Micrometa\Parser\Microformats2;
use Jkphl\Micrometa\Parser\Microdata;
use Jkphl\Micrometa\Parser\JsonLD;

// Include the Composer autoloader
if (@is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
    require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

    // Exit on failure
} else {
    die ('<p style="font-weight:bold;color:red">Please follow the <a href="https://github.com/jkphl/micrometa#dependencies" target="_blank">instructions</a> to install the additional libraries that micrometa is depending on</p>');
}

/**
 * Output a microdata object representation as tree
 *
 * @param \stdClass $object Object
 * @param \boolean $link Link values
 * @return \string                    HTML
 */
function tree($object, $link = false)
{
    $html = '';

    // If it's a true object
    if ($object instanceof \stdClass) {

        // If it's a micro information item
        if (property_exists($object, 'types') &&
            property_exists($object, 'id') &&
            property_exists($object, 'value') &&
            property_exists($object, 'properties') &&
            property_exists($object, 'parser')
        ) {
            $html .= '<details>';
            $html .= '<summary class="item-type-'.$object->parser.'"><h3><span class="item-type">'.implode(
                    '</span> + <span class="item-type">', array_map('htmlspecialchars', $object->types)
                ).'</span> <span class="item-id">[ID = '.htmlspecialchars(
                    $object->id ? $object->id : 'NULL'
                ).']</span></h3></summary>';
            if (strlen($object->value)) {
                $html .= '<div class="item-value">'.htmlspecialchars($object->value).'</div>';
            }
            if (count($object->properties)) {
                $html .= '<dl class="item-properties">';
                foreach ($object->properties as $property => $values) {
                    $html .= '<dt title="'.htmlspecialchars($property).'">'.htmlspecialchars(pathinfo(parse_url($property, PHP_URL_PATH), PATHINFO_FILENAME)).'</dt>';
                    $html .= '<dd>'.tree($values, in_array($property, \Jkphl\Micrometa\Item::$urlProperties)).'</dd>';
                }
                $html .= '</dl>';
            }
            if (count($object->children)) {
                $html .= '<dl class="item-children">';
                $html .= '<dt title="children">children</dt>';
                $html .= '<dd>'.tree($object->children, false).'</dd>';
                $html .= '</dl>';
            }

            $html .= '</details>';

        } else {
            $html .= '<dl class="object">';
            foreach (get_object_vars($object) as $property => $values) {
                $html .= '<dt title="'.htmlspecialchars($property).'">'.htmlspecialchars($property).'</dt>';
                $html .= '<dd>'.tree($values,
                        $link || in_array($property, \Jkphl\Micrometa\Item::$urlProperties)).'</dd>';
            }
            $html .= '</dl>';
        }

        // Else: If it's an (ordered) list
    } elseif (is_array($object)) {
        $html .= '<ol>';
        foreach ($object as $value) {
            $value = tree($value, $link);
            $html .= '<li>'.($link ? '<a href="'.$value.'" target="_blank">'.$value.'</a>' : $value).'</li>';
        }
        $html .= '</ol>';

        // Else: If it's an empty value
    } elseif (!strlen($object)) {
        $html .= '—';

        // Else: It's a scalar
    } else {
        $html .= htmlspecialchars($object);
    }

    return $html;
}

$url = empty($_POST['url']) ? (empty($_GET['url']) ? '' : $_GET['url']) : $_POST['url'];
$data = empty($_POST['data']) ? (empty($_GET['data']) ? '' : $_GET['data']) : $_POST['data'];
$format = empty($_POST['format']) ? (empty($_GET['format']) ? '' : $_GET['format']) : $_POST['format'];
$defaultParser = array(
    Microformats2::NAME => Microformats2::PARSE,
    Microdata::NAME => Microdata::PARSE,
    JsonLD::NAME => JsonLD::PARSE
);
$parser = empty($_POST['parser']) ? (empty($_GET['parser']) ? $defaultParser : $_GET['parser']) : $_POST['parser'];
$parser = array_map('intval', $parser);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Micrometa parser test page</title>
        <style type="text/css">
            body {
                padding: 2em;
                margin: 0;
                color: #222;
                background: #eee;
                line-height: 1.4
            }

            body, input, select {
                font-family: Arial, Helvetica, sans-serif;
                font-size: medium;
            }

            h1, h2 {
                padding: 0;
                margin: 0;
            }

            h2 {
                font-size: larger;
                display: inline;
            }

            h3 {
                font-size: medium;
                display: inline;
            }

            details, summary {
                display: block;
            }

            details {
                margin-bottom: 1em;
            }

            summary {
                cursor: pointer;
                padding: 0.2em .5em;
                background-size: 20px;
                background-position: right center;
                background-repeat: no-repeat;
            }

            .main > summary {
                margin-top: 0;
                background-color: #090;
                color: #fff;
            }

            input, select {
                border: 1px solid #ccc;
                padding: .2em;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box
            }

            input[type=url] {
                width: 20em;
            }

            input[type=submit], label {
                cursor: pointer;
            }

            label, label span {
                margin-right: .5em;
            }

            fieldset {
                border: 2px solid #ccc;
                padding: 1em;
                margin: 3em 0;
                background-color: #fff;
            }

            fieldset div + div {
                margin-top: 1em;
            }

            legend {
                padding: .2em .5em;
                background-color: #ccc;
                color: #222;
            }

            legend a {
                color: #222;
            }

            article {
                width: 60em;
            }

            footer, .hint {
                font-size: 80%;
                font-style: italic;
            }

            pre {
                white-space: pre-wrap;
            }

            .hint {
                margin: 0 0 1em 0;
            }

            dl {
                margin: 0;
                padding: 0;
                position: relative;
                line-height: 1.2rem;
            }

            dt {
                display: block;
                float: left;
                width: 6rem;
                margin: .5rem 0 0 0;
                font-size: small;
                color: #090;
                cursor: help;
                overflow: hidden;
            }

            dt:first-child {
                margin-top: 0;
            }

            dd {
                overflow: hidden;
                display: block;
                margin: .5rem 0 0 7rem;
            }

            ol { counter-reset: item; }
            ol li:before {
                content: counter(item) ".";
                counter-increment: item;
                color: #090;
                font-size: small;
                position: absolute;
                right: 100%;
                text-align: right;
                line-height: 1.2rem;
                padding-right: 1em;
            }

            .rel ol, .alternate ol {
                margin-top: 0;
                margin-bottom: 0;
                padding-left: 2rem;
            }

            li {
                margin: 0;
                color: #222;
                display: block;
                position: relative;
            }

            li:last-child .item-properties .item-properties {
                margin-bottom: 0;
            }

            .item-properties {
                margin-bottom: 2em;
            }

            .item-type {
                display: inline-block;
                color: #090;
            }

            .item-type-mf2 {
                background-image: url('logos/mf2.svg');
            }

            .item-type-microdata {
                background-image: url('logos/microdata.svg');
            }

            .item-type-json-ld {
                background-image: url('logos/json-ld.svg');
            }

            .item-id {
                color: #aaa;
                font-weight: normal;
                margin-left: .5em;
                font-size: x-small;
            }

            .item-value {
                font-style: italic;
                margin: 0 0 1em 0;
                line-height: 1.2;
                color: #888;
            }

            .object {
                margin-top: .5rem;
                padding-bottom: .5rem;
            }

            .alternate .object {
                border-bottom: 1px solid #ccc;
            }
        </style>
    </head>
    <body>
        <article>
            <h1>Micrometa parser demo page</h1>
            <p>This demo page can be used to fetch a remote document and parse it for embedded micro information. You
                can select
                between two output styles and whether you want to print all micro information embedded into the document
                or
                extract the document's author according to the Microformats2 <a
                    href="http://indiewebcamp.com/authorship"
                    target="_blank">authorship algorithm</a></p>
            <form method="post">
                <fieldset>
                    <legend>Enter an URL to be fetched &amp; examined</legend>
                    <div>
                        <label><span>URL</span><input type="url" name="url"
                                                      value="<?php echo htmlspecialchars($url); ?>"
                                                      placeholder="http://" required="required"/></label>
                        <label><span>Data</span><select name="data">
                                <option value="all"<?php if ($data == 'all') {
                                    echo ' selected="selected"';
                                } ?>>All
                                </option>
                                <option value="author"<?php if ($data == 'author') {
                                    echo ' selected="selected"';
                                } ?>>Author
                                </option>
                            </select></label>
                        <label><span>Format</span><select name="format">
                                <option value="tree"<?php if ($format == 'tree') {
                                    echo ' selected="selected"';
                                } ?>>Tree
                                </option>
                                <option value="json"<?php if ($format == 'json') {
                                    echo ' selected="selected"';
                                } ?>>JSON
                                </option>
                            </select></label>
                    </div>
                    <div>
                        Extract with parsers
                        <label><input type="checkbox" name="parser[<?= Microformats2::NAME; ?>]"
                                      value="<?= Microformats2::PARSE; ?>"<?= empty($parser[Microformats2::NAME]) ? '' : ' checked="checked"'; ?>/>
                            Microformats 1+2</label>
                        <label><input type="checkbox" name="parser[<?= Microdata::NAME; ?>]"
                                      value="<?= Microdata::PARSE; ?>"<?= empty($parser[Microdata::NAME]) ? '' : ' checked="checked"'; ?>/>
                            HTML Microdata</label>
                        <label><input type="checkbox" name="parser[<?= JsonLD::NAME; ?>]"
                                      value="<?= JsonLD::PARSE; ?>"<?= empty($parser[JsonLD::NAME]) ? '' : ' checked="checked"'; ?>/>
                            JSON-LD</label>
                        <input type="submit" name="microdata" value="Fetch &amp; parse URL"/>
                    </div>
                </fieldset><?php

                if (!empty($_POST['microdata']) && strlen($url)):

                    ?>
                    <fieldset>
                    <legend>Micro information embedded into <a href="<?php echo htmlspecialchars($url); ?>"
                                                               target="_blank"><?php echo htmlspecialchars($url); ?></a>
                    </legend><?php
                    if (version_compare(PHP_VERSION, '5.4', '<')):
                        ?><p class="hint">Unfortunately JSON pretty-printing is only available with PHP 5.4+.</p><?php
                    endif;

                    require_once dirname(
                            __DIR__
                        ).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Jkphl'.DIRECTORY_SEPARATOR.'Micrometa.php';
                    $micrometa = \Jkphl\Micrometa::instance(trim($url), null, array_sum($parser));
                    if ($data == 'author') {
                        $micrometa = $micrometa->author();
                    }

                    if (!$micrometa):
                        ?>The requested micro information could not be found embedded into this document.<?php
                    elseif ($format == 'json'):
                        ?>
                        <pre><?php echo htmlspecialchars($micrometa->toJSON(true)); ?></pre><?php
                    else:

                        $micro = $micrometa->toObject();

                        // Items
                        ?>
                        <details class="items main" open="open">
                        <summary><h2>Items</h2></summary><?php

                        echo tree($micro->items);

                        ?></details><?php

                        // Rels
                        ?>
                        <details class="rel main">
                        <summary><h2>Related resources</h2></summary><?php

                        echo tree($micro->rels, true);

                        ?></details><?php

                        // Alternates
                        ?>
                        <details class="alternate main">
                        <summary><h2>Alternate representations</h2></summary><?php

                        echo tree($micro->alternates);

                        ?></details><?php

                    endif;

                    ?></fieldset><?php

                endif;

                ?></form>
        </article>
        <footer>
            <p>This demo page is part of the <a href="https://github.com/jkphl/micrometa" target="_blank">micrometa
                    parser</a>
                package | Copyright © 2017 Joschi Kuphal &lt;<a href="mailto:joschi@kuphal.net">joschi@kuphal.net</a>&gt;
                / <a
                    href="https://twitter.com/jkphl" target="_blank">@jkphl</a></p>
        </footer>
    </body>
</html>
