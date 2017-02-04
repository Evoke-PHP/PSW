<?php
/**
 * Cam
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

use PDO;

/**
 * Cam
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
 */
class Cam
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Cam constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function store(array $measurements, array $camData)
    {
        $stmt = $this->pdo->prepare(<<<SQL
INSERT INTO
    weather_reading_cam (weather_reading_id, image_url, url, measurement_time, cam_title)
    VALUES (:weather_reading_id, :image_url, :url, FROM_UNIXTIME(:measurement_time), :cam_title)
SQL
        );

        foreach ($camData as $locationID => $cam) {
            $stmt->execute(['weather_reading_id' => $measurements[$locationID]] + $cam);
        }
    }
}
// EOF
