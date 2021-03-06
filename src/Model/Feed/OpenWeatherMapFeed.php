<?php
/**
 * OpenWeatherMapFeed
 *
 * @package   PSW\Model
 */
namespace PSW\Model\Feed;

use DomainException,
    RuntimeException;

/**
 * OpenWeatherMapFeed
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model\Feed
 */
class OpenWeatherMapFeed extends LocationFeed
{
    /**
     * The api key for our open weather map feed.
     * @var string
     */
    protected $apiKey;

    /**
     * OpenWeatherMapFeed constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @inheritDoc
     */
    public function getData() : array
    {
        $result = file_get_contents(
            'http://api.openweathermap.org/data/2.5/group?id=' . implode(',', array_keys($this->locations)) .
            '&units=metric&APPID=' . $this->apiKey
        );

        if ($result === false) {
            throw new RuntimeException('Error connecting to openweathermap data feed.');
        }

        $decodedResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DomainException('Unable to decode: ' . var_export($result, true));
        }

        $formattedResults = [];

        foreach ($decodedResult['list'] as $measurement) {
            $formattedResults[] = [
                'location_id'       => $this->locations[$measurement['id']]['id'],
                'measurement_time'  => $measurement['dt'],
                'icon'              => 'http://openweathermap.org/img/w/' . $measurement['weather'][0]['icon'] . '.png',
                'temperature'       => $measurement['main']['temp'],
                'humidity'          => $measurement['main']['humidity'],
                'description'       => $measurement['weather'][0]['description'],
                'short_description' => $measurement['weather'][0]['main']
            ];
        }

        return $formattedResults;
    }

    /**
     * @inheritDoc
     */
    public function getFeedID(): string
    {
        return 'openweathermap_id';
    }
}
// EOF
