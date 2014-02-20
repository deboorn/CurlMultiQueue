<?php

/**
 * CurlMultiQueue
 * @author Daniel Boorn Rapid Digital LLC
 * @contact daniel.boorn@gmail.com (info@rapiddigitalllc.com)
 * @license Apache 2.0 Jan 2004
 */

require_once("vendor/com.rapiddigitalllc/curlmultiqueue.php");

//list of urls to fetch
$urls = array(
    'http://api.openweathermap.org/data/2.5/weather?q=London,uk',
    'http://api.openweathermap.org/data/2.5/weather?q=Myrtle%20Beach,%20SC',
    'http://api.duckduckgo.com/?q=DuckDuckGo&format=json&pretty=1',
    'http://api.duckduckgo.com/?q=curl+multi&format=json&pretty=1',
    'http://api.openweathermap.org/data/2.5/weather?q=London,uk',
    'http://api.openweathermap.org/data/2.5/weather?q=Myrtle%20Beach,%20SC',
    'http://api.duckduckgo.com/?q=DuckDuckGo&format=json&pretty=1',
    'http://api.duckduckgo.com/?q=curl+multi&format=json&pretty=1',
    'http://api.openweathermap.org/data/2.5/weather?q=London,uk',
    'http://api.openweathermap.org/data/2.5/weather?q=Myrtle%20Beach,%20SC',
    'http://api.duckduckgo.com/?q=DuckDuckGo&format=json&pretty=1',
    'http://api.duckduckgo.com/?q=curl+multi&format=json&pretty=1',
);

echo "<pre>";


# Example 1 - Fire and forget

$cQ = CurlMultiQueue::forge($urls, 5)->execute();
var_dump($cQ->getTotalTime());
$cQ->dumpResponses();


# Example 2 - Use with PHP Callbacks and Obtaining Responses

/**
 * Callback can be used for tracking amount of open threads
 * @param $active
 * @param $maxPipes
 */
$pipeAmountChange = function ($active, $maxPipes) {
    echo "Thread change: {$active}  of {$maxPipes}\n";
};

/**
 * Callback can be used fire an event on thread open
 * @param $index
 * @param $url
 * @param $ch
 */
$pipeOpened = function ($index, $url, $ch) {
    echo "Thread opened with {$url} [index {$index}]\n";
};

$cQ = CurlMultiQueue::forge($urls, 5, $pipeAmountChange, $pipeOpened);
$time = $cQ->execute()->getTotalTime();

echo "Queue completed in {$time} seconds.\n";

for ($i = 0; $i < count($urls); $i++) {
    var_dump($cQ->getResponse($i));
}




