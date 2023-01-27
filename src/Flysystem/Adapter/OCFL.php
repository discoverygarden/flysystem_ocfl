<?php

namespace Drupal\flysystem_ocfl\Flysystem\Adapter;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;
use League\Flysystem\Adapter\Local;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * OCFL flysystem adapter.
 */
class OCFL extends Local {

  /**
   * The root storage path.
   *
   * @var string
   */
  protected string $root;

  /**
   * The storage layout implementation.
   *
   * @var \Drupal\flysystem_ocfl\OCFLLayoutInterface
   */
  protected OCFLLayoutInterface $layout;

  /**
   * Prefix to append to IDs.
   *
   * @var string
   */
  protected string $idPrefix;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $dispatcher;

  /**
   * Constructor.
   */
  public function __construct($root, OCFLLayoutInterface $layout, EventDispatcherInterface $dispatcher, $id_prefix = '') {
    $this->root = $root;
    $this->layout = $layout;
    $this->idPrefix = $id_prefix;
    $this->dispatcher = $dispatcher;
    parent::__construct($root);
  }

  /**
   * {@inheritDoc}
   */
  public function has($path) {
    try {
      $location = $this->applyPathPrefix($path);
      return file_exists($location);
    }
    catch (UnknownObjectException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function applyPathPrefix($path) {
    $object_id = "{$this->idPrefix}{$path}";
    $relative_object_path = $this->layout->mapToPath($object_id);

    $object_path = parent::applyPathPrefix($relative_object_path);
    if (!is_dir($object_path)) {
      // Does not appear to exist?
      throw new UnknownObjectException("Could not find object for ID {$object_id} at path {$object_path}.");
    }
    // @todo Assert that we support whatever given version, in some way?
    assert(count(glob("{$object_path}/0=ocfl_object_?.?", GLOB_NOSORT)) === 1, "Found object Namaste tag.");

    /** @var \Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent $inventory_event */
    $inventory_event = $this->dispatcher->dispatch(new OCFLInventoryLocationEvent($object_path), OCFLEvents::INVENTORY_LOCATION);

    /** @var \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $resource_event */
    $resource_event = $this->dispatcher->dispatch(new OCFLResourceLocationEvent($object_path, $inventory_event->getInventory()), OCFLEvents::RESOURCE_LOCATION);

    return $resource_event->getResourcePath();
  }

}
