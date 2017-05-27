<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa\Tests\Ports;

use Jkphl\Micrometa\Application\Factory\PropertyListFactory;
use Jkphl\Micrometa\Application\Item\Item as ApplicationItem;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Ports\Item\Item;

/**
 * Trait MicroformatsFeedTrait
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
trait MicroformatsFeedTrait
{
    /**
     * Create and return an h-feed Microformats item
     *
     * @return Item h-feed item
     */
    protected function getFeedItem()
    {
        $authorItem = new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-card'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('John Doe')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'email',
                    'values' => [
                        new StringValue('john@example.com')
                    ]
                ]
            ]
        );

        $entryItem = new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-entry'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('Famous blog post')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'author',
                    'values' => [
                        $authorItem
                    ]
                ]
            ]
        );

        $feedItem = new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-feed'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('John Doe\'s Blog')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'author',
                    'values' => [
                        $authorItem
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'custom-property',
                    'values' => [
                        new StringValue('Property for alias testing')
                    ]
                ],
            ],
            [$entryItem, $entryItem]
        );

        return new Item($feedItem);
    }
}
