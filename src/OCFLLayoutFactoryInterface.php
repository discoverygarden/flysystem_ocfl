<?php

namespace Drupal\flysystem_ocfl;

/**
 * Layout factory interface.
 */
interface OCFLLayoutFactoryInterface {

  /**
   * Get storage layout implementation for the given storage root.
   *
   * @param string $root
   *   An OCFL storage root for which to load a layout implementation.
   *
   * @return \Drupal\flysystem_ocfl\OCFLLayoutInterface
   *   The layout implementation.
   */
  public function getLayout(string $root) : OCFLLayoutInterface;

}
