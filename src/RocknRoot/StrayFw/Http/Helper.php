<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Exception\AppException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Useful functions for the framework users.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Helper
{
    /**
     * Extract domain from HTTP request.
     *
     * @static
     * @param  HttpRequest $request base http request
     * @return string      domain
     */
    public static function extractDomain(HttpRequest $request): string
    {
        if (\preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $request->getHost(), $matches)) {
            return $matches['domain'];
        }
        return 'localhost';
    }

    /**
     * Get nice URL.
     *
     * @static
     * @param  string       $url URL
     * @throws AppException if request is not defined
     * @throws AppException if HTTP request is not defined
     * @return string       nice URL
     */
    public static function niceUrl(string $url): string
    {
        $nice = null;
        if (($pos = \stripos($url, '.')) !== false) {
            list($subDomain, $url) = \explode('.', $url);
            $request = Http::getRequest();
            if (!$request) {
                throw new AppException('Http\Helper: request is not defined');
            }
            $nice = $request->getHttpRequest()->getScheme() . '://';
            if ($subDomain != null) {
                $nice .= $subDomain . '.';
            }
            $nice .= self::extractDomain($request->getHttpRequest());
        }
        return $nice . '/' . \ltrim((string) \preg_replace('/\/+/', '/', $url), '/');
    }
}
