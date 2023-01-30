<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

/**
 * Map an ID according to extension 0004.
 *
 * @see https://ocfl.github.io/extensions/0004-hashed-n-tuple-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0004-hashed-n-tuple-storage-layout"
 * )
 */
class HashedNTupleStorageLayout extends AbstractHashedNTupleStorageLayout {

  /**
   * {@inheritDoc}
   */
  public function defaultPluginConfiguration() : array {
    return parent::defaultPluginConfiguration() + [
      'shortObjectRoot' => FALSE,
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function getFinalPart(string $id, array $basis) : string {
    return $this->configuration['shortObjectRoot'] ?
      substr($basis['basis'], $this->configuration['tupleSize'] * $this->configuration['numberOfTuples']) :
      $basis['basis'];
  }

}
