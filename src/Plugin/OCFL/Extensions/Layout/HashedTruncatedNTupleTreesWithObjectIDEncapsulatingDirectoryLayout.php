<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

/**
 * Map an ID according to extension 0003.
 *
 * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html
 *
 * @OCFLLayout(
 *   id = "0003-hash-and-id-n-tuple-storage-layout"
 * )
 */
class HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout extends AbstractHashedNTupleStorageLayout {

  /**
   * {@inheritDoc}
   */
  protected function getFinalPart(string $id, array $basis) : string {
    $replaced = mb_ereg_replace_callback('[^A-Za-z0-9-_]', [$this, 'encoder'], $id);
    return strlen($replaced) > 100 ?
      substr($replaced, 0, 100) . "-{$basis['basis']}" :
      $replaced;
  }

  /**
   * Replacement callback; Percent-encode according to UTF-8.
   *
   * @param array $matches
   *   The array of matches.
   *
   * @return string
   *   The string to replace.
   */
  protected function encoder(array $matches) : string {
    return static::encode($matches[0]);
  }

  /**
   * Heavy-lifting of percent-encoding UTF-8.
   *
   * @param string $input
   *   The input to encode.
   *
   * @return string
   *   The encoded input.
   */
  protected static function encode(string $input) {
    $ord = mb_ord($input, 'UTF-8');

    // Spec indicates it has to be lower-cased.
    $hex = strtolower(dechex($ord));
    if (strlen($hex) & 1) {
      $hex = "0{$hex}";
    }

    return '%' . implode('%', str_split($hex,2));
  }

}
