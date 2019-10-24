<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests\Domain
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

namespace Jkphl\Micrometa\Infrastructure;

use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Infrastructure\Factory\ProfiledNamesFactory;
use Jkphl\Micrometa\Infrastructure\Parser\ProfiledNamesList;
use Jkphl\Micrometa\AbstractTestBase;

/**
 * Profiled name factory tests
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ProfiledNamesFactoryTest extends AbstractTestBase
{
    /**
     * Test the profiled name factory
     *
     * @param array $args     Arguments
     * @param array $expected Expected profiled names
     *
     * @dataProvider getProfiledNames
     */
    public function testProfiledNamesFactory(array $args, array $expected)
    {
        /** @var ProfiledNamesList $profiledNamesList */
        $profiledNamesList = ProfiledNamesFactory::createFromArguments($args);
        $this->assertInstanceOf(ProfiledNamesList::class, $profiledNamesList);
        $this->assertEquals($expected, $profiledNamesList->getArrayCopy());
    }

    /**
     * Get profiled name test data
     *
     * @return array[]
     */
    public function getProfiledNames()
    {
        $schemaOrgProfile = 'http://schema.org/';
        $feedObject       = (object)['name' => 'h-feed', 'profile' => MicroformatsFactory::MF2_PROFILE_URI];
        $eventObject      = (object)['name' => 'Event', 'profile' => $schemaOrgProfile];

        return [
            [
                ['h-feed'],
                [(object)['name' => 'h-feed', 'profile' => null]],
            ],
            [
                ['h-feed', MicroformatsFactory::MF2_PROFILE_URI],
                [$feedObject],
            ],
            [
                [['h-feed', MicroformatsFactory::MF2_PROFILE_URI]],
                [$feedObject],
            ],
            [
                [['name' => 'h-feed', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]],
                [$feedObject],
            ],
            [
                [$feedObject],
                [$feedObject],
            ],
            [
                [$feedObject, 'h-feed'],
                [$feedObject, $feedObject],
            ],
            [
                [$feedObject, $eventObject],
                [$feedObject, $eventObject],
            ],
            [
                [$feedObject, $eventObject, 'Person'],
                [$feedObject, $eventObject, (object)['name' => 'Person', 'profile' => $schemaOrgProfile]],
            ],
            [
                ['h-feed', MicroformatsFactory::MF2_PROFILE_URI, $eventObject],
                [$feedObject, $eventObject],
            ],
            [
                ['h-feed', $eventObject],
                [(object)['name' => 'h-feed', 'profile' => null], $eventObject],
            ],
        ];
    }

    /**
     * Test an invalid profiled name object
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1489528854
     */
    public function testInvalidObjectProfiledName()
    {
        ProfiledNamesFactory::createFromArguments([(object)['missing' => 'name']]);
    }

    /**
     * Test an invalid profiled name array
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1491063221
     */
    public function testInvalidArrayProfiledName()
    {
        ProfiledNamesFactory::createFromArguments([['invalid' => 'array']]);
    }

    /**
     * Test an invalid profiled name sting
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1489528854
     */
    public function testInvalidStringProfiledName()
    {
        ProfiledNamesFactory::createFromArguments(['']);
    }
}
