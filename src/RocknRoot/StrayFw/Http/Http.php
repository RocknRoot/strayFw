<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Controllers;
use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Exception\NotARender;
use RocknRoot\StrayFw\Locale\Locale;
use RocknRoot\StrayFw\Render\RenderInterface;

/**
 * Bootstrapping class for HTTP requests.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Http
{
    /**
     * True if class has already been initialized.
     *
     * @static
     */
    private static bool $isInit = false;

    /**
     * Current namespace prefix.
     */
    protected static ?string $namespace = null;

    /**
     * Current subdomain prefix.
     *
     * @var string[]
     */
    protected static ?array $subdomain = null;

    /**
     * Current URI prefix.
     */
    protected static ?string $uri = null;

    /**
     * Registed routes.
     *
     * @var array[]
     */
    protected static array $routes = [];

    /**
     * Current raw request.
     */
    protected static ?\RocknRoot\StrayFw\Http\RawRequest $rawRequest = null;

    /**
     * Current request.
     */
    protected static ?\RocknRoot\StrayFw\Http\Request $request = null;

    /**
     * Current render.
     */
    protected static ?\RocknRoot\StrayFw\Http\Response $response = null;

    /**
     * Current controllers.
     *
     * @var object[]
     */
    protected static ?array $controllers = null;

    /**
     * Initialize inner states according to current HTTP request.
     *
     * @static
     */
    public static function init() : void
    {
        if (self::$isInit === false) {
            self::$isInit = true;
            self::$rawRequest = new RawRequest();
            Session::init();
            Locale::init(self::$rawRequest);
        }
    }

    /**
     * Launch the logic stuff. Http need to be initialized beforehand.
     *
     * @static
     * @throws AppException if raw request is not defined
     * @throws NotARender   if response->render is a non RenderInterface implementing object
     */
    public static function run() : void
    {
        if (self::$isInit === true) {
            if ((self::$rawRequest instanceof RawRequest) === false) {
                throw new AppException('Http\Helper: raw request is not defined');
            }
            self::$request = new Request(self::$rawRequest, self::$routes);
            self::$controllers = array();
            self::$response = new Response();
            try {
                \ob_start();
                $before = self::$request->getBefore();
                foreach ($before as $b) {
                    $controller = Controllers::get($b['class']);
                    $action = $b['action'];
                    $controller->$action(self::$request, self::$response);
                    if (self::$request->hasEnded() === true) {
                        break;
                    }
                }
                if (self::$request->hasEnded() === false) {
                    $controller = Controllers::get(self::$request->getClass());
                    $action = self::$request->getAction();
                    $controller->$action(self::$request, self::$response);
                    if (self::$request->hasEnded() === false) {
                        $after = self::$request->getAfter();
                        foreach ($after as $a) {
                            $controller = Controllers::get($a['class']);
                            $action = $a['action'];
                            $controller->$action(self::$request, self::$response);
                        }
                    }
                }
                $render = self::$response->getRender();
                if (!($render instanceof RenderInterface)) {
                    throw new NotARender('response->render is a non RenderInterface implementing object');
                }
                echo $render->render(self::$response->data);
                \ob_end_flush();
            } catch (\Exception $e) {
                \ob_end_clean();
                throw $e;
            }
        }
    }

    /**
     * Set namespace, subdomain and url prefixes for incoming routes.
     *
     * @static
     * @param string               $namespace namespace prefix
     * @param null|string|string[] $subdomain subdomain prefix
     * @param string               $uri       uri prefix
     */
    public static function prefix(string $namespace, $subdomain = null, string $uri = null) : void
    {
        self::$namespace = $namespace;
        self::$subdomain = \is_string($subdomain) ? [ $subdomain ] : $subdomain;
        self::$uri = $uri;
    }

    /**
     * Add route to be considered.
     *
     * @static
     * @param string          $method route HTTP method
     * @param string          $path   route path
     * @param string|string[] $action class(es) and method(s) to call
     */
    public static function route(string $method, string $path, $action) : void
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'route',
                'method' => $method,
                'path' => $path,
                'action' => \is_array($action) ? $action : [ $action ],
                'namespace' => self::$namespace,
                'subdomain' => self::$subdomain,
                'uri' => self::$uri
            );
        }
    }

    /**
     * Add before hook to be considered.
     *
     * @static
     * @param string          $method route HTTP method
     * @param string          $path   route path
     * @param string|string[] $action class(es) and method(s) to call
     */
    public static function before(string $method, string $path, $action) : void
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'before',
                'method' => $method,
                'path' => $path,
                'action' => \is_array($action) ? $action : [ $action ],
                'namespace' => self::$namespace,
                'subdomain' => self::$subdomain,
                'uri' => self::$uri
            );
        }
    }

    /**
     * Add before hook to be considered.
     *
     * @static
     * @param string          $method route HTTP method
     * @param string          $path   route path
     * @param string|string[] $action class(es) and method(s) to call
     */
    public static function after(string $method, string $path, $action) : void
    {
        if (self::$isInit === true) {
            self::$routes[] = array(
                'type' => 'after',
                'method' => $method,
                'path' => $path,
                'action' => \is_array($action) ? $action : [ $action ],
                'namespace' => self::$namespace,
                'subdomain' => self::$subdomain,
                'uri' => self::$uri
            );
        }
    }

    /**
     * Get all registered routes.
     *
     * @return array[] all routes
     */
    public static function getRoutes() : array
    {
        return self::$routes;
    }

    /**
     * Get current raw request.
     *
     * @static
     * @return null|RawRequest
     */
    public static function getRawRequest() : ?RawRequest
    {
        return self::$rawRequest;
    }

    /**
     * Get current request.
     *
     * @static
     */
    public static function getRequest() : ?\RocknRoot\StrayFw\Http\Request
    {
        return self::$request;
    }
}
