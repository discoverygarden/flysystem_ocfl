<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Component\Plugin\ConfigurableInterface as UpstreamConfigurableInterface;

/**
 * Extended configurable interface.
 */
interface ConfigurableInterface extends UpstreamConfigurableInterface {

  /**
   * Allow extensions to load their config as they define it.
   *
   * @param string $root
   *   The OCFL storage root.
   *
   * @return $this
   *   The current instance.
   */
  public function loadConfiguration(string $root);

}
