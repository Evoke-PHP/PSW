<?php
/**
 * Weather
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use PSW\Model\WeatherReading;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\PhpRenderer;

/**
 * Weather
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Controller
 */
class Weather
{
    /**
     * @var WeatherReading
     */
    protected $modelWeatherReading;

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Weather constructor.
     *
     * @param WeatherReading $modelWeatherReading
     * @param PhpRenderer    $renderer
     * @param Response       $response
     */
    public function __construct(WeatherReading $modelWeatherReading, PhpRenderer $renderer)
    {
        $this->modelWeatherReading = $modelWeatherReading;
        $this->renderer            = $renderer;
    }


    public function show(Request $request, Response $response)
    {
        $this->renderer->render(
            $response,
            'index.phtml',
            $this->modelWeatherReading->getSummaryDataByTimeInterval()
        );
    }

    public function showMeasurement(Request $request, Response $response, array $args)
    {
        $this->renderer->render(
            $response,
            'measurement.phtml',
            ['measurement' => reset($this->modelWeatherReading->getReading($args['measurement']))]
        );
    }
}
// EOF
