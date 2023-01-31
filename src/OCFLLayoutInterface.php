<?php

namespace Drupal\flysystem_ocfl;

/**
 * OCFL layout interface.
 */
interface OCFLLayoutInterface {

  /**
   * Given an object ID, locate the object within the root storage.
   *
   * @param string $id
   *   The object ID to lookup.
   *
   * @return string
   *   The resolved path.
   */
  public function mapToPath(string $id) : string;

}
