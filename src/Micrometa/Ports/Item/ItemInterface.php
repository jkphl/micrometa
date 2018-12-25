<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
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

namespace Jkphl\Micrometa\Ports\Item;

use Jkphl\Micrometa\Application\Contract\ValueInterface;
use Jkphl\Micrometa\Application\Item\PropertyListInterface;

/**
 * Item interface
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
interface ItemInterface extends ItemListInterface
{
    /**
     * Return whether the item is of a particular type (or contained in a list of types)
     *
     * @param array ...$types Item types
     *
     * @return bool Item type is contained in the list of types
     * @api
     */
    public function isOfType(...$types);

    /**
     * Get the first value of an item property
     *
     * @param string $name Item property name
     *
     * @return ValueInterface|ValueInterface[]|array|ItemInterface First value of an item property
     * @api
     */
    public function __get($name);

    /**
     * Get a single property (value)
     *
     * @param string $name    Property name
     * @param string $profile Property profile
     * @param int|null $index Property value index
     *
     * @return ValueInterface|ValueInterface[]|array|ItemInterface Property value(s)
     * @api
     */
    public function getProperty($name, $profile = null, $index = null);

    /**
     * Get all values of the first available property in a stack
     *
     * @param array $properties Properties
     *
     * @return ValueInterface[]|array Property values
     * @api
     */
    public function getFirstProperty(...$properties);

    /**
     * Get the item format
     *
     * @return int Item format
     * @api
     */
    public function getFormat();

    /**
     * Get the item type
     *
     * @return \stdClass[] Item type
     * @api
     */
    public function getType();

    /**
     * Get the item ID
     *
     * @return string Item ID
     * @api
     */
    public function getId();

    /**
     * Get the item language
     *
     * @return string Item language
     * @api
     */
    public function getLanguage();

    /**
     * Return all properties
     *
     * @return PropertyListInterface Properties
     * @api
     */
    public function getProperties();

    /**
     * Return the item value
     *
     * @return string Item value
     * @api
     */
    public function getValue();
}
