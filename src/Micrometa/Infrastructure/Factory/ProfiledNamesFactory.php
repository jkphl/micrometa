<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
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

namespace Jkphl\Micrometa\Infrastructure\Factory;

use Jkphl\Micrometa\Infrastructure\Parser\ProfiledNamesList;
use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemList;

/**
 * Profiled name factory
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Infrastructure
 */
class ProfiledNamesFactory
{
    /**
     * Create a list of profiled names from method arguments
     *
     * The method takes an arbitrary number of arguments and tries to parse them as profiled names. Arguments
     * may be strings, arrays or objects.
     *
     * String values are interpreted as names — with one exception: If the first two arguments are both strings,
     * the second one is taken as profile IRI. Optionally following string arguments are taken as names again,
     * assuming to share the same profile:
     *
     *      createFromArguments($name1 [, $profile])
     *      createFromArguments($name1, $profile1, $name2, $profile2 ...)
     *
     * Arrays arguments are expected to have at least one argument which is taken as name. If present, the
     * second argument is used as profile (otherwise an empty profile is assumed):
     *
     *      createFromArguments(array($name [, $profile]))
     *
     * Object values are expected to have a "name" and an optional "profile" property:
     *
     *      createFromArguments((object)array('name' => $name [, 'profile' => $profile]))
     *
     * When an array or object argument is consumed, the profile value will be used for any following string
     * argument. You can "reset" the profile to another value by specifying another array or object value in
     * this case.
     *
     *      createFromArguments(array($name1, $profile1), $name2, $name3 ...)
     *
     * @param array $args Arguments
     *
     * @return ProfiledNamesList Profiled names
     * @see Item::isOfType()
     * @see Item::getFirstProperty()
     * @see ItemList::getFirstItem()
     * @see ItemList::getItems()
     */
    public static function createFromArguments(array $args)
    {
        $profiledNames = [];
        $profile       = false;

        // Consume and register all given names and profiles
        while (count($args)) {
            $profiledNames[] = self::consumeProfiledName($args, $profile);
        }

        return new ProfiledNamesList($profiledNames);
    }

    /**
     * Create a single profiled name by argument consumption
     *
     * @param array $args                  Arguments
     * @param string|boolean|null $profile Profile
     *
     * @return \stdClass Profiled name
     */
    protected static function consumeProfiledName(&$args, &$profile)
    {
        $profiledName          = new \stdClass();
        $profiledName->profile = $profile;

        // Get the first argument
        $arg = array_shift($args);

        // If it's not a scalar argument
        if (!is_scalar($arg)) {
            return self::consumeNonScalarProfiledName($arg, $profile);
        }

        if (($profile === false) && is_string(current($args))) {
            $profile = array_shift($args);
        }

        return self::createProfiledNameFromString(strval($arg), $profile);
    }

    /**
     * Create a profiled name by consuming a non-scalar argument
     *
     * @param \stdClass|array $arg         Argument
     * @param string|boolean|null $profile Profile
     *
     * @return \stdClass Profiled name
     */
    protected static function consumeNonScalarProfiledName($arg, &$profile)
    {
        // If it's an object argument
        if (is_object($arg)) {
            return self::createProfiledNameFromObject($arg, $profile);
        }

        // Else: It must be an array
        return self::createProfiledNameFromArray($arg, $profile);
    }

    /**
     * Create a profiled name from an object argument
     *
     * @param \stdClass $arg               Object argument
     * @param string|boolean|null $profile Profile
     *
     * @return \stdClass Profiled name
     * @throws InvalidArgumentException If the name is missing
     */
    protected static function createProfiledNameFromObject($arg, &$profile)
    {
        // If the name is invalid
        if (!isset($arg->name)) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_TYPE_PROPERTY_NAME_STR,
                InvalidArgumentException::INVALID_TYPE_PROPERTY_NAME
            );
        }

        if (isset($arg->profile)) {
            $profile = trim($arg->profile) ?: null;
        }

        return self::createProfiledNameFromString($arg->name, $profile);
    }

    /**
     * Create a profiled name from string arguments
     *
     * @param string $name                 Name
     * @param string|boolean|null $profile Profile
     *
     * @return \stdClass Profiled name
     * @throws InvalidArgumentException If the name is invalid
     */
    protected static function createProfiledNameFromString($name, $profile)
    {
        // If the name is invalid
        if (!strlen(trim($name))) {
            throw new InvalidArgumentException(
                InvalidArgumentException::INVALID_TYPE_PROPERTY_NAME_STR,
                InvalidArgumentException::INVALID_TYPE_PROPERTY_NAME
            );
        }

        return (object)[
            'name'    => trim($name),
            'profile' => trim($profile) ?: null,
        ];
    }

    /**
     * Create a profiled name from an array argument
     *
     * @param array $arg                   Array argument
     * @param string|boolean|null $profile Profile
     *
     * @return \stdClass Profiled name
     * @throws InvalidArgumentException If the array definition is invalid
     */
    protected static function createProfiledNameFromArray(array $arg, &$profile)
    {
        // If it's an associative array containing a "name" key
        if (array_key_exists('name', $arg)) {
            return self::createProfiledNameFromObject((object)$arg, $profile);
        }

        // If the argument has two items at least
        if (count($arg) > 1) {
            $name    = array_shift($arg);
            $profile = trim(array_shift($arg)) ?: null;

            return self::createProfiledNameFromString($name, $profile);
        }

        throw new InvalidArgumentException(
            InvalidArgumentException::INVALID_TYPE_PROPERTY_ARRAY_STR,
            InvalidArgumentException::INVALID_TYPE_PROPERTY_ARRAY
        );
    }
}
