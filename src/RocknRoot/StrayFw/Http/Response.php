<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Render\RenderInterface;

/**
 * Response class for HTTP requests.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Response
{
    /**
     * HTTP status.
     *
     * @var int
     */
    public $status;

    /**
     * Render object.
     *
     * @var Render\RenderInterface
     */
    protected $render;

    /**
     * Construct response with default values.
     */
    public function __construct()
    {
        $this->status = 200;
        $this->render = null;
    }

    /**
     * Get current HTTP status code.
     *
     * @return int
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * Set render and status.
     *
     * @param RenderInterface $render render object
     * @param int $status status code
     */
    public function render(RenderInterface $render, $status = 200)
    {
        $this->render = $render;
        $this->status = $status;
    }
}
