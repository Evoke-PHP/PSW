<?php
/**
 * WeatherReading
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

use PDO;

/**
 * WeatherReading
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
 */
class WeatherReading
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * WeatherReading constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getReading(int $readingID) : array
    {
        $stmt = $this->pdo->prepare(<<<SQL
SELECT
    location.name,
    weather_reading.id,
    weather_reading.measurement_time,
    weather_reading.description,
    weather_reading.humidity,
    weather_reading.icon,
    weather_reading.short_description,
    weather_reading.temperature,
    weather_reading_comment.id AS comment_id,
    weather_reading_comment.comment,
    weather_reading_comment.username
FROM
    weather_reading
    LEFT JOIN location ON weather_reading.location_id = location.id
    LEFT JOIN weather_reading_comment ON weather_reading.id = weather_reading_comment.weather_reading_id
WHERE
    weather_reading.id = :id
ORDER BY
    weather_reading_comment.id
SQL
        );
        $stmt->execute([':id' => $readingID]);

        $reading = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id        = $row['id'];
            $commentID = $row['comment_id'];

            if (!isset($reading[$id])) {
                $reading[$id] = [
                    'location'          => $row['name'],
                    'measurement_time'  => $row['measurement_time'],
                    'description'       => $row['description'],
                    'humidity'          => $row['humidity'],
                    'icon'              => $row['icon'],
                    'short_description' => $row['short_description'],
                    'temperature'       => $row['temperature'],
                    'comments'          => []
                ];
            }

            if (isset($commentID) && !isset($reading[$id]['comments'][$commentID])) {
                $reading[$id]['comments'][$commentID] = [
                    'comment'  => $row['comment'],
                    'username' => $row['username']
                ];
            }
        }

        return $reading;
    }

    public function getSummaryDataByTimeInterval() : array
    {
        $stmt = $this->pdo->prepare(<<<SQL
SELECT
    location.name,
    weather_reading.id,
    weather_reading.location_id,
    weather_reading.measurement_time,
    weather_reading.description,
    weather_reading.humidity,
    weather_reading.icon,
    weather_reading.short_description,
    weather_reading.temperature,
    COUNT(weather_reading_comment.id) AS comments,
    ROUND((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(weather_reading.measurement_time)) / (180 * 60)) AS intervals_ago
FROM
    weather_reading
    LEFT JOIN location ON weather_reading.location_id = location.id
    LEFT JOIN weather_reading_comment ON weather_reading.id = weather_reading_comment.weather_reading_id
WHERE
    weather_reading.measurement_time > (NOW() - INTERVAL 1 WEEK)
GROUP BY
    weather_reading.id
ORDER BY
    intervals_ago,location_id,measurement_time
SQL
        );
        $stmt->execute();

        $weatherByTimeInterval = [];
        $locations             = [];

        while ($reading = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $intervalsAgo = $reading['intervals_ago'];
            $readingID    = $reading['id'];
            $locationID   = $reading['location_id'];

            if (!isset($weatherByTimeInterval[$intervalsAgo])) {
                $weatherByTimeInterval[$intervalsAgo] = [];
            }

            if (!isset($locations[$locationID])) {
                $locations[$locationID] = $reading['name'];
            }

            if (!isset($weatherByTimeInterval[$intervalsAgo][$locationID])) {
                $weatherByTimeInterval[$intervalsAgo][$locationID] = [];
            }

            $weatherByTimeInterval[$intervalsAgo][$locationID][$readingID] = [
                'measurement_time'  => $reading['measurement_time'],
                'description'       => $reading['description'],
                'humidity'          => $reading['humidity'],
                'icon'              => $reading['icon'],
                'short_description' => $reading['short_description'],
                'temperature'       => $reading['temperature'],
                'comments'          => $reading['comments']
            ];
        }

        return ['locations' => $locations, 'time_intervals' => $weatherByTimeInterval];
    }

    /**
     * Get the locations as an array indexed by their feed id's.
     *
     * @param string $feedId The feed id must not come from user input.
     * @return array
     */
    public function getLocations(string $feedId): array
    {
        $stmt = $this->pdo->query('SELECT ' . $feedId . ',id FROM location');

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function store($measurements)
    {
        $stmt = $this->pdo->prepare(<<<SQL
INSERT INTO
    weather_reading (location_id,measurement_time, icon, temperature, humidity, description, short_description)
    VALUES (:location_id, FROM_UNIXTIME(:measurement_time), :icon, :temperature, :humidity, :description, :short_description) 
SQL
        );

        foreach ($measurements as $reading) {
            $stmt->execute($reading);
        }
    }
}
// EOF
