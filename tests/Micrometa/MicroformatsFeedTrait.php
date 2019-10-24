<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
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

namespace Jkphl\Micrometa;

use Jkphl\Micrometa\Application\Factory\PropertyListFactory;
use Jkphl\Micrometa\Application\Item\Item as ApplicationItem;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Value\ValueInterface;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Ports\Item\Item;

/**
 * Trait MicroformatsFeedTrait
 *
 * @package    Jkphl\Micrometa
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
        return new Item($this->getApplicationFeedItem());
    }

    /**
     * Create and return an h-feed Microformats applocation item
     *
     * @return ApplicationItem h-feed application item
     */
    protected function getApplicationFeedItem()
    {
        $authorItem = $this->getAuthorApplicationItem();
        $entryItem  = $this->getEntryApplicationItem($authorItem);
        $feedItem   = new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-feed'],
            [
                $this->getPropertyObject('name', new StringValue('John Doe\'s Blog')),
                $this->getPropertyObject('author', $authorItem),
                $this->getPropertyObject('custom-property', new StringValue('Property for alias testing')),
            ],
            [$entryItem, $entryItem],
            'feed-id',
            'feed-language',
            'feed-value'
        );

        return $feedItem;
    }

    /**
     * Return an author application item
     *
     * @return ApplicationItem Author application item
     */
    protected function getAuthorApplicationItem()
    {
        return new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-card'],
            [
                $this->getPropertyObject('name', new StringValue('John Doe')),
                $this->getPropertyObject('email', new StringValue('john@example.com')),
            ]
        );
    }

    /**
     * Return a property object
     *
     * @param string $name          Name
     * @param ValueInterface $value Value
     *
     * @return object Property object
     */
    protected function getPropertyObject($name, ValueInterface $value)
    {
        return (object)[
            'profile' => MicroformatsFactory::MF2_PROFILE_URI,
            'name'    => $name,
            'values'  => [$value]
        ];
    }

    /**
     * Return an entry application item
     *
     * @param ApplicationItem $authorItem Author application item
     *
     * @return ApplicationItem Entry application item
     */
    protected function getEntryApplicationItem(ApplicationItem $authorItem)
    {
        return new ApplicationItem(
            Microformats::FORMAT,
            new PropertyListFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-entry'],
            [
                $this->getPropertyObject('name', new StringValue('Famous blog post')),
                $this->getPropertyObject('author', $authorItem),
            ]
        );
    }
}
