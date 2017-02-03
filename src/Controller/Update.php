<?php
/**
 * Update
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSW\Model\Cam;
use PSW\Model\DataFeedInterface;
use PSW\Model\Feed\CamFeed;
use PSW\Model\Location;
use PSW\Model\WeatherReading;

/**
 * Update
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Controller
 */
class Update
{
    /**
     * @var CamFeed
     */
    protected $camFeed;

    /**
     * @var Cam
     */
    protected $modelCam;

    /**
     * @var Location
     */
    protected $modelLocation;

    /**
     * @var DataFeedInterface[]
     */
    protected $weatherFeeds;

    /**
     * @var WeatherReading
     */
    protected $modelWeatherReading;

    /**
     * Update constructor.
     *
     * @param CamFeed        $camFeed
     * @param Cam            $modelCam
     * @param array          $weatherFeeds
     * @param WeatherReading $modelWeatherReading
     */
    public function __construct(
        CamFeed $camFeed,
        Cam $modelCam,
        Location $modelLocation,
        array $weatherFeeds,
        WeatherReading $modelWeatherReading
    ) {
        $this->camFeed             = $camFeed;
        $this->modelCam            = $modelCam;
        $this->modelLocation       = $modelLocation;
        $this->weatherFeeds        = $weatherFeeds;
        $this->modelWeatherReading = $modelWeatherReading;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->weatherFeeds as $dataFeed) {
            try {
                $weatherFeedLocations = $this->modelLocation->getLocations($dataFeed->getFeedID());
                $dataFeed->setLocations($weatherFeedLocations);
                $data         = $dataFeed->getData();
                $measurements = $this->modelWeatherReading->store($data, count($weatherFeedLocations));

                $this->camFeed->setLocations($this->modelLocation->getLocations($this->camFeed->getFeedID()));
                $camData = $this->camFeed->getData();
                $this->modelCam->store($measurements, $camData);

                return $response->withStatus(200)->getBody()->write('Successfully recorded measurements.');
            } catch (\Exception $e) {
                $response->getBody()->write('Exception: ' . $e->getMessage() . "\n");
                continue;
            }
        }

        return $response->withStatus(503)->getBody()->write('Failed to get data from any feed.');
    }
}
// EOF
