<?php
/**
 * Weather
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use PSW\Model\WeatherReading;
use Slim\Csrf\Guard;
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
     * @var Guard
     */
    protected $csrf;

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
     * @param Guard          $csrf
     * @param WeatherReading $modelWeatherReading
     * @param PhpRenderer    $renderer
     */
    public function __construct(Guard $csrf,WeatherReading $modelWeatherReading, PhpRenderer $renderer)
    {
        $this->csrf                = $csrf;
        $this->modelWeatherReading = $modelWeatherReading;
        $this->renderer            = $renderer;
    }

    public function addComment(Request $request, Response $response, array $args)
    {
        $this->modelWeatherReading->addComment($args + $request->getParsedBody());
        return $response->withStatus(302)->withHeader('Location', '/weather/measurement/' . $args['measurement']);
    }

    public function show(Request $request, Response $response)
    {
        $this->modelWeatherReading->setPage($request->getParam('p', 1));
        $this->modelWeatherReading->setTimeFilter($request->getParam('t', 'day'));

        $this->renderer->render(
            $response,
            'index.phtml',
            $this->modelWeatherReading->getSummaryDataByTimeInterval() +
            [
                'page'        => $this->modelWeatherReading->getPage(),
                'pages'       => $this->modelWeatherReading->getPages(),
                'time_filter' => $this->modelWeatherReading->getTimeFilter()
            ]
        );
    }

    public function showMeasurement(Request $request, Response $response, array $args)
    {
        $measurements = $this->modelWeatherReading->getReading($args['measurement']);
        $measurement = reset($measurements);

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        $this->renderer->render(
            $response,
            'measurement.phtml',
            [
                'measurement' => $measurement,
                'name_key'    => $nameKey,
                'value_key'   => $valueKey,
                'name'        => $name,
                'value'       => $value
            ]
        );
    }
}
// EOF
