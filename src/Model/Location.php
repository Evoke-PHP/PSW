<?php
/**
 * Location
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

use DomainException;
use PDO;

/**
 * Location
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
 */
class Location
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Comment constructor.
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
     * @param string $feedID
     * @return array
     */
    public function getLocations(string $feedID) : array
    {
        if (!in_array($feedID, ['cam_id', 'openweathermap_id', 'yahoo_id'])) {
            throw new DomainException('Feed ID: ' . $feedID . ' is outside of our known feeds');
        }

        $stmt = $this->pdo->query('SELECT id,' . $feedID . ',lat,lng FROM location');
        $locations = [];

        while ($row = $stmt->fetch(PDO::FETCH_GROUP|PDO::FETCH_ASSOC)) {
            $locations[$row[$feedID]] = $row;
        }

        return $locations;
    }

    /**
     * Update the cameras used for the cam feed.
     */
    public  function updateCams(array $cams)
    {
        $stmt = $this->pdo->prepare('UPDATE location SET cam_id = :cam_id WHERE id=:id');

        foreach ($cams as $id => $cam) {
            $stmt->execute([':id' => $id, ':cam_id' => $cam]);
        }
    }
}
// EOF
