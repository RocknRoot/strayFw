<?php

namespace RocknRoot\StrayFw\Http;

/**
 * Parsed data from HTTP request before logical routing.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RawRequest
{
    /**
     * Request HTTP scheme.
     *
     * @var string
     */
    protected $scheme;

    /**
     * Requested host name.
     *
     * @var string
     */
    protected $host;

    /**
     * Requested sub domain.
     *
     * @var string
     */
    protected $subDomain;

    /**
     * Requested URI.
     *
     * @var string
     */
    protected $query;

    /**
     * Request HTTP method.
     *
     * @var string
     */
    protected $method;

    /**
     * GET variables.
     *
     * @var array
     */
    protected $getVars;

    /**
     * POST variables.
     *
     * @var array
     */
    protected $postVars;

    /**
     * Fill properties with current HTTP request.
     */
    public function __construct()
    {
        if (empty($_SERVER['HTTPS']) === false && $_SERVER['HTTPS'] !== 'off') {
            $this->scheme = 'https';
        } else {
            $this->scheme = 'http';
        }
        $this->host = $_SERVER['SERVER_NAME'];
        $this->subDomain = $this->host;
        if (preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $this->subDomain, $matches)) {
            $this->subDomain = $matches['domain'];
        }
        $this->subDomain = rtrim(strstr($this->host, $this->subDomain, true), '.');
        $this->query = str_replace('/index.php', null, $_SERVER['REQUEST_URI']);
        if (($pos = stripos($this->query, '?')) !== false) {
            $this->query = substr($this->query, 0, stripos($this->query, '?'));
        }
        $this->query = rtrim($this->query, '/');
        if (strlen($this->query) == 0) {
            $this->query = '/';
        }
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->getVars = $_GET;
        $this->postVars = $_POST;
    }

    /**
     * Get request HTTP scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get requested host name.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get requested sub domain.
     *
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * Get requested URI.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get request HTTP method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get GET variables.
     *
     * @return array
     */
    public function getGetVars()
    {
        return $this->getVars;
    }

    /**
     * Get POST variables.
     *
     * @return array
     */
    public function getPostVars()
    {
        return $this->postVars;
    }
}
