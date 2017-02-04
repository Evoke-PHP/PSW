<?php
/**
 * Comment
 *
 * @package   PSW\Controller
 */
namespace PSW\Controller;

use PSW\Model;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\PhpRenderer;

/**
 * Comment
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2017 Paul Young
 * @package   PSW\Controller
 */
class Comment
{
    /**
     * @var Model\Comment
     */
    protected $modelComment;

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Comment constructor.
     *
     * @param Model\Comment $modelComment
     * @param PhpRenderer   $renderer
     */
    public function __construct(Model\Comment $modelComment, PhpRenderer $renderer)
    {
        $this->modelComment = $modelComment;
        $this->renderer     = $renderer;
    }

    public function addComment(Request $request, Response $response, array $args)
    {
        $this->modelComment->store($args + $request->getParsedBody());
        return $response->withStatus(302)->withHeader('Location', '/weather/measurement/' . $args['measurement']);
    }

}
// EOF
