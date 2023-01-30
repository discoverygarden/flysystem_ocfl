<?php

namespace Drupal\flysystem_ocfl;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\flysystem_ocfl\Annotation\OCFLLayout;

/**
 * OCFL layout manager/factory.
 */
class OCFLLayoutManager extends DefaultPluginManager implements OCFLLayoutFactoryInterface {

  /**
   * Constructor.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      "Plugin/OCFL/Extensions/Layout",
      $namespaces,
      $module_handler,
      OCFLLayoutInterface::class,
      OCFLLayout::class
    );

    $this->alterInfo('flysystem_ocfl_layout_info');
    $this->setCacheBackend($cache_backend, 'flysystem_ocfl_layout_plugins');
  }

  /**
   * Attempt to load layout based on "ocfl_layout.json" file.
   *
   * @param string $root
   *   The path to the OCFL storage root directory.
   *
   * @return \Drupal\flysystem_ocfl\OCFLLayoutInterface|false
   *   The loaded interface plugin, or boolean FALSE.
   */
  protected function getFromLayoutConfig(string $root) {
    $layout = "{$root}/ocfl_layout.json";
    if (!file_exists($layout)) {
      return FALSE;
    }

    $layout_config = json_decode(file_get_contents($layout), TRUE);
    assert(array_key_exists('extension', $layout_config));
    $extension = $layout_config['extension'];
    $extension_config_path = "{$root}/extensions/{$extension}/config.json";
    assert(file_exists($extension_config_path));
    return $this->createInstance($extension, json_decode(file_get_contents($extension_config_path), TRUE));
  }

  /**
   * Do the heavy-lifting of building a layout.
   *
   * @param string $root
   *   The root for which to build a layout.
   *
   * @return \Drupal\flysystem_ocfl\OCFLLayoutInterface|false
   *   The built layout.
   */
  protected function doGetLayout(string $root) : OCFLLayoutInterface {
    if ($layout = $this->getFromLayoutConfig($root)) {
      return $layout;
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getLayout(string $root) : OCFLLayoutInterface {
    $cache_id = "flysystem_ocfl_layout:$root";
    if ($item = $this->cacheBackend->get($cache_id)) {
      return $item->data;
    }
    if ($layout = $this->getFromLayoutConfig($root)) {
      $this->cacheBackend->set($cache_id, $layout);
      return $layout;
    }

    throw new PluginException("Unable to identify storage layout scheme.");
  }

}
