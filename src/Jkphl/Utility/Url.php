<?php

/**
 * micrometa – Micro information meta parser
 *
 * @category Jkphl
 * @package        Jkphl_Utility
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Jkphl\Utility;

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

/**
 * URL manipulation class
 *
 * @category Jkphl
 * @package        Jkphl_Utility
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Url
{
    /**
     * URL part constant / key name mapping
     *
     * @var array
     */
    protected static $_keys = array(
        PHP_URL_SCHEME => 'scheme',
        PHP_URL_HOST => 'host',
        PHP_URL_PORT => 'port',
        PHP_URL_USER => 'user',
        PHP_URL_PASS => 'pass',
        PHP_URL_PATH => 'path',
        PHP_URL_QUERY => 'query',
        PHP_URL_FRAGMENT => 'fragment'
    );
    /**
     * Original URL string
     *
     * @var \string
     */
    protected $_url = null;
    /**
     * URL parts
     *
     * @var \array
     */
    protected $_parts = null;

    /************************************************************************************************
     * PUBLIC METHODS
     ***********************************************************************************************/

    /**
     * Constructor
     *
     * @param \string $url Original URL
     * @param \boolean $sanitize Sanitize URL
     */
    public function __construct($url, $sanitize = false)
    {
        $this->_url = $url;
        if (strncmp('//', $this->_url, 2)) {
            $this->_parts = parse_url($url);
        } else {
            $this->_parts = parse_url("http:$url");
            unset($this->_parts['scheme']);
        }
        if (empty($this->_parts['query'])) {
            $this->_parts['query'] = array();
        } else {
            parse_str($this->_parts['query'], $this->_parts['query']);
        }
        if ($sanitize) {
            $this->sanitize();
        }
    }

    /**
     * Sanitize some default URL parts
     *
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function sanitize()
    {
        if (empty($this->_parts['scheme'])) {
            $this->_parts['scheme'] = 'http';
        }
        if (empty($this->_parts['path'])) {
            $this->_parts['path'] = '/';
        }
        if (!empty($this->_parts['host']) && strncmp($this->_parts['path'], '/', 1)) {
            $this->_parts['path'] = '/'.$this->_parts['path'];
        }
        return $this;
    }

    /**
     * Create and return a (possibly sanitized and resolved) URL manipulation object
     *
     * @param \string $url Original URL
     * @param \boolean $sanitize Sanitize URL
     * @param \string|\Jkphl\Utility\Url $resolve URL (string or object) to resolve the new one against
     * @return \Jkphl\Utility\Url                        URL manipulation object
     */
    public static function instance($url, $sanitize = false, $resolve = null)
    {
        $instance = new self($url, $sanitize);
        if ($resolve !== null) {
            $instance->absolutize(($resolve instanceof self) ? $resolve : self::instance($resolve, true));
        }
        return $instance;
    }

    /**
     * Resolve this URL against a reference URL (in case this one is relative or otherwise incomplete)
     *
     * @param \Jkphl\Utility\Url $reference Reference URL
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function absolutize(\Jkphl\Utility\Url $reference)
    {

        // If the host part is missing
        if (empty($this->_parts['host'])) {
            $transfer = array('scheme', 'host', 'port', 'user', 'pass');

            // If this is a relative URL
            if ($this->isRelative()) {
                $this->_parts['path'] = dirname(rtrim($reference->path, '/')).'/'.$this->_parts['path'];
            }

            // Else if this URL is protocol relative
        } elseif (empty($this->_parts['scheme'])) {
            $transfer = array('scheme');

            // Else: Nothing to transfer
        } else {
            $transfer = array();
        }

        // Run through all transferrable keys
        foreach ($transfer as $key) {
            if (empty($this->_parts[$key])) {
                $this->$key = $reference->$key;
            }
        }

        return $this;
    }

    /**
     * Add query parameters
     *
     * @param \array $params Add query parameters to the URL (key / value pairs, also nested)
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function addQuery(array $params)
    {
        $this->_parts['query'] = array_merge($this->_parts['query'], $params);
        return $this;
    }

    /**
     * Remove specific query parameters from URL
     *
     * @param \array $params Query parameter names to remove from the URL
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function removeQuery(array $params)
    {
        $this->_parts['query'] = array_diff_key($this->_parts['query'], array_flip($params));
        return $this;
    }

    /**
     * Return a specific URL part (generic getter)
     *
     * @param \string $key Property key
     * @return \mixed                            Property value
     */
    public function __get($key)
    {
        return array_key_exists($key, $this->_parts) ? $this->_parts[$key] : null;
    }

    /************************************************************************************************
     * MAGIC METHODS
     ***********************************************************************************************/

    /**
     * Set a specific URL part (generic setter)
     *
     * @param \string $key Key
     * @param \mixed $value Value
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Set a specific URL part
     *
     * @param \string $key Part key
     * @param \mixed $value Value
     * @return \Jkphl\Utility\Url                URL manipulation object
     */
    public function set($key, $value)
    {
        if (in_array($key, self::$_keys)) {
            $this->_parts[$key] = $value;
        }
        return $this;
    }

    /**
     * String serialization
     *
     * @return \string                            URL string
     */
    public function __toString()
    {
        if ($this->isRelative()) {
            $url = './';
        } else {
            $url = (empty($this->_parts['scheme']) ? 'http' : $this->_parts['scheme']).'://';
            $url .= empty($this->_parts['user']) ? '' : rawurlencode(
                    $this->_parts['user']
                ).(empty($this->_parts['pass']) ? '' : ':'.rawurlencode($this->_parts['pass'])).'@';
            $url .= $this->_parts['host'];
            $url .= empty($this->_parts['port']) ? '' : ':'.$this->_parts['port'];
        }
        $url .= empty($this->_parts['path']) ? '' : $this->_parts['path'];
        $url .= count($this->_parts['query']) ? '?'.http_build_query($this->_parts['query']) : '';
        $url .= empty($this->_parts['fragment']) ? '' : '#'.$this->_parts['fragment'];
        return $url;
    }

    /************************************************************************************************
     * STATIC METHODS
     ***********************************************************************************************/

    /**
     * Return whether this URL is relative
     *
     * @return \boolean                            Whether this URL is relative
     */
    public function isRelative()
    {
        return empty($this->_parts['path']) ? false : (empty($this->_parts['host']) && (boolean)strncmp(
                $this->_parts['path'], '/', 1
            ));
    }
}
