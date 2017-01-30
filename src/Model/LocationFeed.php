<?php
/**
 * LocationFeed
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

/**
 * LocationFeed
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
 */
abstract class LocationFeed implements DataFeedInterface
{
    /**
     * The locations to retrieve the weather for.
     * @var mixed[]
     */
    protected $locations = [];

    /**
     * @inheritDoc
     */
    public function setLocations(Array $locations)
    {
        $this->locations = $locations;
    }
}
// EOF
