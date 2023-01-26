<?php

namespace Drupal\flysystem_ocfl\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OCFLInventoryLocationEvent extends Event {

  protected string $objectPath;
  protected array $inventory;

  /**
   * @param string $object_path
   */
  public function __construct(string $object_path) {
    $this->objectPath = $object_path;
  }

  public function getObjectPath() : string {
    return $this->objectPath;
  }

  public function setInventoryByPath(string $path) : void {
    $inventory_contents = file_get_contents($path);
    $this->setInventory(json_decode($inventory_contents, TRUE));
  }

  public function setInventory(array $inventory) : void {
    $this->inventory = $inventory;
    $this->stopPropagation();
  }

  public function getInventory() : array {
    return $this->inventory;
  }

}
