<?php

namespace Drupal\flysystem_ocfl\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLEvents;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FCRepoResourceLocator implements EventSubscriberInterface {

  const MANIFEST_NAME = '.fcrepo/fcr-root.json';

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() : array {
    return [
      OCFLEvents::RESOURCE_LOCATION => 'getResourceFromFcrepoManifest',
    ];
  }

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

  protected function getPathForHash(OCFLResourceLocationEvent $event, string $hash) : string {
    $inventory = $event->getInventory();
    assert(array_key_exists('manifest', $inventory));
    assert(array_key_exists($hash, $inventory['manifest']));
    $relative_paths = $inventory['manifest'][$hash];
    assert(count($relative_paths) === 1);
    $relative_path = reset($relative_paths);
    if ($relative_path === FALSE) {
      throw new \LogicException("Manifest contains hash referencing no names?");
    }
    $full_path = "{$event->getObjectRoot()}/{$relative_path}";
    assert(file_exists($full_path));
    return $full_path;
  }

  protected function getPathForName(OCFLResourceLocationEvent $event, string $name) {
    $hash = $this->getHashForName($event, $name);
    assert($hash !== FALSE);
    return $this->getPathForHash($event, $hash);
  }

  public function getResourceFromFcrepoManifest(OCFLResourceLocationEvent $event) {
    $fcrepo_manifest_hash = $this->getHashForName($event, static::MANIFEST_NAME);
    if ($fcrepo_manifest_hash === FALSE) {
      dsm("failed to find fcrepo manifest for {$event->getObjectRoot()}");
      return;
    }

    $fcrepo_manifest = json_decode(file_get_contents($this->getPathForHash($event, $fcrepo_manifest_hash)), TRUE);
    assert(array_key_exists('contentPath', $fcrepo_manifest));
    $event->setResourcePath($this->getPathForName($event, $fcrepo_manifest['contentPath']));
  }
}
