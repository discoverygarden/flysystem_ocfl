<?php

namespace Drupal\flysystem_ocfl\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event to facilitate inventory location.
 */
class OCFLInventoryLocationEvent extends Event {

  /**
   * The path to the object of which to locate the inventory.
   *
   * @var string
   */
  protected string $objectPath;

  /**
   * The located inventory, when set.
   *
   * @var array
   */
  protected array $inventory;

  /**
   * Constructor.
   */
  public function __construct(string $object_path) {
    $this->objectPath = $object_path;
  }

  /**
   * Accessor; get the path to the object of which to locate the inventory.
   *
   * @return string
   *   The path to the object.
   */
  public function getObjectPath() : string {
    return $this->objectPath;
  }

  /**
   * Setter helper; load a JSON file containing the inventory and set it.
   *
   * NOTE: Stops event propagation (via ::setInventory()).
   *
   * @param string $path
   *   The path to a JSON file containing the inventory.
   */
  public function setInventoryByPath(string $path) : void {
    assert(file_exists($path), 'The referenced inventory file exists.');
    $inventory_contents = file_get_contents($path);
    $parsed = json_decode($inventory_contents, TRUE);
    assert(is_array($parsed), 'The parsed manifest is an array.');
    $this->setInventory($parsed);
  }

  /**
   * Setter; set the inventory and stop further propagation of the event.
   *
   * NOTE: Stops event propagation.
   *
   * @param array $inventory
   *   The inventory to set.
   */
  public function setInventory(array $inventory) : void {
    $this->inventory = $inventory;
    $this->stopPropagation();
  }

  /**
   * Accessor; get the set inventory.
   *
   * @return array
   *   The inventory.
   */
  public function getInventory() : array {
    if (!isset($this->inventory)) {
      throw new \LogicException('Attempted to get inventory without it having been set.');
    }
    return $this->inventory;
  }

}
