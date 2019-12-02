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
     * Response data.
     *
     * @var array
     */
    public $data;

    /**
     * Render object.
     *
     * @var null|\RocknRoot\StrayFw\Render\RenderInterface
     */
    protected $renderInst;

    /**
     * Construct response with default values.
     */
    public function __construct()
    {
        $this->status = 200;
        $this->data = [];
        $this->renderInst = null;
    }

    /**
     * Get set render object.
     *
     * @return null|\RocknRoot\StrayFw\Render\RenderInterface
     */
    public function getRender() : ?\RocknRoot\StrayFw\Render\RenderInterface
    {
        return $this->renderInst;
    }

    /**
     * Set render and status.
     *
     * @param RenderInterface $render render object
     * @param int             $status status code
     */
    public function render(RenderInterface $render, int $status = 200)
    {
        $this->renderInst = $render;
        $this->status = $status;
    }
}
