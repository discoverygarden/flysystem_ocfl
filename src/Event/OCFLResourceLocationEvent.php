<?php

namespace Drupal\flysystem_ocfl\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event to facilitate resource location.
 */
class OCFLResourceLocationEvent extends Event {

  /**
   * The object's root path.
   *
   * @var string
   */
  protected string $objectRoot;

  /**
   * The inventory within which to locate the resource.
   *
   * @var array
   */
  protected array $inventory;

  /**
   * The path to the resource, when set.
   *
   * @var string
   */
  protected string $resourcePath;

  /**
   * Constructor.
   */
  public function __construct(
    string $object_root,
    array $inventory
  ) {
    $this->objectRoot = $object_root;
    $this->inventory = $inventory;
  }

  /**
   * Accessor; get the object's root path.
   *
   * @return string
   *   The object's root path.
   */
  public function getObjectRoot() : string {
    return $this->objectRoot;
  }

  /**
   * Accessor; get the inventory within which to locate the resource.
   *
   * @return array
   *   The inventory within which to locate the resource.
   */
  public function getInventory() : array {
    return $this->inventory;
  }

  /**
   * Setter; set the resource path.
   *
   * NOTE: Stops event propagation.
   *
   * @param string $resourcePath
   *   The path to the resource to set.
   */
  public function setResourcePath(string $resourcePath) : void {
    $this->resourcePath = $resourcePath;
    $this->stopPropagation();
  }

  /**
   * Accessor; get the set resource path.
   *
   * @return string
   *   The set resource path.
   */
  public function getResourcePath() : string {
    return $this->resourcePath;
  }

}
