<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Exception\AppException;

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
     * Extract domain from raw request.
     *
     * @static
     * @param  RawRequest $rawRequest base raw request
     * @return string     domain
     */
    public static function extractDomain(RawRequest $rawRequest): string
    {
        if (\preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $rawRequest->getHost(), $matches)) {
            return $matches['domain'];
        }

        return 'localhost';
    }

    /**
     * Get nice URL.
     *
     * @static
     * @param  string       $url raw URL
     * @throws AppException if request is not defined
     * @throws AppException if raw request is not defined
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
            $nice = $request->getRawRequest()->getScheme() . '://';
            if ($subDomain != null) {
                $nice .= $subDomain . '.';
            }
            $nice .= self::extractDomain($request->getRawRequest());
        }

        return $nice . '/' . \ltrim((string) \preg_replace('/\/+/', '/', $url), '/');
    }
}
