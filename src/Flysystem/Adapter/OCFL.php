<?php

namespace Drupal\flysystem_ocfl\Flysystem\Adapter;

use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\EventSubscriber\OCFLResourceLocationEvent;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;
use League\Flysystem\Adapter\Local;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OCFL extends Local {

  protected string $root;
  protected OCFLLayoutInterface $layout;
  protected string $idPrefix;
  protected EventDispatcherInterface $dispatcher;

  public function __construct($root, OCFLLayoutInterface $layout, EventDispatcherInterface $dispatcher, $id_prefix = '') {
    $this->root = $root;
    $this->layout = $layout;
    $this->idPrefix = $id_prefix;
    $this->dispatcher = $dispatcher;
    parent::__construct($root);
  }

  public function applyPathPrefix($path) {
    $object_id = "{$this->idPrefix}{$path}";
    $relative_object_path = $this->layout->mapToPath($object_id);

    $object_path = parent::applyPathPrefix($relative_object_path);
    // TODO: Assert that we support whatever given version, in some way?
    assert(count(glob("{$object_path}/0=ocfl_object_?.?", GLOB_NOSORT)) === 1, "Found object Namaste tag.");

    // TODO: Acquire appropriate inventory and find the file representing the current item.
    /** @var \Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent $inventory_event */
    $inventory_event = $this->dispatcher->dispatch(new OCFLInventoryLocationEvent($object_path), OCFLEvents::INVENTORY_LOCATION);

    /** @var \Drupal\flysystem_ocfl\EventSubscriber\OCFLResourceLocationEvent $resource_event */
    $resource_event = $this->dispatcher->dispatch(new OCFLResourceLocationEvent($object_path, $inventory_event->getInventory()), OCFLEvents::RESOURCE_LOCATION);

    return $resource_event->getResourcePath();
  }

}
