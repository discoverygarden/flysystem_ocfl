<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;

/**
 * Map an ID according to extension 0004.
 *
 * @see https://ocfl.github.io/extensions/0004-hashed-n-tuple-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0004-hashed-n-tuple-storage-layout"
 * )
 */
class HashedNTupleStorageLayout extends PluginBase implements OCFLLayoutInterface, ConfigurableInterface {

  /**
   * {@inheritDoc}
   */
  public function mapToPath($id) : string {
    $hash = hash($this->configuration['digestAlgorithm'], $id);

    $parts = [];
    for ($i = 0; $i < $this->configuration['numberOfTuples']; $i++) {
      $parts[] = substr($hash, $i * $this->configuration['tupleSize'], $this->configuration['tupleSize']);
    }
    $parts[] = $this->configuration['shortObjectRoot'] ?
      substr($hash, $this->configuration['tupleSize'] * $this->configuration['numberOfTuples']) :
      $hash;

    return implode('/', $parts);
  }

  /**
   * {@inheritDoc}
   */
  public function getConfiguration() : array {
    return $this->configuration;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfiguration(array $configuration) : void {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() : array {
    return [
      'digestAlgorithm' => 'sha256',
      'tupleSize' => 3,
      'numberOfTuples' => 3,
      'shortObjectRoot' => FALSE,
    ];
  }

}
