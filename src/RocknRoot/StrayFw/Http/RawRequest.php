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
     */
    protected string $scheme;

    /**
     * Requested host name.
     */
    protected string $host;

    /**
     * Requested sub domain.
     */
    protected string $subDomain;

    /**
     * Requested URI.
     */
    protected string $query;

    /**
     * Request HTTP method.
     */
    protected string $method;

    /**
     * GET variables.
     *
     * @var array<string, mixed>
     */
    protected array $getVars;

    /**
     * POST variables.
     *
     * @var array<string, mixed>
     */
    protected array $postVars;

    /**
     * Body JSON content.
     *
     * @var null|mixed
     */
    protected $jsonBodyVars;

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
        $this->host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->subDomain = $this->host;
        if (\preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $this->subDomain, $matches)) {
            $this->subDomain = $matches['domain'];
        }
        $this->subDomain = \rtrim((string) \strstr($this->host, $this->subDomain, true), '.');
        $query = \str_replace('/index.php', '', $_SERVER['REQUEST_URI'] ?? '');
        if (($pos = \stripos($query, '?')) !== false) {
            $query = \substr($query, 0, $pos);
        }
        $query = \rtrim($query, '/');
        if (\strlen($query) == 0) {
            $query = '/';
        }
        $this->query = $query;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'get';
        $this->getVars = $_GET;
        $this->postVars = $_POST;
        $body = \file_get_contents('php://input');
        if ($body) {
            $this->jsonBodyVars = \json_decode($body, true);
        }
    }

    /**
     * Get request HTTP scheme.
     *
     * @return string
     */
    public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
     * Get requested host name.
     *
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * Get requested sub domain.
     *
     * @return string
     */
    public function getSubDomain() : string
    {
        return $this->subDomain;
    }

    /**
     * Get requested URI.
     *
     * @return string
     */
    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * Get request HTTP method.
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Get GET variables.
     *
     * @return array<string, mixed>
     */
    public function getGetVars() : array
    {
        return $this->getVars;
    }

    /**
     * Get POST variables.
     *
     * @return array<string, mixed>
     */
    public function getPostVars() : array
    {
        return $this->postVars;
    }

    /**
     * Get JSON body variables.
     *
     * @return null|mixed
     */
    public function getJSONBodyVars()
    {
        return $this->jsonBodyVars;
    }
}
