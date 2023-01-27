<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MutableHeadInventoryLocator implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() : array {
    return [
      OCFLEvents::INVENTORY_LOCATION => ['setMutableHeadInventoryIfPresent', 100],
    ];
  }

  public function setMutableHeadInventoryIfPresent(OCFLInventoryLocationEvent $event) {
    $object_root = $event->getObjectPath();
    $mutable_root = "{$object_root}/extensions/0005-mutable-head";
    $mutable_inventory = "{$mutable_root}/head/inventory.json";
    if (!is_dir($mutable_root)) {
      // No extension directory, this is fine.
      return;
    }
    elseif (!is_file($mutable_inventory)) {
      throw new \LogicException("Mutable head extension dir {$mutable_root} exists; however, failed to find its inventory.");
    }
    $event->setInventoryByPath($mutable_inventory);
  }

}
