<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BaseInventoryLocator implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [
      OCFLEvents::INVENTORY_LOCATION => 'loadObjectRootInventory',
      OCFLEvents::RESOURCE_LOCATION => ['resourceNotFound', -100],
    ];
  }

  public function loadObjectRootInventory(OCFLInventoryLocationEvent $event) {
    $event->setInventoryByPath("{$event->getObjectPath()}/inventory.json");
  }

  public function resourceNotFound(OCFLResourceLocationEvent $event) {
    throw new \LogicException("Unknown resource structure.");
  }

}
