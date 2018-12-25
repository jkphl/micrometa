<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain\Miom
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

namespace Jkphl\Micrometa\Domain\Item;

use Jkphl\Micrometa\Domain\Factory\IriFactory;
use Jkphl\Micrometa\Domain\Factory\PropertyListFactory;
use Jkphl\Micrometa\Domain\Factory\PropertyListFactoryInterface;

/**
 * Micro information item
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Domain
 */
class Item implements ItemInterface
{
    /**
     * Use the setup methods
     */
    use ItemSetupTrait;

    /**
     * Item constructor
     *
     * @param string|\stdClass|\stdClass[] $type                     Item type(s)
     * @param \stdClass[] $properties                                Item properties
     * @param string|null $itemId                                    Item id
     * @param string|null $itemLanguage                              Item language
     * @param PropertyListFactoryInterface|null $propertyListFactory Property list factory
     */
    public function __construct(
        $type,
        array $properties = [],
        $itemId = null,
        $itemLanguage = null,
        PropertyListFactoryInterface $propertyListFactory = null
    ) {
        $this->setup(
            $propertyListFactory ?: new PropertyListFactory(),
            is_array($type) ? $type : [$type],
            $properties,
            trim($itemId),
            trim($itemLanguage)
        );
    }

    /**
     * Return the item types
     *
     * @return \stdClass[] Item types
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the item ID (if any)
     *
     * @return string|null Item id
     */
    public function getId()
    {
        return $this->itemId;
    }

    /**
     * Return the item language (if any)
     *
     * @return string|null Item language
     */
    public function getLanguage()
    {
        return $this->itemLanguage;
    }

    /**
     * Return all item properties
     *
     * @return PropertyListInterface Item properties list
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Return the values of a particular property
     *
     * @param string|\stdClass|Iri $name Property name
     * @param string|null $profile       Property profile
     *
     * @return array Item property values
     */
    public function getProperty($name, $profile = null)
    {
        $iri = IriFactory::create(
            (($profile === null) || is_object($name)) ?
                $name :
                (object)[
                    'profile' => $profile,
                    'name'    => $name
                ]
        );

        return $this->properties->offsetGet($iri);
    }

    /**
     * Return whether the value should be considered empty
     *
     * @return boolean Value is empty
     */
    public function isEmpty()
    {
        return false;
    }
}
