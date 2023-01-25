<?php

namespace Drupal\flysystem_ocfl\Flysystem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use Drupal\flysystem_ocfl\Flysystem\Adapter\OCFL;
use Drupal\flysystem_ocfl\OCFLLayoutFactoryInterface;
use http\Exception\InvalidArgumentException;
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
   * @var \Drupal\flysystem_ocfl\OCFLLayoutFactoryInterface
   */
  protected OCFLLayoutFactoryInterface $layoutFactory;

  /**
   * Constructor.
   */
  public function __construct(
    OCFLLayoutFactoryInterface $layoutFactory,
    string $root,
    string $id_prefix
  ) {
    $this->layoutFactory = $layoutFactory;
    $this->root = $root;
    $this->idPrefix = $id_prefix;
  }

  /**
   * {@inheritDoc}
   */
  public function getAdapter() {
    return new OCFL(
      $this->root,
      $this->layoutFactory->getLayout($this->root),
      $this->idPrefix
    );
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

    $namaste_tokens = glob("{$this->root}/0=ocfl_*", GLOB_NOSORT);
    $token_count = count($namaste_tokens);
    if ($token_count === 0) {
      $issues[] = "Failed to find namaste token for the OCFL root.";
    }
    elseif ($token_count > 1) {
      $issues[] = "Found too many namaste tokens for the OCFL root.";
    }

    return $issues;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    if (!($configuration['root'] ?? FALSE)) {
      throw new InvalidArgumentException("Missing 'root' configuration.");
    }
    return new static(
      $container->get('flysystem_ocfl.layout_factory'),
      $configuration['root'],
      $configuration['id_prefix']
    );
  }

}
