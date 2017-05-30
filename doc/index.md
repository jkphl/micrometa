# jkphl/micrometa

[![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url] [![Scrutinizer Code Quality][scrutinizer-image]][scrutinizer-url] [![Code Climate][codeclimate-image]][codeclimate-url] [![Documentation Status][readthedocs-image]][readthedocs-url] [![Clear architecture][clear-architecture-image]][clear-architecture-url]

> A meta parser for extracting micro information out of web documents, currently supporting Microformats 1+2, HTML Microdata, RDFa Lite 1.1, JSON-LD and Link Types

# About

*micrometa* is a **meta parser** for extracting micro information out of web documents (HTML, XML, SVG etc.), currently supporting

1. [Microformats](http://microformats.org/wiki) and [Microformats 2](http://microformats.org/wiki/microformats2),
2. W3C [HTML Microdata](https://www.w3.org/TR/microdata/),
3. W3C [JSON-LD](https://www.w3.org/TR/json-ld/),
4. [RDFa Lite 1.1](https://www.w3.org/TR/rdfa-lite/) and
5. [Link Types](https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types).

The parser is vocabulary agnostic and processes a wide range of expressions like Microformats, [schema.org](http://schema.org) and the various RDFa ontologies. Extracted items are returned in a unified / universal format.

## Online demo & bookmarklet

To quickly test a site for contained micro information you can try the **online demo site** at

* https://micrometa.jkphl.is 

There's also a **bookmarklet** you can save to your browser bookmarks in order to get a one-click analysis via the online demo site. [Please get it here](https://gist.github.com/jkphl/c61c0ae33a216cfc78c3fe4a6788285d).

# Usage

## Parser creation & invocation

In order to extract micro information out of a web document you have to create and invoke a meta parser instance:

```php
use Jkphl\Micrometa\Ports\Parser;

$micrometa = new Parser();
$items = $micrometa('http://example.com');
```

By default, the meta parser utilizes all known subparsers (i.e. formats) to find items in the document. You can change the default selection of subparsers by passing a `$format` bitmask to the parser constructor:
 
 ```php
 use Jkphl\Micrometa\Ports\Parser;
 use Jkphl\Micrometa\Ports\Format;
 
// Format::MICROFORMATS = 1
// Format::MICRODATA = 2
// Format::JSON_LD = 4
// Format::RDFA_LITE = 8
// Format::LINK_TYPE = 16
// Format::ALL = 31
 
 $micrometa = new Parser(Format::MICROFORMATS | Format::MICRODATA);
```

... or pick the formats per invocation:

```php
use Jkphl\Micrometa\Ports\Parser;

$micrometa = new Parser();
$items = $micrometa('http://example.com', null, Format::RDFA_LITE);
```

*micrometa* is both able to fetch a document from the web as well as parse piped in source code. In any case you need to provide a URL for relative link resolution:

```php
use Jkphl\Micrometa\Ports\Parser;

$micrometa = new Parser();
$items = $micrometa('http://example.com', '<html>...</html>');
```

When fetching a remote document, you may specify an array with `$option`s for the HTTP client (please [read the section about URIs](https://github.com/jkphl/dom-factory/blob/master/doc/index.md#uris) in the *jkphl/dom-factory* documentation for more details):

```php
// With HTTP client / request options
$options = [
    'client' => ['timeout' => 30],
    'request' => ['verify' => false],
];
$items = $micrometa('http://example.com', null, Format::ALL, $options);
```

All [items](#items) found are returned as an [item object model](#item-object-model).

## Items

Items are the main entity constructed by the parser. Regardless of their original format they share a common structure (JSON notation):

```js
{
    "format": 0, // Original item format, see format constants
    "id": null, // Unique item ID
    "language": null, // Item language
    "value": null, // The overall value of the item
    "types": [], // The item's type(s)
    "properties": {}, // The item's properties
    "items": [] // Nested sub-items
}
```

Support for the different aspects varies depending on the format:

| Format         | `format` | `id` | `lang` | `value` | `types` | `properties` | `items` |
|:---------------|:--------:|:----:|:------:|:-------:|:-------:|:------------:|:-------:|
| Microformats   |    ✓     |  –   |   ✓    |    ✓    |    ✓    |      ✓       |    ✓    |
| HTML Microdata |    ✓     |  ✓   |   –    |    –    |    ✓    |      ✓       |    –    |
| RDFa Lite 1.1  |    ✓     |  ✓   |   –    |    –    |    ✓    |      ✓       |    –    |
| JSON-LD        |    ✓     |  ✓   |   ✓    |    –    |    ✓    |      ✓       |    –    |
| Link Type      |    ✓     |  –   |   –    |    –    |    ✓    |      ✓       |    –    |

### Format, ID, language and value

```php
$format = $item->getFormat();
$id = $item->getId();
$language = $item->getLanguage();
$value = $item->getValue();
```

Unavailable aspects return `null` as their value. 

### Item types

An item has one more **types**. Item types can be represented as strings, but are in fact [IRI](#iris) objects made up of a name and a profile string (denoting the vocabulary they belong to):

```php
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Domain\Item\Iri;

/**
 * @var Item $item
 * @var array $types
 */
$types = $item->getType();

/**
 * @var Iri $type
 */
$type = $types[0];

/**
 * @var string $typeName
 * @var string $typeProfile
 * @var string $typeStr
 */
$typeName = $type->name; // e.g. "h-entry"
$typeProfile = $type->profile; // e.g. "http://microformats.org/profile/"
$typeStr = "$type"; // e.g. "http://microformats.org/profile/h-entry"
```

The string representation of an item type does neither have to be a valid URL nor point to an existing resource on the web. It's rather a [namespace](https://en.wikipedia.org/wiki/Namespace)-like feature to disginguish between like-named types from different vocabularies. You can test whether an item is of a particular type (or contained in list of types):

```
$isAnHEntry = $item->isOfType('h-entry');
$isAnHEntry = $item->isOfType('h-entry', 'http://microformats.org/profile/');
$isAnHEntry = $item->isOfType(new Iri('http://microformats.org/profile/', 'h-entry'));
$isAnHEntry = $item->isOfType((object)['profile' => 'http://microformats.org/profile/', 'name' => 'h-entry']);
```

You can also pass multiple types to `isOfType()` using the [profiled names syntax](#profiled-names-syntax) described below. The method will return `true` as soon as one of the given types matches. This way you can easily determine if an item is e.g. a Microformats `h-card` or a schema.org `Person` (which are roughly equivalent). 

### Item properties

Each item has a **property list** with zero or more multi-valued **properties** (i.e. they can each have zero or more values). The property list behaves much like an array but is in fact an array-like object that uses [IRIs](#iris) as property keys, so you can do things like this with it:
 
```php
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Application\Item\PropertyList;
use Jkphl\Micrometa\Domain\Item\Iri;

/**
 * @var Item $item
 * @var PropertyList $properties
 * @var Iri $propertyName
 * @var array $propertyValues
 */
$properties = $item->getProperties();
foreach ($properties as $propertyName => $propertyValues) {
    echo $propertyName->profile; // --> "http://microformats.org/profile/"
    echo $propertyName->name; // --> "description"
    echo $propertyName; // --> "http://microformats.org/profile/description"
}

// Find the first property in the list with name "description" (regardless of profile)
$description = $properties['description'];

// Find the property with name "description" and profile "http://microformats.org/profile/"
$description = $properties['http://microformats.org/profile/description'];
$description = $properties[new Iri('http://microformats.org/profile/', 'description')];
$description = $properties[(object)['profile' => 'http://microformats.org/profile/', 'name' => 'description']];

// Find property by lowerCamelCased alias
$customProperty = $properties['custom-property'];
$customProperty = $properties['customProperty'];
```

The values of a property may either be

* **string values**,
* lists of **alternate string values** or
* **nested items**.

A string value may be language tagged while alternate values have an accessible key each:
 
```php
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Application\Value\AlternateValues;

/** @var StringValue $stringValue */
$stringValue = $properties['description'][0];
echo $stringValue; // --> "Lorem ipsum ..."
echo $stringValue->getLanguage(); // --> "de"

/** @var AlternateValues $alternateValue */
$alternateValue = $properties['content'][0];
echo $alternateValue['html']; // --> "<p>Lorem ipsum ...</p>"
echo $alternateValue['value']; // --> "Lorem ipsum ..."
echo $alternateValue['value']->getLanguage(); // --> "de"
```

There are several methods of retrieving a particular property:

```php
// Get all values for a particular property (with and without profile)
/** @var array $propertyValues */
$propertyValues = $item->getProperty('description', 'http://microformats.org/profile/');
$propertyValues = $item->getProperty('description');

// Get the second value of a particular property (by index in the value list)
$propertyValues = $item->getProperty('description', null, 1);

// Get the first value for the first property named "description" (no profile)
$firstPropertyValue = $item->description;
```

Similar to the `isOfType()` method for item types there's a way to find and return the first property matching a prioritized list (again using the [profiled names syntax](#profiled-names-syntax) described below):
 
```php
// Get the start date of an event, preferring Microformats over schema.org
$nameProperty = $item->getFirstProperty(
    new Iri('http://microformats.org/profile/', 'start'),  
    new Iri('http://schema.org/', 'startDate')  
);
```

## Item lists

Depending on the format, an item may have nested child `items`, which is why each item is an **item list** itself. To get the children of an item you can either iterate over it or explicitly use `getChildren()`:

```php
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemList;

/**
 * @var Item $item
 * @var Item $child
 */
foreach ($item as $child) {
    // ...
}

/** @var ItemList $children */
$children = $item->getItems();
```

Item lists have some convenience methods for quickly finding children of particular types:

```php
use Jkphl\Micrometa\Ports\Item\Item;
/** @var Item $item */

// Get all nested h-event Microformats 
$events = $item->getItems('h-event', 'http://microformats.org/profile/');

// Get the first nested h-event Microformat
$event = $item->getFirstItem('h-event', 'http://microformats.org/profile/');
$event = $item->hEvent(); // lowerCamelCased item type name (without profile)
$event = $item->hEvent(0);

// Get the second nested h-event Microformat
$event = $item->hEvent(1);

// Get the first nested h-event Microformat OR schema.org Event (whichever comes first)
$event = $item->getFirstItem(
    new Iri('http://microformats.org/profile/', 'h-event'),
    new Iri('http://schema.org/', 'Event')
);
````

## Item object model

The top-level result returned by the parser is an **item object model** which is a special item list featuring a convenience method for link type items (only useful if you enable the [Link Types](https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types) parser):
 
```php
 use Jkphl\Micrometa\Ports\Parser;
 use Jkphl\Micrometa\Ports\Format;
 use Jkphl\Micrometa\Ports\Item\ItemObjectModel;
 use Jkphl\Micrometa\Ports\Item\ItemList;
 use Jkphl\Micrometa\Ports\Item\Item;
 
/** @var Parser $micrometa */
$micrometa = new Parser(Format::LINK_TYPE);

/** @var ItemObjectModel $items */
$items = $micrometa('http://example.com');

// Get all Link Type items
/** @var ItemList $allLinks */
$allLinks = $items->link();

// Get all Link Type items with rel="alternate"
/** @var ItemList $alternateLinks */
$alternateLinks = $items->link('alternate');

// Get the second alternate Link Type item (by index)
/** @var Item $firstAlternateLink */
$firstAlternateLink = $item->link('alternate', 1);
```

## Object export

All **items**, **item lists** and the **item object model** itself support being exported to a [POPO](http://www.javaleaks.org/open-source/php/plain-old-php-object.html) that can be JSON encoded. During export,

* [IRIs](#iris) will be stringified (loosing the distinction between their profile and their name),
* property lists will be arrayified (loosing their IRI keys),
* string values will be stringified (loosing the language tag), 
* alternate values will be arrayified.

```php
echo json_encode($items->toObject(), JSON_PRETTY_PRINT);
```

will output something like this:

```json
{
    "items": [
        {
            "format": 1,
            "id": null,
            "language": "en",
            "value": null,
            "types": [
                "http://microformats.org/profile/h-event"
            ],
            "properties": {
                "http://microformats.org/profile/location": [
                    {
                        "format": 1,
                        "id": null,
                        "language": "en",
                        "value": "Contentful",
                        "types": [
                            "http://microformats.org/profile/h-card"
                        ],
                        "properties": {
                            "http://microformats.org/profile/adr": [
                                {
                                    "format": 1,
                                    "id": null,
                                    "language": "en",
                                    "value": "Ritterstra\u00dfe 12 10969 Berlin , Germany 52.5020786 13.4089942 Berlin",
                                    "types": [
                                        "http://microformats.org/profile/h-adr"
                                    ],
                                    "properties": {
                                        "http://microformats.org/profile/street-address": [
                                            "Ritterstra\u00dfe 12"
                                        ],
                                        "http://microformats.org/profile/postal-code": [
                                            "10969"
                                        ],
                                        "http://microformats.org/profile/locality": [
                                            "Berlin"
                                        ],
                                        "http://microformats.org/profile/country": [
                                            "Germany"
                                        ],
                                        "http://microformats.org/profile/latitude": [
                                            "52.5020786"
                                        ],
                                        "http://microformats.org/profile/longitude": [
                                            "13.4089942"
                                        ],
                                        "http://microformats.org/profile/region": [
                                            "Berlin"
                                        ],
                                        "http://microformats.org/profile/name": [
                                            "Ritterstra\u00dfe 12 10969 Berlin , Germany 52.5020786 13.4089942 Berlin"
                                        ]
                                    },
                                    "items": []
                                }
                            ],
                            "http://microformats.org/profile/name": [
                                "Contentful"
                            ],
                            "http://microformats.org/profile/label": [
                                "Contentful"
                            ],
                            "http://microformats.org/profile/org": [
                                "Contentful"
                            ]
                        },
                        "items": []
                    },
                    {
                        "format": 1,
                        "id": null,
                        "language": "en",
                        "value": "tollwerk",
                        "types": [
                            "http://microformats.org/profile/h-card"
                        ],
                        "properties": {
                            "http://microformats.org/profile/adr": [
                                {
                                    "format": 1,
                                    "id": null,
                                    "language": "en",
                                    "value": "Klingenhofstra\u00dfe 5 90411 N\u00fcrnberg , Germany 49.4751594 11.1067807 Bavaria",
                                    "types": [
                                        "http://microformats.org/profile/h-adr"
                                    ],
                                    "properties": {
                                        "http://microformats.org/profile/street-address": [
                                            "Klingenhofstra\u00dfe 5"
                                        ],
                                        "http://microformats.org/profile/postal-code": [
                                            "90411"
                                        ],
                                        "http://microformats.org/profile/locality": [
                                            "N\u00fcrnberg"
                                        ],
                                        "http://microformats.org/profile/country": [
                                            "Germany"
                                        ],
                                        "http://microformats.org/profile/latitude": [
                                            "49.4751594"
                                        ],
                                        "http://microformats.org/profile/longitude": [
                                            "11.1067807"
                                        ],
                                        "http://microformats.org/profile/region": [
                                            "Bavaria"
                                        ],
                                        "http://microformats.org/profile/name": [
                                            "Klingenhofstra\u00dfe 5 90411 N\u00fcrnberg , Germany 49.4751594 11.1067807 Bavaria"
                                        ]
                                    },
                                    "items": []
                                }
                            ],
                            "http://microformats.org/profile/name": [
                                "tollwerk"
                            ],
                            "http://microformats.org/profile/label": [
                                "tollwerk"
                            ],
                            "http://microformats.org/profile/org": [
                                "tollwerk"
                            ]
                        },
                        "items": []
                    }
                ],
                "http://microformats.org/profile/name": [
                    "Accessibility Club"
                ],
                "http://microformats.org/profile/summary": [
                    "Hands-on meetup for web developers and designers about all things web accessibility and assistive technology"
                ],
                "http://microformats.org/profile/url": [
                    "https://accessibility-club.org"
                ],
                "http://microformats.org/profile/start": [
                    "2016-11-07T12:00+02:00"
                ],
                "http://microformats.org/profile/end": [
                    "2016-11-07T17:00+02:00"
                ]
            },
            "items": []
        }
    ]
}
```

## IRIs

[Internationalized Resource Identifiers](https://tools.ietf.org/html/rfc3987) (IRIs) are used e.g. by RDFa to uniquely identify types and properties when making up an ontology. The `Iri` objects in *micrometa* serve the purpose of having their short name stored separately from their base IRI ("profile") so that you can reference them in both short and expanded form. When you stringify an `Iri` (explicitly with `strval()` or implicitly by `echo`ing or concatenating it), you will get the expanded identifier:

```php
use \Jkphl\Micrometa\Domain\Item\Iri;

$iri = new Iri('http://example.com/', 'name');
echo $iri->profile; // --> "http://example.com/"
echo $iri->name; // --> "name"
echo $iri; // --> "http://example.com/name"
```

## Profiled names syntax

The methods

* `Item::isOfType()`,
* `Item::getFirstProperty()`,
* `ItemList::getFirstItem()` and
* `ItemList::getItems()`

support an arbitrary number of input parameters making up a list of **profiled names** (i.e. type or property names each associated with a profile; see [IRIs](#iris)). Please read the method documentation of [`ProfiledNamesFactory::createFromArguments()`](../src/Micrometa/Infrastructure/Factory/ProfiledNamesFactory.php#L51) to learn about the syntax.

## Logging

*micrometa* produces a few status messages (mostly for debugging purposes) and lets you pass in a any [PSR-3](http://www.php-fig.org/psr/psr-3/) compatible logger. It comes bundled with [monolog](https://github.com/Seldaek/monolog), so you could e.g. build upon that:

```php
use Jkphl\Micrometa\Ports\Format;
use Jkphl\Micrometa\Ports\Parser;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

$logHandler = new TestHandler();
$logger = new Logger('DEMO', [$logHandler]);
$micrometa = new Parser(Format::ALL, $logger);
```

*micrometa* comes with its own log handler (`ExceptionLogger`) that swallows all messages below a certain log level (`ERROR` by default) and throws them as exception otherwise. You can use and customize the `ExceptionLogger`:

```php
use Jkphl\Micrometa\Ports\Format;
use Jkphl\Micrometa\Ports\Parser;
use Jkphl\Micrometa\Infrastructure\Logger\ExceptionLogger;
use Monolog\Logger;

$exceptionLogHandler = new ExceptionLogger(Logger::INFO); // 0 for all messages as exceptions
$micrometa = new Parser(Format::ALL, $exceptionLogHandler);
```

## Cache

It turns out that processing JSON-LD is rather time consuming as the [underlying parser](https://github.com/lanthaler/JsonLD) fetches all referenced contexts from the web. To speed up things a bit you can use any [PSR-6](http://www.php-fig.org/psr/psr-6/) compatible cache implementation for storing the contexts and vocabularies that have already been fetched. *micrometa* comes bundled with [Symfony Cache](https://github.com/symfony/cache), so you can e.g. easily build upon that:

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Jkphl\Micrometa\Ports\Cache;
use Jkphl\Micrometa\Ports\Parser;

$cacheAdapter = new FilesystemAdapter('micrometa', 0, __DIR__.DIRECTORY_SEPARATOR.'cache');
Cache::setAdapter($cacheAdapter);
$micrometa = new Parser();

$cacheAdapter = Cache::getAdapter();
// ...
```

## Backwards compatibility

Originally it was my intention to keep the second generation of *micrometa* as close to the former API as possible. For several reasons, however, I had to break backwards compatibility almost completely:

* The first generation of *micrometa* was very [Microformats](http://microformats.org/wiki)-centric and supported a couple of features that aren't inherent to other formats. And vice versa, some of the other formats bring in additional features that I had to find a good common ground for. The new generation focuses on a lean and unified structure for all of them. If there's enough interest, I'll bring back some of the original features (e.g. the [IndieWeb authorship algorithm](http://indiewebcamp.com/authorship)) as plugins or complementary libraries. Let me know!
* Some of the supported formats have the concept of contexts / vocabularies that are associated with namespace-like URIs / IRIs, which also comes in handy when combining the formats. To support the distinct storage of profiles and names, most of the old public methods had to be changed significantly, making backwards compatibility close to impossible.

# Installation

This library requires PHP >=5.6 or later. I recommend using the latest available version of PHP as a matter of principle. It has no userland dependencies. It's installable and autoloadable via [Composer](https://getcomposer.org/) as [jkphl/micrometa](https://packagist.org/packages/jkphl/micrometa).
        
```bash
composer require jkphl/micrometa
```

Alternatively, [download a release](https://github.com/jkphl/micrometa/releases) or clone this repository, then require or include its [`autoload.php`](../autoload.php) file.

## Dependencies

![Composer dependency graph](https://rawgit.com/jkphl/micrometa/master/doc/dependencies.svg)

## Quality

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If you notice compliance oversights, please send a patch via pull request.

## Contributing

Found a bug or have a feature request? [Please have a look at the known issues](https://github.com/jkphl/micrometa/issues) first and open a new issue if necessary. Please see [contributing](../CONTRIBUTING.md) and [conduct](../CONDUCT.md) for details.

## Security

If you discover any security related issues, please email joschi@tollwerk.de instead of using the issue tracker.

## Credits

- [Joschi Kuphal][author-url]
- [All Contributors](../../contributors)

## License

Copyright © 2017 [Joschi Kuphal][author-url] / joschi@tollwerk.de. Licensed under the terms of the [MIT license](../LICENSE).


[travis-image]: https://secure.travis-ci.org/jkphl/micrometa.svg
[travis-url]: https://travis-ci.org/jkphl/micrometa
[coveralls-image]: https://coveralls.io/repos/jkphl/micrometa/badge.svg?branch=master&service=github
[coveralls-url]: https://coveralls.io/github/jkphl/micrometa?branch=master
[scrutinizer-image]: https://scrutinizer-ci.com/g/jkphl/micrometa/badges/quality-score.png?b=master
[scrutinizer-url]: https://scrutinizer-ci.com/g/jkphl/micrometa/?branch=master
[codeclimate-image]: https://lima.codeclimate.com/github/jkphl/micrometa/badges/gpa.svg
[codeclimate-url]: https://lima.codeclimate.com/github/jkphl/micrometa
[readthedocs-image]: https://readthedocs.org/projects/jkphl-micrometa/badge/?version=latest
[readthedocs-url]: http://jkphl-micrometa.readthedocs.io/en/latest/?badge=latest
[clear-architecture-image]: https://img.shields.io/badge/Clear%20Architecture-%E2%9C%94-brightgreen.svg
[clear-architecture-url]: https://github.com/jkphl/clear-architecture
[author-url]: https://jkphl.is
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
