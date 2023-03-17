<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Locate "root" resources from fcrepo.
 */
class FCRepoResourceLocator implements EventSubscriberInterface {

  const MANIFEST_NAME = '.fcrepo/fcr-root.json';

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      OCFLEvents::RESOURCE_LOCATION => 'getResourceFromFcrepoManifest',
    ];
  }

  /**
   * Helper; given a file name/relative path, get the hash of the resource.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $event
   *   The event being processed.
   * @param string $name
   *   The name for which to lookup the hash.
   *
   * @return string|false
   *   The hash if found; otherwise, boolean FALSE.
   */
  protected function getHashForName(OCFLResourceLocationEvent $event, string $name) {
    $inventory = $event->getInventory();

    assert(array_key_exists('head', $inventory));
    $head = $inventory['head'];
    assert(array_key_exists($head, $inventory['versions']));
    $version = $inventory['versions'][$head];
    foreach ($version['state'] as $hash => $names) {
      if (in_array($name, $names)) {
        return $hash;
      }
    }
    return FALSE;
  }

  /**
   * Helper; given a file hash, find the path.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $event
   *   The event to be processed.
   * @param string $hash
   *   The hash of the resource to lookup from the manifest in the inventory.
   *
   * @return string
   *   The looked-up path.
   */
  protected function getPathForHash(OCFLResourceLocationEvent $event, string $hash) : string {
    $inventory = $event->getInventory();
    assert(array_key_exists('manifest', $inventory), 'Inventory contains manifest.');
    assert(array_key_exists($hash, $inventory['manifest']), 'Manifest contains hash.');
    $relative_paths = $inventory['manifest'][$hash];
    assert(count($relative_paths) === 1, 'Only one path found.');
    $relative_path = reset($relative_paths);
    if ($relative_path === FALSE) {
      throw new \LogicException("Manifest contains hash referencing no names?");
    }
    $full_path = "{$event->getObjectRoot()}/{$relative_path}";
    assert(file_exists($full_path), "Resolved file ({$full_path}) exists.");
    return $full_path;
  }

  /**
   * Helper; given a resource name, lookup its full path.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $event
   *   The event baing processed.
   * @param string $name
   *   The name of the resource to lookup.
   *
   * @return string
   *   The path to the resource.
   */
  protected function getPathForName(OCFLResourceLocationEvent $event, string $name) : string {
    $hash = $this->getHashForName($event, $name);
    assert($hash !== FALSE);
    return $this->getPathForHash($event, $hash);
  }

  /**
   * Event callback; locate resource given an "fcr-root.json" being present.
   *
   * @param \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent $event
   *   The event being processed.
   */
  public function getResourceFromFcrepoManifest(OCFLResourceLocationEvent $event) : void {
    $fcrepo_manifest_hash = $this->getHashForName($event, static::MANIFEST_NAME);
    if ($fcrepo_manifest_hash === FALSE) {
      return;
    }

    $fcrepo_manifest = json_decode(file_get_contents($this->getPathForHash($event, $fcrepo_manifest_hash)), TRUE);
    assert(array_key_exists('interactionModel', $fcrepo_manifest));
    assert($fcrepo_manifest['interactionModel'] === 'http://www.w3.org/ns/ldp#NonRDFSource');
    if (!array_key_exists('contentPath', $fcrepo_manifest)) {
      return;
    }
    $event->setResourcePath($this->getPathForName($event, $fcrepo_manifest['contentPath']));
  }

}
