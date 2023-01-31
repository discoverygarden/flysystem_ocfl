<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Component\Plugin\ConfigurableInterface;

/**
 * Map an ID according to extension 0007.
 *
 * @see https://ocfl.github.io/extensions/0007-n-tuple-omit-prefix-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0007-n-tuple-omit-prefix-storage-layout"
 * )
 */
class NTupleOmitPrefixStorageLayout extends FlatOmitPrefixStorageLayout implements ConfigurableInterface {

  use TupleTrait {
    defaultPluginConfiguration as traitDefaultPluginConfiguration;
  }

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!in_array($this->configuration['zeroPadding'], ['left', 'right'], TRUE)) {
      throw new \LogicException('Unknown value for "zeroPadding".');
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function getBasis(string $id) : array {
    $sliced = parent::mapToPath($id);
    $padded = str_pad(
      $sliced,
      $this->configuration['numberOfTuples'] * $this->configuration['tupleSize'],
      '0',
      $this->configuration['zeroPadding'] === 'left' ? STR_PAD_LEFT : STR_PAD_RIGHT
    );
    return [
      'basis' => $this->configuration['reverseObjectRoot'] ? strrev($padded) : $padded,
      'base_id' => $sliced,
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function getFinalPart(string $id, array $basis) : string {
    return $basis['base_id'];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultPluginConfiguration() : array {
    return $this->traitDefaultPluginConfiguration() + [
      'zeroPadding' => 'left',
      'reverseObjectRoot' => FALSE,
    ];
  }

}
