<?php
/**
 * Interface.
 * @brief Bootstrap interface.
 * @author nekith@gmail.com
 */

interface strayRoutingIBootstrap
{
  /**
   * Get routing request.
   * @return strayRoutingRequest
   */
  public function GetRequest();

  /**
   * Bootstrapping the installation.
   * @param string $url routing requested URL
   * @param string $method HTTP method
   */
  public function Run($url, $method);
}
