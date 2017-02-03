<?php
/**
 * Cams
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSW\Model\Feed\CamFeed;
use PSW\Model\Location;

/**
 * Cams
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Controller
 */
class Cam
{
    /**
     * @var CamFeed
     */
    protected $modelCamFeed;

    /**
     * @var Location
     */
    protected $modelLocation;

    /**
     * Update constructor.
     *
     * @param CamFeed  $modelCamFeed
     * @param Location $modelLocation
     */
    public function __construct(CamFeed $modelCamFeed, Location $modelLocation)
    {
        $this->modelCamFeed  = $modelCamFeed;
        $this->modelLocation = $modelLocation;
    }

    public function updateLocations(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $this->modelCamFeed->setLocations($this->modelLocation->getLocations($this->modelCamFeed->getFeedID()));
            $this->modelLocation->updateCams($this->modelCamFeed->getUpdatedCams());
        } catch (\Exception $e) {
            return $response->withStatus(503)->getBody()->write('Failed to update locations: ' . $e->getMessage());
        }

        return $response->withStatus(200)->getBody()->write('Successful update of cams.');
    }
}
// EOF
