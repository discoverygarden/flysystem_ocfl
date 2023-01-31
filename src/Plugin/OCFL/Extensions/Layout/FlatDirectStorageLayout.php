<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Core\Plugin\PluginBase;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;

/**
 * Map an ID according to extension 0002.
 *
 * @see https://ocfl.github.io/extensions/0002-flat-direct-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0002-flat-direct-storage-layout"
 * )
 */
class FlatDirectStorageLayout extends PluginBase implements OCFLLayoutInterface {

  /**
   * {@inheritDoc}
   */
  public function mapToPath(string $id) : string {
    return $id;
  }

}
