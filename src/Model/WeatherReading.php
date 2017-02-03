<?php
/**
 * WeatherReading
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

use DateTime;
use DateInterval;
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
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $pages;

    /**
     * @var string
     */
    protected $timeFilter = 'day';

    /**
     * WeatherReading constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addCam($cam)
    {
        $stmt = $this->pdo->prepare(<<<SQL
INSERT INTO
    weather_reading_cam (weather_reading_id, image_url, url, measurement_time)
    VALUES (:weather_reading_id,:image_url,:url,:measurement_time)
SQL
        );

        $stmt->execute([
            ':weather_reading_id' => $cam['measurement'],
            ':image_url'          => $cam['image_url'],
            ':url'                => $cam['url'],
            'measurement_time'    => $cam['measurement_time']
        ]);
    }

    public function getPage() : int
    {
        return $this->currentPage;
    }

    public function getPages() : int
    {
        return $this->pages;
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
    weather_reading_comment.comment_text,
    weather_reading_comment.comment_time,
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
                    'id'                => $row['id'],
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
                    'comment_text' => $row['comment_text'],
                    'comment_time' => $row['comment_time'],
                    'username'     => $row['username']
                ];
            }
        }

        return $reading;
    }

    public function getSummaryDataByTimeInterval() : array
    {
        $this->pdo->beginTransaction();
        $oldestMeasurement = $this->pdo->query('SELECT MIN(weather_reading.measurement_time) FROM weather_reading');
        $timeOffset = new DateTime;
        $periodOfMeasurements = $timeOffset->diff(new DateTime($oldestMeasurement->fetchColumn()));
        $daysOfMeasurements = $periodOfMeasurements->format('%a');
        $this->pages = ceil($daysOfMeasurements / ($this->timeFilter === 'week' ? 7 : 1));

        if ($this->currentPage > $this->pages) {
            $this->currentPage = $this->pages;
        }

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
    weather_reading.measurement_time >= :time_from AND
    weather_reading.measurement_time <= :time_to
GROUP BY
    weather_reading.id
ORDER BY
    intervals_ago,location_id,measurement_time
SQL
        );

        $periodIndicator = $this->timeFilter === 'week' ? 'W' : 'D';
        $timeOffset->sub(new DateInterval('P' . (string)($this->currentPage - 1) . $periodIndicator));
        $timeTo = $timeOffset->format('Y-m-d H:i:s');
        $timeOffset->sub(new DateInterval('P1' . $periodIndicator));
        $timeFrom = $timeOffset->format('Y-m-d H:i:s');

        $stmt->execute(
            [
                ':time_from' => $timeFrom,
                ':time_to'   => $timeTo
            ]);

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

    public function getTimeFilter() : string
    {
        return $this->timeFilter;
    }

    public function setPage(int $currentPage)
    {
        $this->currentPage = max($currentPage, 1);
    }

    public function setTimeFilter(string $filter)
    {
        if ($filter !== 'day' && $filter !== 'week') {
            trigger_error(
                'Time filter should be either "day" or "week". Received: ' . var_export($filter, true) .
                ' No change made.',
                E_USER_NOTICE
            );
            return;
        }

        $this->timeFilter = $filter;
    }

    /**
     * Store the measurements returning the list of new weather_reading.id's created for each location.
     * @param array $measurements
     * @param int $count
     * @return array
     */
    public function store(array $measurements, int $count) : array
    {
        $locationsStored = [];

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare(<<<SQL
INSERT INTO
    weather_reading (location_id,measurement_time, icon, temperature, humidity, description, short_description)
    VALUES (:location_id, FROM_UNIXTIME(:measurement_time), :icon, :temperature, :humidity, :description, :short_description) 
SQL
        );

        foreach ($measurements as $reading) {
            $stmt->execute($reading);
            $locationsStored[$reading['location_id']] = $this->pdo->lastInsertId();
        }

        if (count($locationsStored) !== $count) {
            $this->pdo->rollBack();
            throw new \RuntimeException(
                'Not all feed measurements could be recorded (only ' . count($locationsStored) . ' of ' . $count . ')' .
                ' for data: ' . var_export($measurements, true)
            );
        }

        $this->pdo->commit();

        return $locationsStored;
    }
}
// EOF
