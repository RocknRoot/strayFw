<?php

namespace RocknRoot\StrayFw\Database;

/**
 * Useful functions.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Helper
{
    /**
     * Apply these rules to a name :
     *    *  a sequence beginning with a lowercase letter must be followed by lowercase letters and digits
     *    *  a sequence beginning with an uppercase letter can be followed by either :
     *       *  uppercase letters and digits followed by either :
     *          *  end of the string
     *          *  an uppercase letter followed by a lowercase letter or digit
     *       *  lowercase letters or digits
     * ex : CamelCase => camel_case
     *
     * @static
     * @param  string $name model name
     * @return string codified model name
     */
    public static function codifyName(string $name): string
    {
        $matches = array();
        \preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = (\strtoupper($match) == $match ? \strtolower($match) : \lcfirst($match));
        }
        return \implode('_', $ret);
    }
}
