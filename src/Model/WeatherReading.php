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

    /**
     * Get the locations as an array indexed by their feed id's.
     *
     * @param string $feedId The feed id must not come from user input.
     * @return array
     */
    public function getLocations(string $feedId) : array
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
