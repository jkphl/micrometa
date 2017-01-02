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
                    $html .= '<dt title="'.htmlspecialchars($property).'">'.htmlspecialchars(pathinfo(parse_url($property,
                            PHP_URL_PATH), PATHINFO_FILENAME)).'</dt>';
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
            $properties = get_object_vars($object);
            ksort($properties);
            foreach ($properties as $property => $values) {
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
            $html .= '<li>'.$value.'</li>';
        }
        $html .= '</ol>';

        // Else: If it's an empty value
    } elseif (!strlen($object)) {
        $html .= '—';

        // Else: It's a scalar
    } else {
        $html .= $link ? '<a href="'.htmlspecialchars($object).'" target="_blank">'.htmlspecialchars($object).'</a>' : htmlspecialchars($object);
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

            details.main {
                margin-bottom: 1em;
            }

            summary {
                cursor: pointer;
                padding: 0.2em .5em;
                background-size: 20px;
                background-position: right center;
                background-repeat: no-repeat;
            }

            summary::-webkit-details-marker {
                display: none;
            }

            summary::before {
                content: '\23E9';
                padding-right: .6em;
            }

            details[open] > summary::before {
                content: '\23EC';
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
                vertical-align: middle;
            }

            input[type=url] {
                width: 20em;
            }

            input[type=submit], label {
                cursor: pointer;
            }

            input[type=submit] {
                margin-left: 1em;
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
                clear: left;
                width: 6rem;
                margin: .5rem 0 0 0;
                font-size: small;
                color: #090;
                cursor: help;
                overflow: hidden;
                white-space: nowrap;
            }

            dt:first-child {
                margin-top: 0;
            }

            dd {
                overflow: hidden;
                display: block;
                margin: .5rem 0 0 7rem;
            }

            ol {
                counter-reset: item;
            }

            ol li:before {
                display: inline-block;
                content: counter(item) ".";
                counter-increment: item;
                color: #090;
                font-size: x-small;
                position: absolute;
                right: 100%;
                text-align: right;
                padding-right: 1em;
                line-height: 22px;
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
                background-image: url('data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzOTAgNDAwIj48bGluZWFyR3JhZGllbnQgaWQ9ImEiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTMxNC44NTMzIiB5MT0iNjQ5LjkwMzMiIHgyPSItMzEzLjg1MjUiIHkyPSI2NTAuOTA0MSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgzMjAgMCAwIDMyMCAxMDA3NjMuMDc4IC0yMDc4OTkpIj48c3RvcCBvZmZzZXQ9IjAuMDUiIHN0b3AtY29sb3I9IiM1NkEyMzEiLz48c3RvcCBvZmZzZXQ9IjAuOTUiIHN0b3AtY29sb3I9IiMzQjc5MkQiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjYSkiIHN0cm9rZT0iI0ZGRiIgc3Ryb2tlLXdpZHRoPSIyMCIgZD0iTTcwIDcwaDIwMGMzMy4xMzggMCA2MCAyNi44NjMgNjAgNjB2MjAwYzAgMzMuMTM3LTI2Ljg2MiA2MC02MCA2MEg3MGMtMzMuMTM3IDAtNjAtMjYuODYzLTYwLTYwVjEzMGMwLTMzLjEzNyAyNi44NjMtNjAgNjAtNjB6Ii8+PGxpbmVhckdyYWRpZW50IGlkPSJiIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zMTQuNTAyMiIgeTE9IjY1MC40ODU0IiB4Mj0iLTMxMy41MDE0IiB5Mj0iNjUxLjQ4NjEiIGdyYWRpZW50VHJhbnNmb3JtPSJtYXRyaXgoMjQ1IDAgMCAyNDUgNzcxNzMuMDYzIC0xNTkzNDQpIj48c3RvcCBvZmZzZXQ9IjAuMDUiIHN0b3AtY29sb3I9IiM3MkJEMkQiLz48c3RvcCBvZmZzZXQ9IjAuOTUiIHN0b3AtY29sb3I9IiM1NkEyMzEiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjYikiIHN0cm9rZT0iI0ZGRiIgc3Ryb2tlLXdpZHRoPSIyMCIgZD0iTTE2NSAyNWgxNTVjMjQuODU0IDAgNDUgMjAuMTQ2IDQ1IDQ1djE1NWMwIDI0Ljg1NC0yMC4xNDYgNDUtNDUgNDVIMTY1Yy0yNC44NTQgMC00NS0yMC4xNDYtNDUtNDVWNzBjMC0yNC44NTQgMjAuMTQ2LTQ1IDQ1LTQ1eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iYyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzEzLjcwNTMiIHkxPSI2NTEuODA3NiIgeDI9Ii0zMTIuNzA1MyIgeTI9IjY1Mi44MDc2IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDE2MCAwIDAgMTYwIDUwNDEzLjA0IC0xMDQyNzkpIj48c3RvcCBvZmZzZXQ9IjAuMDUiIHN0b3AtY29sb3I9IiM5QzMiLz48c3RvcCBvZmZzZXQ9IjAuOTUiIHN0b3AtY29sb3I9IiM3MkJEMkQiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjYykiIHN0cm9rZT0iI0ZGRiIgc3Ryb2tlLXdpZHRoPSIyMCIgZD0iTTI0MCAxMGgxMjBjMTEuMDQ2IDAgMjAgOC45NTQgMjAgMjB2MTIwYzAgMTEuMDQ2LTguOTU0IDIwLTIwIDIwSDI0MGMtMTEuMDQ2IDAtMjAtOC45NTQtMjAtMjBWMzBjMC0xMS4wNDYgOC45NTQtMjAgMjAtMjB6Ii8+PC9zdmc+');
            }

            .item-type-microdata {
                background-image: url('data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48cGF0aCBmaWxsPSIjOTAwIiBkPSJNMCAwaDEwMHYxMDBIMFYweiIvPjxwYXRoIGZpbGw9IiM1MTAwMDAiIGQ9Ik0zNy40NDUgNjYuODE4Yy4zNiAzLjA5IDEuMTQ1IDUuMjYgMi4zNSA2LjUwMiAyLjA4NyAyLjI5IDYuMDIgMy40MzQgMTEuOCAzLjQzNCAzLjM3Mi4wNCA2LjA2LS40NjQgOC4wNy0xLjUwNyAxLjk2Ni0xLjAwMiAyLjk0OC0yLjUwOCAyLjk0OC00LjUxNiAwLTEuODgtLjgwMy0zLjM1LTIuNDA4LTQuMzktMS42NDYtLjk2LTcuNjA2LTIuNjktMTcuODgzLTUuMTgtNy40MjctMS44NC0xMi42NjQtNC4xNS0xNS43MTYtNi45Mi0zLjA5LTIuNjktNC42MTQtNi42Mi00LjU3NS0xMS44IDAtNi4wMiAyLjM5LTExLjI0IDcuMTctMTUuNjUyIDQuNzQtNC4zMzUgMTEuNDItNi41MDMgMjAuMDUtNi41MDMgOC4xOSAwIDE0Ljg4IDEuNjI1IDIwLjA1IDQuODc3IDUuMSAzLjMzIDguMDUgOC45OSA4Ljg1IDE2Ljk4SDYwLjk5Yy0uMjQtMi4yMDctLjg2My0zLjk1NC0xLjg2Ny01LjI0LTEuOTI2LTIuMzI3LTUuMTM4LTMuNDktOS42MzMtMy40OS0zLjczMy4wNC02LjM4Mi42Mi03Ljk0NyAxLjc0NS0xLjY0NiAxLjE2NS0yLjQ0OCAyLjUzLTIuNDA4IDQuMDk2IDAgMi4wMDcuODQyIDMuNDMzIDIuNTI4IDQuMjc0IDEuNjg2Ljk2NCA3LjY0NyAyLjU1IDE3Ljg4MyA0Ljc1NiA2Ljc4MyAxLjY0NCAxMS45IDQuMDczIDE1LjM1MyA3LjI4MyAzLjM3IDMuMzMgNS4wNiA3LjQ0NSA1LjA2IDEyLjM0MyAwIDYuNTA0LTIuNDEgMTEuODAyLTcuMjMgMTUuODk2LTQuOSA0LjE3My0xMi4zOSA2LjI0LTIyLjQ2IDYuMi0xMC4zNi4wNC0xNy45Ni0yLjE0OC0yMi44Mi02LjU2NC00Ljk0LTQuMzM4LTcuNDEtOS44NzctNy40MS0xNi42MmgxNy40eiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik0zNy40NDUgNTYuNTQyYy4zNiAzLjA5IDEuMTQ1IDUuMjU4IDIuMzUgNi41MDMgMi4wODcgMi4yODYgNi4wMiAzLjQzIDExLjggMy40MyAzLjM3Mi4wNCA2LjA2LS40NiA4LjA3LTEuNTAzIDEuOTY2LTEuMDA0IDIuOTQ4LTIuNTEgMi45NDgtNC41MTcgMC0xLjg4Ny0uODAzLTMuMzUtMi40MDgtNC4zOTUtMS42NDYtLjk2Mi03LjYwNi0yLjY5LTE3Ljg4My01LjE3OC03LjQyNy0xLjg0Ny0xMi42NjQtNC4xNTUtMTUuNzE2LTYuOTI1LTMuMDktMi42OS00LjYxNC02LjYyMy00LjU3NS0xMS44IDAtNi4wMiAyLjM5LTExLjI0IDcuMTctMTUuNjU1QzMzLjk0IDEyLjE2NyA0MC42MiAxMCA0OS4yNSAxMGM4LjE5IDAgMTQuODcgMS42MjUgMjAuMDUgNC44NzcgNS4wOTYgMy4zMyA4LjA0NiA4Ljk5IDguODUgMTYuOThINjAuOTljLS4yNC0yLjIxLS44NjMtMy45NTUtMS44NjctNS4yNC0xLjkyNi0yLjMyNy01LjEzOC0zLjQ5LTkuNjMzLTMuNDktMy43MzMuMDQtNi4zODIuNjItNy45NDcgMS43NDUtMS42NDYgMS4xNjQtMi40NDggMi41MjgtMi40MDggNC4wOTMgMCAyLjAwOC44NDIgMy40MzMgMi41MjggNC4yNzYgMS42ODYuOTcgNy42NDcgMi41NSAxNy44ODMgNC43NiA2Ljc4MyAxLjY1IDExLjkgNC4wOCAxNS4zNTMgNy4yOSAzLjM3IDMuMzMgNS4wNiA3LjQ1IDUuMDYgMTIuMzQ4IDAgNi41LTIuNDEgMTEuOC03LjIzIDE1Ljg5Ni00LjkgNC4xNzQtMTIuMzkgNi4yNC0yMi40NiA2LjItMTAuMzYuMDQtMTcuOTYtMi4xNS0yMi44Mi02LjU2Mi00Ljk0LTQuMzMzLTcuNDEtOS44NzItNy40MS0xNi42MTZoMTcuNHoiLz48L3N2Zz4=');
            }

            .item-type-json-ld {
                background-image: url('data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDI0IDEwMjQiPjxwYXRoIGQ9Ik0zMTAuMjMgODI2LjE1djU2LjQ3OGgtMjUuMWMtNjUuMTA2IDAtMTA4LjcwOC05LjY3Ny0xMzAuOC0yOS4wMjMtMjIuMDk3LTE5LjM1My0zMy4xNC01Ny45MTctMzMuMTQtMTE1LjY5OFY2NDQuMTdjMC0zOS40NzctNi45OTgtNjYuODAyLTIwLjk4Mi04MS45Ny0xMy45OS0xNS4xNi0zOS4yODgtMjIuNzQ2LTc1Ljg5LTIyLjc0NkgwVjQ4My4zN2gyNC4zMTZjMzYuODY3IDAgNjIuMjI1LTcuNDUyIDc2LjA4Ni0yMi4zNTUgMTMuODU2LTE0LjkwMyAyMC43ODctNDEuOTY1IDIwLjc4Ny04MS4xODV2LTk0LjEyOGMwLTU3Ljc4MiAxMS4wNC05Ni4yODUgMzMuMTQtMTE1LjUwMyAyMi4wOS0xOS4yMiA2NS42OS0yOC44MyAxMzAuNzk1LTI4LjgzaDI1LjF2NTYuMDg0aC0yNy40NTNjLTM2LjYxIDAtNjAuNDY2IDUuNjg3LTcxLjU3NyAxNy4wNi0xMS4xMTYgMTEuMzc1LTE2LjY2OCAzNS42My0xNi42NjggNzIuNzU0djk3LjI2NmMwIDQxLjA1Mi01Ljk1IDcwLjg2LTE3Ljg0NiA4OS40Mi0xMS45IDE4LjU3LTMyLjIyNyAzMS4xMi02MC45ODYgMzcuNjUyIDI5LjAyMyA3LjA1NyA0OS40MTcgMTkuODcyIDYxLjE4MyAzOC40MzMgMTEuNzY3IDE4LjU3IDE3LjY1IDQ4LjI0IDE3LjY1IDg5LjAzdjk3LjI2NWMwIDM3LjM4NyA1LjU1MiA2MS43MDMgMTYuNjY4IDcyLjk0OCAxMS4xMSAxMS4yNCAzNC45NjcgMTYuODY0IDcxLjU3NyAxNi44NjRoMjcuNDU1em00MDMuNTI4IDBoMjcuNDU0YzM2LjYwMyAwIDYwLjQ2LTUuNjI1IDcxLjU3Ni0xNi44NjQgMTEuMTEtMTEuMjQ1IDE2LjY2OC0zNS41NjIgMTYuNjY4LTcyLjk1VjYzOS4wN2MwLTQwLjc4NiA1Ljg4NC03MC40NiAxNy42NS04OS4wMjcgMTEuNzY1LTE4LjU2MiAzMi4xNi0zMS4zNzYgNjEuMTgyLTM4LjQzNS0yOS4wMjItNi41MzItNDkuNDE3LTE5LjA4My02MS4xODMtMzcuNjUtMTEuNzY2LTE4LjU2My0xNy42NS00OC4zNy0xNy42NS04OS40MjJWMjg3LjI3YzAtMzcuMTI1LTUuNTU3LTYxLjM4LTE2LjY2Ny03Mi43NTQtMTEuMTE2LTExLjM3NC0zNC45NzQtMTcuMDYtNzEuNTc2LTE3LjA2aC0yNy40NTRWMTQxLjM3aDI0LjcwOGM2NS4xMDUgMCAxMDguNTcyIDkuNjEgMTMwLjQwNiAyOC44MjcgMjEuODMgMTkuMjE4IDMyLjc1IDU3LjcyIDMyLjc1IDExNS41MDN2OTQuMTI4YzAgMzguOTYyIDcuMDYgNjUuOTU3IDIxLjE3OCA4MC45OSAxNC4xMiAxNS4wMzggMzkuNzQgMjIuNTUgNzYuODcgMjIuNTVoMjQuMzE3djU2LjA4NUg5OTkuNjdjLTM3LjEzIDAtNjIuNzUgNy41ODYtNzYuODcgMjIuNzQ3LTE0LjEyIDE1LjE2Ny0yMS4xOCA0Mi40OTItMjEuMTggODEuOTd2OTMuNzM2YzAgNTcuNzgtMTAuOTIgOTYuMzQ2LTMyLjc0OCAxMTUuNjk4LTIxLjgzNCAxOS4zNDctNjUuMyAyOS4wMjMtMTMwLjQwNiAyOS4wMjNoLTI0LjcwOFY4MjYuMTV6Ii8+PHBhdGggZmlsbD0iIzBDNDc5QyIgZD0iTTY5NS41MDQgNjAwLjY2N2MtMy41NDgtMS44OC03LjE2Mi0zLjQ4LTEwLjgtNC45MDNsMi42LS4yMTJzLTIzLjE1My0xMC4yNTItMjUuMTgtODQuNjc0Yy0yLTc0LjQzMiAyMi4wNzMtODcuMTI3IDIyLjA3My04Ny4xMjdsLTMuNDU4LjE1YzE4LjE4NS05LjMzIDMzLjgzMi0yNC4wNyA0NC4xNi00My41MyAyNi45LTUwLjYgNy42NS0xMTMuNDYtNDIuOTY1LTE0MC4zNjUtNTAuNjM3LTI2Ljg3NC0xMTMuNDc4LTcuNjctMTQwLjM2MyA0Mi45ODQtMTEuMDU2IDIwLjc3Ni0xNC4xOTYgNDMuNjAyLTEwLjcwNCA2NS4xNjZsLTEuMTgzLTEuODE4czYuMDk3IDI3LjAwOC01Ny4yMiA2Ni4zMWMtNjMuMzA0IDM5LjMyNS05MS44NjIgMTkuNzQ2LTkxLjg2MiAxOS43NDZsMS44MTggMi42OGMtMS44MTItMS4xMjItMy41NDctMi4yNjYtNS40MzctMy4yNTMtNTAuNjM3LTI2LjkxLTExMy40OTUtNy42OC0xNDAuMzkyIDQyLjk1Ni0yNi44ODUgNTAuNjMtNy42NTIgMTEzLjQ2IDQyLjk2OCAxNDAuMzggMzcuNzQgMjAuMDMgODIuMjQzIDE0LjQ0NyAxMTMuNTk3LTEwLjY3NmwtLjY4IDEuMzE0czIzLjA0OC0xOC45NiA4OS40NyAxNi43YzUyLjQzMyAyOC4xMzUgNjAuMjIgNTUuNzEzIDYxLjIxOCA2Ni4wNzYtMS4zNzIgMzguNDUgMTguNjcgNzYuMTYgNTQuOTI3IDk1LjQyIDUwLjYyIDI2LjkxNiAxMTMuNDggNy42NyAxNDAuMzY0LTQyLjk1MyAyNi45MDgtNTAuNjIgNy42ODYtMTEzLjQ4My00Mi45NS0xNDAuMzd6TTU3OC40NTYgNjE0LjMxYy04LjM5IDIuOTY4LTMyLjM1NyA2LjI1NC04Mi44MTUtMjAuODA1LTU0LjY0My0yOS4zNC02Mi43NTQtNTMuODUtNjMuOTQyLTYxLjIwNy43NzUtOC45MjUuMjgtMTcuODMzLTEuMjQzLTI2LjUyM2wuMzM0LjUwMnMtNC40NS0yMy44NCA1OC4wNzctNjIuNjY4YzU1Ljk2My0zNC43NCA4MS40MDQtMjcuODMgODYuMTQtMjYuMDIgMy4wNDYgMi4wNyA2LjIwMyA0IDkuNTE3IDUuNzYyIDYuMjk4IDMuMzQ3IDEyLjc5IDUuOTcgMTkuMzY3IDcuOTEgNy42OTggNy4zMyAyMS44NjYgMjguMTQgMjMuMzQgODIuODU0IDEuNTA1IDU1LjEyNi0xNC42OTMgNzYuMzg0LTIzLjUyMyA4My45MDItOS4wOTUgNC4xMTYtMTcuNjA3IDkuNjEtMjUuMjQ4IDE2LjI5eiIvPjxwYXRoIGZpbGw9IiNGRkYiIGQ9Ik01NzEuNjEyIDI3My4wNGMtMzAuMjA1IDMzLjAzOC0zMC44NCA4MS42NC0xLjc0IDEwOS4xNC0xNC4zODYtMTMuODUyLTE0LjA3My00Mi42NzguNDYzLTcwLjI2NyAxLjg2OC0yLjQ3IDcuMjg0LTguMzEyIDE1LjItNS42NTYuNzk3LjI3MyAxLjMyLjM0NiAxLjYyOC4yOTYgMS43OS4zODUgMy42MjYuNjEzIDUuNTIyLjUzIDEyLjAyLS41NDcgMjEuMzE4LTEwLjcxNiAyMC43NzctMjIuNzQyLS4yNDUtNS4zOS0yLjQ4LTEwLjE2NC01Ljk0LTEzLjgzIDI3LjktMTguMjggNTkuOTItMjAuMzcgNzMuMDM4LTguMzlsLjUwMi4wMzVjLTI5Ljk3LTI3LjM2NS03OC45Ni0yMi40NzMtMTA5LjQ1IDEwLjg4OHpNMjY3LjAxNiA1NzguNDA2Yy0uMjU3LS4yNjMtLjUyLS41Ny0uNzg2LS44MjYuMTY3LjE3My4zMy4zODUuNTMuNTdsLjI1Ni4yNTZ6bS45NjQtMTA5Ljk3Yy0zMC4yMSAzMy4wNDQtMzAuODQgODEuNjM0LTEuNzQ1IDEwOS4xNC0xNC4zOC0xMy44NTYtMTQuMDcyLTQyLjY4NC40NjQtNzAuMjcyIDEuODctMi40NzcgNy4yOTItOC4zMSAxNS4yMS01LjY1Ni43ODMuMjczIDEuMzEzLjM0IDEuNjMuMzAyIDEuNzkuMzg1IDMuNjMuNjEzIDUuNTIuNTMgMTIuMDIzLS41NTMgMjEuMzItMTAuNzIyIDIwLjc3NS0yMi43MzYtLjI1LTUuMzk0LTIuNDgyLTEwLjE3NC01LjkzLTEzLjgzMyAyNy44ODUtMTguMjcgNTkuOTA3LTIwLjM4IDczLjAyNi04LjM5MmwuNDk4LjA0NWMtMjkuOTctMjcuMzgyLTc4Ljk3LTIyLjQ5Ni0xMDkuNDUgMTAuODc3em0zMjAuNDYgMjc1LjQwNmMtLjI2Mi0uMjctLjUyNC0uNTctLjc4Ni0uODI2LjE2Mi4xNzMuMzMuMzg1LjUyNC41NjNsLjI2Mi4yNnptLjk2LTEwOS45ODZjLTMwLjIxIDMzLjA1NS0zMC44NCA4MS42NTUtMS43NDYgMTA5LjE1NS0xNC4zOTItMTMuODUtMTQuMDczLTQyLjY4NC40NTctNzAuMjYyIDEuODc4LTIuNDgyIDcuMjk1LTguMzEyIDE1LjIxNS01LjY1Ni43OTguMjYgMS4zMTIuMzQgMS42My4yOTYgMS43ODQuMzg1IDMuNjMuNjEzIDUuNTIuNTM1IDEyLjAxNi0uNTU3IDIxLjMxLTEwLjczIDIwLjc2OC0yMi43NTctLjI0NC01LjM5LTIuNDgtMTAuMTYzLTUuOTMtMTMuODI4IDI3Ljg5LTE4LjI2OCA1OS45MDMtMjAuMzcgNzMuMDMzLTguMzlsLjQ5LjA0Yy0yOS45NTItMjcuMzctNzguOTYtMjIuNDgtMTA5LjQzMiAxMC44NzJ6Ii8+PC9zdmc+');
            }

            .item-id {
                color: #aaa;
                font-weight: normal;
                margin-left: .5em;
                font-size: x-small;
            }

            .item-value {
                font-style: italic;
                margin: 1em 0 0 0;
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

            .legend {
                padding-right: 24px;
                margin: 0 0 0 1em;
                background-size: contain;
                background-repeat: no-repeat;
                background-position: right center;
            }
        </style>
    </head>
    <body>
        <article>
            <h1>Micrometa parser demo page</h1>
            <p>This demo page is part of the <a href="https://github.com/jkphl/micrometa" target="_blank">micrometa
                    parser</a> package and can be used to fetch a remote document and parse it for embedded micro
                information. You
                can select between 2 output styles, 3 different parsers and whether you want to print all micro
                information
                embedded into the document or extract the document's author only (according to the Microformats2 <a
                    href="http://indiewebcamp.com/authorship" target="_blank">authorship algorithm</a>).</p>
            <form method="post">
                <fieldset>
                    <legend>Enter an URL to be fetched &amp; examined</legend>
                    <div>
                        <label><span>URL</span><input type="url" name="url"
                                                      value="<?= htmlspecialchars($url); ?>"
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
                        <label class="legend item-type-mf2"><input type="checkbox" name="parser[<?= Microformats2::NAME; ?>]" value="<?= Microformats2::PARSE; ?>"<?= empty($parser[Microformats2::NAME]) ? '' : ' checked="checked"'; ?>/> Microformats 1+2</label>
                        <label class="legend item-type-microdata"><input type="checkbox" name="parser[<?= Microdata::NAME; ?>]" value="<?= Microdata::PARSE; ?>"<?= empty($parser[Microdata::NAME]) ? '' : ' checked="checked"'; ?>/> HTML Microdata</label>
                        <label class="legend item-type-json-ld"><input type="checkbox" name="parser[<?= JsonLD::NAME; ?>]" value="<?= JsonLD::PARSE; ?>"<?= empty($parser[JsonLD::NAME]) ? '' : ' checked="checked"'; ?>/> JSON-LD</label>
                        <input type="submit" name="microdata" value="Fetch &amp; parse URL"/>
                    </div>
                </fieldset><?php

                if (!empty($_POST['microdata']) && strlen($url)):

                    ?>
                    <fieldset>
                    <legend>Micro information embedded into <a href="<?= htmlspecialchars($url); ?>"
                                                               target="_blank"><?= htmlspecialchars($url); ?></a>
                    </legend><?php
                    if (version_compare(PHP_VERSION, '5.4', '<')):
                        ?><p class="hint">Unfortunately JSON pretty-printing is only available with PHP 5.4+.</p><?php
                    endif;

                    flush();
                    $micrometa = \Jkphl\Micrometa::instance(trim($url), null, array_sum($parser));
                    if ($data == 'author') {
                        $micrometa = $micrometa->author();
                    }

                    if (!$micrometa):
                        ?>The requested micro information could not be found embedded into this document.<?php
                    elseif ($format == 'json'):
                        ?>
                        <pre><?= htmlspecialchars($micrometa->toJSON(true)); ?></pre><?php
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
            <p>
                Copyright © 2017 Joschi Kuphal &lt;<a href="mailto:joschi@kuphal.net">joschi@kuphal.net</a>&gt;
                / <a href="https://twitter.com/jkphl" target="_blank">@jkphl</a></p>
        </footer>
    </body>
</html>
