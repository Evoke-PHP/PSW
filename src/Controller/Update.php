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
     * @var DataFeedInterface
     */
    protected $modelDataFeed;

    /**
     * @var WeatherReading
     */
    protected $modelWeatherReading;

    public function __construct(DataFeedInterface $modelDataFeed, WeatherReading $modelWeatherReading)
    {
        $this->modelDataFeed       = $modelDataFeed;
        $this->modelWeatherReading = $modelWeatherReading;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->modelDataFeed->setLocations($this->modelWeatherReading->getLocations($this->modelDataFeed->getFeedID()));
        $this->modelWeatherReading->store($this->modelDataFeed->getData());

        return $response->withStatus(200);
    }
}
// EOF
