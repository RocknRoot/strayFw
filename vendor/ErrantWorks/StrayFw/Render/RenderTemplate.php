<?php

namespace ErrantWorks\StrayFw\Render;

use ErrantWorks\StrayFw\Http\Request;
use ErrantWorks\StrayFw\Render\ArgsTrait;
use ErrantWorks\StrayFw\Render\Twig;

/**
 * Template render class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderTemplate implements RenderInterface
{
    use ArgsTrait;

    /**
     * Associated request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Template file name.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Construct render with base arguments.
     *
     * @param Request $request associated request
     * @param string $fileName template file name
     * @param array $args base arguments
     */
    public function __construct(Request $request, $fileName, array $args = array())
    {
        $this->args = $args;
        $this->request = $request;
        $this->fileName = $fileName;
    }

    /**
     * Return the generated display.
     *
     * @return string content
     */
    public function render()
    {
        $env = Twig::getEnv($this->request->getDir());
        $template = $env->loadTemplate($this->fileName);
        return $template->render($this->args);
    }
}
