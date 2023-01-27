<?php

namespace Drupal\flysystem_ocfl\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OCFLResourceLocationEvent extends Event {

  protected string $objectRoot;
  protected array $inventory;
  protected string $resourcePath;

  public function __construct(
    string $object_root,
    array $inventory
  ) {
    $this->objectRoot = $object_root;
    $this->inventory = $inventory;
  }

  public function getObjectRoot() : string {
    return $this->objectRoot;
  }

  public function getInventory() : array {
    return $this->inventory;
  }

  public function setResourcePath(string $resourcePath) : void {
    $this->resourcePath = $resourcePath;
    $this->stopPropagation();
  }

  public function getResourcePath() : string {
    return $this->resourcePath;
  }
}
