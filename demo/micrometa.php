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
            $html .= '<h3><span class="item-type item-type-'.$object->parser.'">'.implode(
                    '</span> + <span class="item-type">', array_map('htmlspecialchars', $object->types)
                ).'</span> <span class="item-id">[ID = '.htmlspecialchars(
                    $object->id ? $object->id : 'NULL'
                ).']</span></h3>';
            if (strlen($object->value)) {
                $html .= '<div class="item-value">'.htmlspecialchars($object->value).'</div>';
            }
            if (count($object->properties)) {
                $html .= '<dl class="item-properties">';
                foreach ($object->properties as $property => $values) {
                    $html .= '<dt>'.htmlspecialchars($property).'</dt>';
                    $html .= '<dd>'.tree($values, in_array($property, \Jkphl\Micrometa\Item::$urlProperties)).'</dd>';
                }
                $html .= '</dl>';
            }
            if (count($object->children)) {
                $html .= '<dl class="item-children">';
                $html .= '<dt>children</dt>';
                $html .= '<dd>'.tree($object->children, false).'</dd>';
                $html .= '</dl>';
            }

        } else {
            $html .= '<dl class="object">';
            foreach (get_object_vars($object) as $property => $values) {
                $html .= '<dt>'.htmlspecialchars($property).'</dt>';
                $html .= '<dd>'.tree(
                        $values, $link || in_array(
                            $property, array_merge(\Jkphl\Micrometa\Item::$urlProperties, array('rels'))
                        )
                    ).'</dd>';
            }
            $html .= '</dl>';
        }

        // Else: If it's an (ordered) list
    } elseif (is_array($object)) {
        $html .= '<ol>';
        foreach ($object as $value) {
            $value = tree($value, $link || in_array($value, array('rels')));
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

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Micrometa parser test page</title>
        <style type="text/css">
            body {
                padding: 2em;
                margin: 0;
                color: #333;
                background: #fafafa;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 1em;
                line-height: 1.4
            }

            h1 {
                margin-top: 0;
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
            }

            legend {
                padding: 0 .5em;
                font-weight: bold;
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
                line-height: 32px;
            }

            dt {
                display: block;
                float: left;
                width: 6em;
                margin: 0;
                font-weight: bold;
                line-height: 32px;
                font-size: small;
            }

            dd {
                overflow: hidden;
                display: block;
                margin: 0;
            }

            dd dt {
                width: 10em;
            }

            ol {
                margin: 0 0 0 2em;
                padding: 0;
            }

            li {
                margin: 0;
            }

            li:last-child .item-properties .item-properties {
                margin-bottom: 0;
            }

            h3 {
                padding: 0;
                margin: 0;
                font-weight: bold;
                font-size: medium;
            }

            .item-properties {
                margin-bottom: 2em;
            }

            .item-type {
                display: inline-block;
                color: #090;
                padding-right: 26px;
                background-size: contain;
                background-position: right center;
                background-repeat: no-repeat;
                height: 20px;
                line-height: 20px;
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
                margin-bottom: 2em;
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
                    <label><span>URL</span><input type="url" name="url" value="<?php echo htmlspecialchars($url); ?>"
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
                    <input type="submit" name="microdata" value="Fetch &amp; parse URL"/>
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
                    // Include the Composer autoloader
                    if (@is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
                        require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

                        // Exit on failure
                    } else {
                        die ('<p style="font-weight:bold;color:red">Please follow the <a href="https://github.com/jkphl/micrometa#dependencies" target="_blank">instructions</a> to install the additional libraries that micrometa is depending on</p>');
                    }
                    require_once dirname(
                            __DIR__
                        ).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Jkphl'.DIRECTORY_SEPARATOR.'Micrometa.php';
                    $micrometa = \Jkphl\Micrometa::instance(trim($url));
                    if ($data == 'author') {
                        $micrometa = $micrometa->author();
                    }

                    if (!$micrometa):
                        ?>The requested micro information could not be found embedded into this document.<?php
                    elseif ($format == 'json'):
                        ?>
                        <pre><?php echo htmlspecialchars($micrometa->toJSON(true)); ?></pre><?php
                    else:
                        echo tree($micrometa->toObject());
                    endif;

                    ?></fieldset><?php

                endif;

                ?></form>
        </article>
        <footer>
            <p>This demo page is part of the <a href="https://github.com/jkphl/micrometa" target="_blank">micrometa
                    parser</a>
                package | Copyright © 2016 Joschi Kuphal &lt;<a href="mailto:joschi@kuphal.net">joschi@kuphal.net</a>&gt;
                / <a
                    href="https://twitter.com/jkphl" target="_blank">@jkphl</a></p>
        </footer>
    </body>
</html>
