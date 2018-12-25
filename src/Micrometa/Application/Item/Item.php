<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application\Item
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

namespace Jkphl\Micrometa\Application\Item;

use Jkphl\Micrometa\Application\Factory\PropertyListFactoryInterface;

/**
 * Item
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Application
 * @method PropertyListInterface getProperties() Item properties list
 */
class Item extends \Jkphl\Micrometa\Domain\Item\Item implements ItemInterface
{
    /**
     * Parser format
     *
     * @var int
     */
    protected $format;
    /**
     * Item value
     *
     * @var string
     */
    protected $value;
    /**
     * Nested Items
     *
     * @var ItemInterface[]
     */
    protected $children;

    /**
     * Item constructor
     *
     * @param int $format                                       Parser format
     * @param PropertyListFactoryInterface $propertyListFactory Property list factory
     * @param string|\stdClass|\stdClass[] $type                Item type(s)
     * @param \stdClass[] $properties                           Item properties
     * @param ItemInterface[] $children                         Nested items
     * @param string|null $itemId                               Item id
     * @param string|null $itemLanguage                         Item language
     * @param string|null $value                                Item value
     */
    public function __construct(
        $format,
        PropertyListFactoryInterface $propertyListFactory,
        $type,
        array $properties = [],
        array $children = [],
        $itemId = null,
        $itemLanguage = null,
        $value = null
    ) {
        $this->format = $format;
        parent::__construct($type, $properties, $itemId, $itemLanguage, $propertyListFactory);
        $this->children = $children;
        $this->value    = $value;
    }

    /**
     * Export the object
     *
     * @return mixed
     */
    public function export()
    {
        return (object)[
            'format'     => $this->getFormat(),
            'id'         => $this->getId(),
            'language'   => $this->getLanguage(),
            'value'      => $this->getValue(),
            'types'      => array_map(
                function ($type) {
                    return $type->profile.$type->name;
                },
                $this->getType()
            ),
            'properties' => $this->getProperties()->export(),
            'items'      => array_map(
                function (ItemInterface $item) {
                    return $item->export();
                },
                $this->getChildren()
            )
        ];
    }

    /**
     * Return the parser format
     *
     * @return int Parser format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Return the item value
     *
     * @return string Item value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the nested children
     *
     * @return ItemInterface[] Nested children
     */
    public function getChildren()
    {
        return $this->children;
    }
}
