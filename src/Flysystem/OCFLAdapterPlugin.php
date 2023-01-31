<?php

namespace Drupal\flysystem_ocfl\Flysystem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use Drupal\flysystem_ocfl\Flysystem\Adapter\OCFL;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * OCFL adapter plugin.
 *
 * @Adapter(
 *   id = "ocfl"
 * )
 */
class OCFLAdapterPlugin implements FlysystemPluginInterface, ContainerFactoryPluginInterface {

  use FlysystemUrlTrait;

  /**
   * The root of the OCFL storage.
   *
   * @var string
   */
  protected string $root;

  /**
   * Prefix for IDs being mapped.
   *
   * @var string
   */
  protected string $idPrefix;

  /**
   * Constructor.
   */
  public function __construct(
    string $root,
    string $id_prefix
  ) {
    $this->root = $root;
    $this->idPrefix = $id_prefix;
  }

  /**
   * {@inheritDoc}
   */
  public function getAdapter() {
    return OCFL::createInstance($this->root, $this->idPrefix);
  }

  /**
   * Helper; find Namaste token in the given directory.
   *
   * @see https://ocfl.io/1.0/spec/#root-conformance-declaration
   */
  protected function findToken() {
    $dvalues = [
      'ocfl_1.0',
      'ocfl_1.1',
    ];

    foreach ($dvalues as $dvalue) {
      $token = "0=$dvalue";
      $path = "{$this->root}/{$token}";
      if (file_exists($path) && file_get_contents($path) == "{$dvalue}\n") {
        return $dvalue;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function ensure($force = FALSE) : array {
    $issues = [];

    if (!is_dir($this->root)) {
      $issues[] = "The target root does not appear to be a directory: {$this->root}";
      return $issues;
    }

    if (!$this->findToken()) {
      $issues[] = "Failed to find OCFL storage Namaste token.";
    }

    return $issues;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    if (!($configuration['root'] ?? FALSE)) {
      throw new \InvalidArgumentException("Missing 'root' configuration.");
    }
    return new static(
      $configuration['root'],
      $configuration['id_prefix'] ?? ''
    );

  }

}
