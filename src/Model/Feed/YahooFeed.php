<?php
/**
 * YahooFeed
 *
 * @package   PSW\Model
 */
namespace PSW\Model\Feed;

use DateTime,
    DOMDocument,
    DomainException,
    RuntimeException;

/**
 * YahooFeed
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model\Feed
 */
class YahooFeed extends LocationFeed
{
    /**
     * @const The CDATA start string.
     * @todo Make this a private class constant when we start using PHP 7.1
     */
    const CDATA_START = '<![CDATA[';

    /**
     * @const The CDATA end string.
     * @todo Make this a private class constant when we start using PHP 7.1
     */
    const CDATA_END   = ']]>';

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $result = file_get_contents(
            'https://query.yahooapis.com/v1/public/yql?q=' .
            urlencode(
                'SELECT woeid,item.condition,item.description,atmosphere.humidity FROM weather.forecast ' .
                'WHERE woeid IN(' . implode(',', array_keys($this->locations)) . ') AND u="c"'
            ) . '&format=json'
        );

        if ($result === false) {
            throw new RuntimeException('Error connecting to yahoo weather feed.');
        }

        $decodedResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DomainException('Unable to decode: ' . var_export($result, true));
        }

        $formattedResults = [];

        foreach ($decodedResult['query']['results'] as $measurements) {
            foreach ($measurements as $measurement) {
                $item = $measurement['item'];
                list($icon, $location) = $this->parseDescription($item['description']);
                $measurementTime = new DateTime($item['condition']['date']);

                $formattedResults[] = [
                    'location_id'       => $this->locations[$location]['id'],
                    'measurement_time'  => $measurementTime->getTimestamp(),
                    'icon'              => $icon,
                    'temperature'       => $item['condition']['temp'],
                    'humidity'          => $measurement['atmosphere']['humidity'],
                    'description'       => $item['condition']['text'],
                    'short_description' => $item['condition']['text']
                ];
            }
        }

        return $formattedResults;
    }

    /**
     * @inheritDoc
     */
    public function getFeedID(): string
    {
        return 'yahoo_id';
    }

    /**
     * Parse the description field into an icon and a location.
     * @param $description
     * @return array
     */
    private function parseDescription($description)
    {
        if (!substr($description, 0, strlen(self::CDATA_START)) == self::CDATA_START ||
            !substr($description, - strlen(self::CDATA_END)) == self::CDATA_END) {
            throw new DomainException('Unable to parse expected CDATA section.');
        }

        $description = substr($description, strlen(self::CDATA_START), - strlen(self::CDATA_END));
        $doc = new DOMDocument();
        $doc->loadHTML($description);


        $icon = $doc->getElementsByTagName('img')[0]->getAttribute('src');
        $descriptionLinks = $doc->getELementsByTagName('a')[0]->getAttribute('href');

        if (preg_match('~([0-9]+)/$~', $descriptionLinks, $matches) !== 1) {
            throw new DomainException('Unable to get location from description.');
        }

        $location = $matches[1];

        return [$icon, $location];
    }
}
// EOF
