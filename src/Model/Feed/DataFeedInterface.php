<?php
/**
 * DataFeedInterface
 *
 * @package   PSW\Model\Feed
 */
namespace PSW\Model\Feed;

/**
 * DataFeedInterface
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model\Feed
 */
interface DataFeedInterface
{
    /**
     * Get the Feed ID string that identifies the data feed.
     * @return string
     */
    function getFeedID() : string;

    /**
     * Return the result of the last request.
     * @return mixed[]
     */
    function getData() : array;
}
// EOF
