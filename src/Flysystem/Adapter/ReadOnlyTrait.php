<?php

namespace Drupal\flysystem_ocfl\Flysystem\Adapter;

use League\Flysystem\Config;

/**
 * Prevent all write operations on an adapter.
 *
 * This concept seems to be missing in older versions of Flysystem. Flysystem 3
 * introduced a "Read-only Adapter Decorator"; however, we are (presently)
 * stuck with an older version of Flysystem.
 *
 * @see https://flysystem.thephpleague.com/docs/adapter/read-only/
 */
trait ReadOnlyTrait {

  /**
   * Write a new file.
   *
   * @see \League\Flysystem\AdapterInterface::write()
   */
  public function write($path, $contents, Config $config) {
    return FALSE;
  }

  /**
   * Write a new file using a stream.
   *
   * @see \League\Flysystem\AdapterInterface::writeStream()
   */
  public function writeStream($path, $resource, Config $config) {
    return FALSE;
  }

  /**
   * Update a file.
   *
   * @see \League\Flysystem\AdapterInterface::update()
   */
  public function update($path, $contents, Config $config) {
    return FALSE;
  }

  /**
   * Update a file using a stream.
   *
   * @see \League\Flysystem\AdapterInterface::updateStream()
   */
  public function updateStream($path, $resource, Config $config) {
    return FALSE;
  }

  /**
   * Rename a file.
   *
   * @see \League\Flysystem\AdapterInterface::rename()
   */
  public function rename($path, $newpath) {
    return FALSE;
  }

  /**
   * Copy a file.
   *
   * @see \League\Flysystem\AdapterInterface::copy()
   */
  public function copy($path, $newpath) {
    return FALSE;
  }

  /**
   * Delete a file.
   *
   * @see \League\Flysystem\AdapterInterface::delete()
   */
  public function delete($path) {
    return FALSE;
  }

  /**
   * Delete a directory.
   *
   * @see \League\Flysystem\AdapterInterface::deleteDir()
   */
  public function deleteDir($dirname) {
    return FALSE;
  }

  /**
   * Create a directory.
   *
   * @see \League\Flysystem\AdapterInterface::createDir()
   */
  public function createDir($dirname, Config $config) {
    return FALSE;
  }

  /**
   * Set the visibility for a file.
   *
   * @see \League\Flysystem\AdapterInterface::setVisibility()
   */
  public function setVisibility($path, $visibility) {
    return FALSE;
  }

}
