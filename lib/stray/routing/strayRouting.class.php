<?php
/**
 * @brief Bootstrap router.
 * @singleton
 * @author nekith@gmail.com
 */

final class strayRouting extends strayASingleton
{
  /**
   * Host in the request.
   * @var string
   */
  private $_host;

  /**
   * Resolve routing request URL.
   * @param string $url request URL
   * @param string $method request HTTP method
   * @param bool $debug true if debug mode
   * @return strayRoutingRequest internal routing request
   */
  public function Route($url, $method, $debug = false)
  {
    $request = new strayRoutingRequest($url, $method, $debug);
    $components = parse_url($url);
    if (false === $components)
      throw new strayExceptionError('can\'t execute this request');
    $components['method'] = $method;
    if (false === isset($components['path']))
      $components['path'] = '/';
    else
      $components['path'] = rtrim($components['path'], '/') . '/';
    $this->_ResolveRoutes($components, $request);
    return $request;
  }

  /**
   * Resolve routes.
   * @param array $components URL components
   * @param strayRoutingRequest $request request
   */
  private function _ResolveRoutes(array $components, strayRoutingRequest $request)
  {
    $installRoutes = strayConfigInstall::fGetInstance()->GetRoutes();
    if (true === isset($installRoutes['routes']))
    {
      // install routes
      $defaultApp = null;
      foreach ($installRoutes['routes'] as $route)
      {
        if (false === isset($route['app']))
          throw new strayExceptionError('install routes : no app for route ' . var_export($route, true));
        if (false === isset($route['subdomain']) && false === isset($route['url']))
          $defaultApp = $route['app'];
        else
        {
          if (true === isset($route['subdomain']))
          {
            if (0 === stripos($components['host'], $route['subdomain'] . '.'))
            {
              $request->app = $route['app'];
              break;
            }
          }
          if (true === isset($route['url']))
          {
            if (0 === stripos($components['path'], $route['url']))
            {
              $request->app = $route['app'];
              if (1 < strlen($components['path']))
                $components['path'] = rtrim(substr($route['app'], strlen($components['path'])), '/') . '/';
              break;
            }
          }
        }
      }
      if (null === $request->app)
      {
        if (null == $defaultApp)
          throw new strayExceptionError('install routes : can\'t find default app');
        $request->app = $defaultApp;
      }
      // app routes
      $appRoutes = strayConfigApp::fGetInstance($request->app)->GetRoutes();
      if (false === isset($appRoutes['routes']))
        throw new strayExceptionError('app routes : no routes');
      foreach ($appRoutes['routes'] as $route)
      {
        if (false === isset($route['url']))
          throw new strayExceptionError('app routes : route has no url ' . var_export($route, true));
        if (false === isset($route['view']))
          throw new strayExceptionError('app routes : route has no view ' . var_export($route, true));
        if (false === isset($route['method']) || $components['method'] == 'GET' || $route['method'] == $components['method'])
        {
          if (false === isset($route['ajax']) || $request->IsAjax() == $route['ajax'])
          {
            $matches = null;
            if (1 < strlen($route['url']))
              $route['url'] = rtrim($route['url'], '/') . '/';
            if (true === isset($components['path']) && 1 === preg_match('#^' . $route['url'] . '$#', $components['path'], $matches))
            {
              list($widget, $view) = explode('.', $route['view']);
              $request->widget = $widget;
              $request->view = $view;
              array_walk($matches, function($v, $k) use ($request)
              {
                if (false === is_numeric($k) && null != $v)
                  $request->params[$k] = $v;
              });
              break;
            }
          }
        }
      }
      list($widget, $view) = explode('.', $route['view']);
      $request->widget = $widget;
      $request->view = $view;
    }
  }

  /**
   * Set host.
   * @param string $host host
   */
  public function SetHost($host)
  {
    $this->_host = $host;
  }

  /**
   * Get the host in the request.
   * @return string host
   */
  public function GetHost()
  {
    return $this->_host;
  }

  /**
   * Get nice url for specified $url.
   * @param string $url source url
   * @return string nice url
   */
  static public function fGenerateNiceUrl($url)
  {
    $nice = null;
    if (false !== strpos($url, '.'))
    {
      list($subdomain, $url) = explode('.', $url);
      $nice = strayRoutingBootstrap::fGetInstance()->GetRequest()->GetScheme() . '://';
      if (null != $subdomain)
        $nice .= $subdomain . '.';
      $nice .= strayRoutingBootstrap::fGetInstance()->GetRequest()->GetDomain();
    }
    return $nice . '/' . ltrim(preg_replace('/\/+/', '/', $url), '/');
  }

  /**
   * Get nice url for specified $route.
   * @param string $route route name
   * @param array $args route args
   * @return string nice url
   */
  static public function fGenerateNiceUrlForRoute($route, $args = array())
  {
    $app = strayRoutingBootstrap::fGetInstance()->GetRequest()->app;
    $url = null;
    if (false !== strpos($route, '.'))
    {
      list($app, $route) = explode('.', $route);
      $routes = strayConfigInstall::fGetInstance()->GetRoutes();
      $url = $routes['routes'][$app]['subdomain'] . '.';
    }
    $routes = strayConfigApp::fGetInstance($app)->GetRoutes();
    if (null == $routes)
    {
      strayLog::fGetInstance()->Error('can\'t find app "' . $app . '" for route view helper');
      return null;
    }
    if (false === isset($routes['routes'][$route]))
    {
      strayLog::fGetInstance()->Error('can\'t find route "' . $route . '" for route view helper');
      return null;
    }
    $url .= $routes['routes'][$route]['url'];
    foreach ($args as $name => $value)
      $url = preg_replace('/\(\?<' . $name . '>(.*?)\)/', $value, $url);
    // clear optional parts
    $url = preg_replace('/\(\?<(\w)+>(.*?)\)[?*]/', null, $url);
    $url = str_replace([ '(', ')', '?' ], null, $url);
    return self::fGenerateNiceUrl(rtrim($url, '/'));
  }
}
