<?php
/**
 * Update
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PSW\Model\DataFeedInterface;
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
     * @var [DataFeedInterface]
     */
    protected $modelDataFeeds;

    /**
     * @var WeatherReading
     */
    protected $modelWeatherReading;

    public function __construct(array $modelDataFeeds, WeatherReading $modelWeatherReading)
    {
        $this->modelDataFeeds      = $modelDataFeeds;
        $this->modelWeatherReading = $modelWeatherReading;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->modelDataFeeds as $dataFeed) {
            try {
                $dataFeed->setLocations($this->modelWeatherReading->getLocations($dataFeed->getFeedID()));
                $data = $dataFeed->getData();
                $this->modelWeatherReading->store($data);
                return $response->withStatus(200);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $response->withBody('Failed to get data from any feed.')->withStatus(503);
    }
}
// EOF
