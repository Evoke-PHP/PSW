<?php
/**
 * Comment
 *
 * @package   PSW\Model
 */
namespace PSW\Model;

/**
 * Comment
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Model
 */
class Comment
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

    public function addComment($comment)
    {
        $stmt = $this->pdo->prepare(<<<SQL
INSERT INTO
    weather_reading_comment (weather_reading_id, username, comment_text)
    VALUES (:weather_reading_id,:username,:comment_text)
SQL
        );

        $stmt->execute([
            ':weather_reading_id' => $comment['measurement'],
            ':username'           => $comment['name'],
            ':comment_text'       => $comment['comment']
        ]);
    }
}
// EOF
