<?php

namespace Drupal\flysystem_ocfl;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\flysystem_ocfl\Annotation\OCFLLayout;

class OCFLLayoutManager extends DefaultPluginManager implements OCFLLayoutFactoryInterface {

  public function __construct(
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      "Plugin/OCFL/Extensions/Layout",
      $namespaces,
      $module_handler,
      OCFLLayoutInterface::class,
      OCFLLayout::class
    );
  }

  public function getLayout(string $root) : OCFLLayoutInterface {
    // TODO: Implement getLayout() method.
  }

}
