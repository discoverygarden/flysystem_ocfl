<?php

namespace Drupal\flysystem_ocfl;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\flysystem_ocfl\Annotation\OCFLLayout;

class OCFLLayoutManager extends DefaultPluginManager implements OCFLLayoutFactoryInterface {

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

  public function getLayout(string $root) : OCFLLayoutInterface {
    if ($layout = $this->getFromLayoutConfig($root)) {
      return $layout;
    }

    throw new PluginException("Unable to identify storage layout scheme.");
  }

}
