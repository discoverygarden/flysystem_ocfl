<?php

namespace Drupal\flysystem_ocfl\Flysystem\Adapter;

use Drupal\Core\Cache\CacheBackendInterface;
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

  use ReadOnlyTrait;

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
   * Cache mapped paths.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $cacheBackend;

  /**
   * Constructor.
   */
  public function __construct(string $root, OCFLLayoutInterface $layout, EventDispatcherInterface $dispatcher, CacheBackendInterface $cache, string $id_prefix = '') {
    $this->root = $root;
    $this->layout = $layout;
    $this->cacheBackend = $cache;
    $this->idPrefix = $id_prefix;
    $this->dispatcher = $dispatcher;
    parent::__construct($root);
  }

  /**
   * Static factory method.
   *
   * @param string $root
   *   The root of the OCFL storage.
   * @param string $id_prefix
   *   Prefix to prepend to incoming IDs.
   *
   * @return static
   *   The adapter instance.
   */
  public static function createInstance(string $root, string $id_prefix = '') {
    /** @var \Drupal\flysystem_ocfl\OCFLLayoutFactoryInterface $layout_factory */
    $layout_factory = \Drupal::service('plugin.manager.flysystem_ocfl_layout');
    return new static(
      $root,
      $layout_factory->getLayout($root),
      \Drupal::service('event_dispatcher'),
      \Drupal::service('cache.flysystem_ocfl_location'),
      $id_prefix
    );
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
   * Helper; find Namaste token in the given directory.
   *
   * @see https://ocfl.io/1.0/spec/#object-conformance-declaration
   */
  protected function findToken(string $object_path) {
    $dvalues = [
      'ocfl_object_1.0',
      'ocfl_object_1.1',
    ];

    foreach ($dvalues as $dvalue) {
      $token = "0={$dvalue}";
      $path = "{$object_path}/{$token}";
      if (file_exists($path) && file_get_contents($path) === "{$dvalue}\n") {
        return $dvalue;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function applyPathPrefix($path) {
    $cache_id = "flysystem_ocfl:mapped:{$this->root}:{$path}";
    if ($item = $this->cacheBackend->get($cache_id)) {
      assert(file_exists($item->data));
      return $item->data;
    }

    $object_id = "{$this->idPrefix}{$path}";
    $relative_object_path = $this->layout->mapToPath($object_id);

    $object_path = parent::applyPathPrefix($relative_object_path);
    if (!is_dir($object_path)) {
      // Does not appear to exist?
      throw new UnknownObjectException("Could not find object for ID {$object_id} at path {$object_path}.");
    }

    assert($this->findToken($object_path) !== FALSE, "Found object Namaste tag.");

    /** @var \Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent $inventory_event */
    $inventory_event = $this->dispatcher->dispatch(new OCFLInventoryLocationEvent($object_path), OCFLEvents::INVENTORY_LOCATION);

    /** @var \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $resource_event */
    $resource_event = $this->dispatcher->dispatch(new OCFLResourceLocationEvent($object_path, $inventory_event->getInventory()), OCFLEvents::RESOURCE_LOCATION);

    $resource_path = $resource_event->getResourcePath();
    $this->cacheBackend->set($cache_id, $resource_path);

    return $resource_path;
  }

  /**
   * {@inheritDoc}
   */
  public function getVisibility($path) {
    $result = parent::getVisibility($path);

    if (in_array($result['visibility'], ['public', 'private'])) {
      return $result;
    }
    else {
      return [
          'visibility' => 'private',
        ] + $result;
    }
  }

}
