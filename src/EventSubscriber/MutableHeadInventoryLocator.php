<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OCFL mutable head extension support.
 *
 * @see https://ocfl.github.io/extensions/0005-mutable-head.html
 */
class MutableHeadInventoryLocator implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      OCFLEvents::INVENTORY_LOCATION => [
        'setMutableHeadInventoryIfPresent',
        100,
      ],
    ];
  }

  /**
   * Event callback; use mutable head, if present.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent $event
   *   The location event to process.
   */
  public function setMutableHeadInventoryIfPresent(OCFLInventoryLocationEvent $event) : void {
    $object_root = $event->getObjectPath();
    $mutable_root = "{$object_root}/extensions/0005-mutable-head";
    $mutable_inventory = "{$mutable_root}/head/inventory.json";
    if (!is_dir($mutable_root)) {
      // No extension directory, your inventory is located elsewhere.
      return;
    }
    elseif (!is_file($mutable_inventory)) {
      throw new \LogicException("Mutable head extension dir {$mutable_root} exists; however, failed to find its inventory.");
    }
    $event->setInventoryByPath($mutable_inventory);
  }

}
