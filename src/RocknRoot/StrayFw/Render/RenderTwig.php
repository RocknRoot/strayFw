<?php

namespace RocknRoot\StrayFw\Render;

use RocknRoot\StrayFw\Http\Request;

/**
 * Twig template render class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderTwig implements RenderInterface
{
    /**
     * Associated request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Templates files directory.
     *
     * @var string
     */
    protected $templatesDir;

    /**
     * Template file name.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Construct render with base arguments.
     *
     * @param Request $request      associated request
     * @param string  $templatesDir templates directory
     * @param string  $fileName     template file name
     */
    public function __construct(Request $request, string $templatesDir, string $fileName)
    {
        $this->request = $request;
        $this->templatesDir = DIRECTORY_SEPARATOR . ltrim(rtrim($templatesDir, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        $this->fileName = $fileName;
    }

    /**
     * Return the generated display.
     *
     * @return string content
     */
    public function render(array $args) : string
    {
        $env = Twig::getEnv($this->templatesDir);
        $template = $env->loadTemplate($this->fileName);
        if (isset($args['request']) === false) {
            $args['request'] = $this->request;
        }

        return $template->render($args);
    }
}
