<?php
/**
 * CamFeed
 *
 * @package   PSW\Model
 */
namespace PSW\Model\Feed;

use DomainException;
use RuntimeException;

/**
 * CamFeed
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model\Feed
 */
class CamFeed extends LocationFeed
{
    const API_KEY_HEADER = "X-Mashape-Key: 6TaGC4ecu0mshMm9XOqSA2ecTE0fp1V7kPJjsnez5bwA3SePXy\r\n";
    const BASE_URL = 'https://webcamstravel.p.mashape.com/webcams/';

    /**
     * @inheritDoc
     */
    public function getFeedID(): string
    {
        return 'cam_id';
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $context = stream_context_create(['http' => ['method' => 'GET', 'header' => self::API_KEY_HEADER]]);

        $result = file_get_contents(
            self::BASE_URL . 'list/webcam=' . implode(',', array_keys($this->locations)) .
            '?show=webcams:image,url',
            false,
            $context
        );

        if ($result === false) {
            throw new RuntimeException('Error connecting to cam feed.');
        }

        $decodedResult = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DomainException('Unable to decode: ' . var_export($result, true));
        }

        if (!isset($decodedResult['result']['webcams']) || !is_array($decodedResult['result']['webcams'])) {
            throw new RuntimeException('Unable to parse result: ' . var_export($decodedResult, true));
        }

        $formattedResults = [];

        foreach ($decodedResult['result']['webcams'] as $cam) {
            $formattedResults[$this->locations[$cam['id']]['id']] = [
                'image_url'        => $cam['image']['current']['preview'],
                'url'              => $cam['url']['current']['desktop'],
                'measurement_time' => $cam['image']['update'],
                'cam_title'        => $cam['title']
            ];
        }

        return $formattedResults;
    }

    public function getUpdatedCams()
    {
        $updatedCams = [];

        foreach ($this->locations as $loc) {
            $context = stream_context_create(['http' => ['method' => 'GET', 'header' => self::API_KEY_HEADER]]);

            $result = file_get_contents(
                self::BASE_URL . 'list/nearby=' . $loc['lat'] . ',' . $loc['lng'] . ',30/orderby=distance/limit=1',
                false,
                $context
            );

            if ($result === false) {
                throw new RuntimeException('Error connecting to cam feed.');
            }

            $decodedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DomainException('Unable to decode: ' . var_export($result, true));
            }

            $firstResult = $decodedResult['result']['webcams'][0]['id'] ?? false;

            if ($firstResult && $firstResult != $loc['cam_id']) {
                $updatedCams[$loc['id']] = $firstResult;
            }
        }

        return $updatedCams;
    }
}
// EOF
