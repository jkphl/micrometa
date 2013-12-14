<!DOCTYPE html>
<!--

/***********************************************************************************
 *  The MIT License (MIT)
 *  
 *  Copyright © 2013 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Micrometa parser test page</title>
        <style type="text/css">
            @CHARSET "UTF-8";
            body{
                padding:2em;
                margin:0;
                color:#333;
                background:#fafafa;
                font-family:Arial, Helvetica, sans-serif;
                font-size:1em;
                line-height:1.4
            }
            h1 {
                margin-top: 0;
            }
            input[type=url] {
                width: 20em;
            }
            label {
                cursor: pointer;
            }
            fieldset {
                border: 2px solid #ccc;
                padding: 1em;
                margin: 3em 0;
            }
            legend {
                padding: 0 .5em;
                font-weight: bold;
            }
            article {
                width: 60em;
            }
            footer, .hint {
            	font-size: 80%;
            	font-style: italic;
            }
            pre {
            	white-space: pre-wrap;
            }
            .hint {
            	margin: 0 0 1em 0;
            }
        </style>
    </head>
    <body>
        <article>
            <h1>Micrometa parser demo page</h1>
            <p>This demo page can be used to fetch a remote document and parse it for embedded micro information.</p>
            <form method="post">
                <fieldset>
                    <legend>Enter an URL to be fetched &amp; examined</legend>
                    <label>URL <input type="url" name="url" value="<?php echo empty($_POST['url']) ? '' : htmlspecialchars($_POST['url']); ?>" placeholder="http://" required="required"/></label>
                    <input type="submit" name="microdata" value="Fetch &amp; parse URL"/>
                </fieldset><?php

if (!empty($_POST['microdata']) && !empty($_POST['url'])):

				?><fieldset>
                    <legend>Micro information embedded into <a href="<?php echo htmlspecialchars($_POST['url']); ?>" target="_blank"><?php echo htmlspecialchars($_POST['url']); ?></a></legend><?php
	if (version_compare(PHP_VERSION, '5.4', '<')):
					?><p class="hint">Unfortunately JSON pretty-printing is only available with PHP 5.4+.</p><?php
	endif;
                    ?><pre><?php 
                    
	// Include the Composer autoloader
	if (@is_file(dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
		require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
	
	// Exit on failure
	} else {
		die ('<p style="font-weight:bold;color:red">Please follow the <a href="https://github.com/jkphl/micrometa#dependencies" target="_blank">instructions</a> to install the additional libraries that micrometa is depending on</p>');
	}
	require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Jkphl'.DIRECTORY_SEPARATOR.'Micrometa.php';
	echo htmlspecialchars(\Jkphl\Micrometa::instance(trim($_POST['url']))->toJSON(true));
                    
                    ?></pre>
                </fieldset><?php
                
endif;

            ?></form>
        </article>
        <footer>
        	<p>This demo page is part of the <a href="https://github.com/jkphl/micrometa" target="_blank">micrometa parser</a> package | Copyright © 2013 Joschi Kuphal &lt;<a href="mailto:joschi@kuphal.net">joschi@kuphal.net</a>&gt; / <a href="https://twitter.com/jkphl" target="_blank">@jkphl</a></p>
        </footer>
    </body>
</html>