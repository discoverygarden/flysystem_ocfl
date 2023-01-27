<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Base inventory and resource locator implementation.
 */
class BaseLocator implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      OCFLEvents::INVENTORY_LOCATION => 'loadObjectRootInventory',
      OCFLEvents::RESOURCE_LOCATION => ['resourceNotFound', -100],
    ];
  }

  /**
   * Event callback; load "inventory.json" located directly in the object.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent $event
   *   The event to be processed.
   */
  public function loadObjectRootInventory(OCFLInventoryLocationEvent $event) : void {
    $event->setInventoryByPath("{$event->getObjectPath()}/inventory.json");
  }

  /**
   * Event callback; base "main" resource is not defined.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $event
   *   The event to be processed.
   */
  public function resourceNotFound(OCFLResourceLocationEvent $event) : void {
    throw new \LogicException("Unknown resource structure.");
  }

}
