<?php
/**
 * DataFeedInterface
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

/**
 * DataFeedInterface
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
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

    /**
     * Set the locations that we are getting data for.
     * @param array $locations
     * @return mixed
     */
    function setLocations(Array $locations);
}
// EOF
