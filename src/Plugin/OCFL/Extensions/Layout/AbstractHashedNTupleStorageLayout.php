<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;

/**
 * Map an ID using hashed tuples, as used by some implementations.
 */
abstract class AbstractHashedNTupleStorageLayout extends PluginBase implements OCFLLayoutInterface, ConfigurableInterface {

  use TupleTrait {
    defaultPluginConfiguration as traitDefaultPluginConfiguration;
  }

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritDoc}
   */
  protected function getBasis(string $id) : array {
    return [
      'basis' => hash($this->configuration['digestAlgorithm'], $id),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultPluginConfiguration() : array {
    return $this->traitDefaultPluginConfiguration() + [
      'digestAlgorithm' => 'sha256',
    ];
  }

}
