<?php

namespace RocknRoot\StrayFw\Http;

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
     * Set render object.
     *
     * @param RenderInterface $render render object
     */
    public function setRender(RenderInterface $render)
    {
        $this->render = render;
    }
}
