micrometa
=========

is a simple **meta parser** for extracting micro information in several different formats out of HTML documents, written in PHP.

Embedding micro information into HTML documents is a pretty darn cool way of enriching your content with machine readable metadata. Unfortunately there are several different (de facto) standards for doing so, e.g.

1.	The "original" [Microformats (μF)](http://microformats.org/wiki),
2.	the updated [Microformats 2](http://microformats.org/wiki/microformats2) syntax,
3.	the [W3C Microdata](http://www.w3.org/TR/microdata/) specification,
4.	[RDFa](http://en.wikipedia.org/wiki/RDFa) and others ...

As a meta parser *micrometa* recognizes multiple formats and combines them to one common [JSON](http://en.wikipedia.org/wiki/JSON) result set. Currently both **Microformat** variants and the **Microdata** specification are supported. 

Dependencies
------------

*micrometa* relies on the following external parser packages:

1.	The [IndieWeb](https://github.com/indieweb) [microformats-2 parser for PHP](https://github.com/indieweb/php-mf2) (which also supports the original set of microformats),
2.	and the [MicrodataPHP parser](https://github.com/linclark/MicrodataPHP) by [Lin Clark](https://github.com/linclark).

Both packages are available on GitHub as well. To install them, simply follow the [installation instructions](lib/README.md) in the `lib` directory. I might add support for further formats at some point in the future.

Parsing
-------

*micrometa* essentially consists of one [main parser class](src/Jkphl/Micrometa.php) and several auxiliary classes. Fetching and parsing a specific URL is a easy as this:

```php
require_once '/path/to/micrometa/src/Jkphl/Micrometa.php'
	
$micrometaParser	= new \Jkphl\Micrometa($url);
$micrometaData		= $micrometaParser->toObject();
```

or simply

```php
$micrometaData		= \Jkphl\Micrometa::instance($url)->toObject();
```

Instead of letting *micrometa* fetch a remote HTML document you may also pipe some HTML source code directly into the parser. However, you will still have to provide a valid URL used for resolving relative URLs:

```php
$micrometaData		= \Jkphl\Micrometa::instance($url, $htmlSourceCode)->toObject();
```

*micrometa* provides several methods for accessing the extracted micro information. The `toObject()` method (see above) simply gets you a copy of **all** the extracted micro information, represented as a vanilla object (`\stdClass`) with three properties:

<table>
	<tr>
		<td>Property</td>
		<td>Key</td>
		<td>Property</td>
		<td>Value</td>
		<td>Data type</td>
	</tr>
	<tr>
		<td><i>items</i></td>
		<td> </td>
		<td> </td>
		<td>A list of all top level micro information items.</td>
		<td><code>\array</code></td>
	</tr>
	<tr>
		<td> </td>
		<td><code>0, 1, 2 ...</code></td>
		<td> </td>
		<td>Top level micro information item (see below).</td>
		<td><code>\Jkphl\Micrometa\Item</code></td>
	</tr>
	<tr>
		<td><i>rels</i></td>
		<td> </td>
		<td> </td>
		<td>A collection representing all <code>rel</code> attribute nodes (except the ones with an <i>alternate</i> value, see below).</td>
		<td><code>\stdClass</code></td>
	</tr>
	<tr>
		<td> </td>
		<td><code>rel</code> value</td>
		<td> </td>
		<td>Aggregated list of all <code>href</code> values of the elements with this <code>rel</code> value.</td>
		<td><code>\array</code></td>
	</tr>
	<tr>
		<td><i>alternates</i></td>
		<td> </td>
		<td> </td>
		<td>A list representing all <code>rel</code> attribute nodes having the value <i>alternate</i>.</td>
		<td><code>\array</code></td>
	</tr>
	<tr>
		<td> </td>
		<td><code>0, 1, 2 ...</code></td>
		<td> </td>
		<td>Object representing a single <code>rel="<i>alternate</i>"</code> attribute node.</td>
		<td><code>\stdClass</code></td>
	</tr>
	<tr>
		<td> </td>
		<td> </td>
		<td><i>url</i></td>
		<td>Value of the corresponding `href` attribute.</td>
		<td><code>\string</code></td>
	</tr>
	<tr>
		<td> </td>
		<td> </td>
		<td><i>rel</i></td>
		<td>Additional <code>rel</code> value components (if any, with "alternate" stripped out). .</td>
		<td><code>\string</code></td>
	</tr>
</table>

Example
-------

The included [example page](demo/example.html) features a mixture of [Microformats 2](http://microformats.org/wiki/microformats2) and [W3C Microdata](http://www.w3.org/TR/microdata/) information:

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Mixed microformats-2 / microdata example document</title>
    </head>
    <body>
        <figure class="h-card" itemscope="itemscope" itemtype="http://data-vocabulary.org/Person">
            <img class="u-photo" itemprop="photo" src="http://www.gravatar.com/avatar/60a1d50aa04c5742644fb9f1a21d74ba.jpg?s=100" alt="Joschi Kuphal" />
            <figcaption>
                <address>
                	<span class="p-name" itemprop="name"><span class="p-given-name">Joschi</span> <span class="p-family-name">Kuphal</span></span>
                	<span class="p-role" itemprop="role">Web architect</span>
                	<span class="p-adr" itemprop="address" itemscope="itemscope" itemtype="http://data-vocabulary.org/Address">from <span class="p-locality" itemprop="locality">Nuremberg</span>, <span class="p-country-name" itemprop="country">Germany</span></span>
                </address>
            </figcaption>
        </figure>
    </body>
</html>
```

This is the JSON output extracted by *micrometa*:

```JSON
{
    "items": [
        {
            "types": [
                "h-card"
            ],
            "properties": {
                "name": [
                    "Joschi Kuphal"
                ],
                "given-name": [
                    "Joschi"
                ],
                "family-name": [
                    "Kuphal"
                ],
                "role": [
                    "Web architect"
                ],
                "adr": [
                    "from Nuremberg, Germany"
                ],
                "locality": [
                    "Nuremberg"
                ],
                "country-name": [
                    "Germany"
                ],
                "photo": [
                    "http:\/\/www.gravatar.com\/avatar\/60a1d50aa04c5742644fb9f1a21d74ba.jpg?s=100"
                ]
            },
            "value": null
        },
        {
            "types": [
                "http:\/\/data-vocabulary.org\/Person"
            ],
            "properties": {
                "photo": [
                    "http:\/\/www.gravatar.com\/avatar\/60a1d50aa04c5742644fb9f1a21d74ba.jpg?s=100"
                ],
                "name": [
                    "Joschi Kuphal"
                ],
                "role": [
                    "Web architect"
                ],
                "address": [
                    {
                        "properties": {
                            "locality": [
                                "Nuremberg"
                            ],
                            "country": [
                                "Germany"
                            ]
                        },
                        "type": [
                            "http:\/\/data-vocabulary.org\/Address"
                        ]
                    }
                ]
            },
            "value": null
        }
    ],
    "rels": {

    },
    "alternates": [

    ]
}
```

Demo
----
There's a [demo page](demo/micrometa.php) included in this package, which you can use for checking arbitrary URLs for embedded micro information. Please be aware that the demo page has to be hosted on a PHP enabled server (preferably PHP 5.4+ for getting a pretty-printed JSON result). 

Legal
-----
Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / [@jkphl](https://twitter.com/jkphl)

*micrometa* is licensed under the terms of the [MIT license](LICENSE.txt).