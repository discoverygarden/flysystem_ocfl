<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;

/**
 * Map an ID according to extension 0006.
 *
 * @see https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0006-flat-omit-prefix-storage-layout"
 * )
 */
class FlatOmitPrefixStorageLayout extends PluginBase implements OCFLLayoutInterface, ConfigurableInterface {

  use ConfigurableTrait;

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
  public function mapToPath(string $id) : string {
    $sliced = substr($id, strrpos($id, $this->configuration['delimiter']));

    if (strlen($sliced) === 0) {
      throw new \InvalidArgumentException("Got zero-length path with delimiter '{$this->configuration['delimiter']}' in ID '{$id}'.");
    }

    return $sliced;
  }

  /**
   * {@inheritDoc}
   */
  protected function validateConfiguration(array $configuration) : void {
    if (!($configuration['delimiter'] ?? FALSE)) {
      throw new \LogicException('Missing "delimiter" configuration.');
    }
  }

}
