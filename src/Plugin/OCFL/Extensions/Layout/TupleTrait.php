<?php

namespace Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout;

/**
 * Helper trait for mapping to paths joined tuple structures.
 */
trait TupleTrait {

  use ConfigurableTrait;

  /**
   * Get the basis for tuple generation.
   *
   * @param string $id
   *   The ID for which to derive a basis.
   *
   * @return array
   *   An associative array containing:
   *   - basis: the basis for mapping; and,
   *   - optionally, other values to pass along (to the ::getFinalPart() call).
   */
  abstract protected function getBasis(string $id) : array;

  /**
   * Map tuple-based items to paths.
   *
   * @param string $id
   *   The ID to map.
   *
   * @return string
   *   The mapped path.
   */
  public function mapToPath(string $id) : string {
    $basis = $this->getBasis($id);

    $parts = [];
    for ($i = 0; $i < $this->configuration['numberOfTuples']; $i++) {
      $parts[] = substr($basis['basis'], $i * $this->configuration['tupleSize'], $this->configuration['tupleSize']);
    }
    $parts[] = $this->getFinalPart($id, $basis);

    return implode('/', $parts);
  }

  /**
   * Get the final path component.
   *
   * @param string $id
   *   The ID for which we are deriving the final component, if it is used.
   * @param array $basis
   *   The basis (and other context) for generation.
   *
   * @return string
   *   The final path component.
   */
  abstract protected function getFinalPart(string $id, array $basis) : string;

  /**
   * Some "default" defaults.
   *
   * @return array
   *   Tuple things seem to be based on the same values.
   */
  public function defaultPluginConfiguration() : array {
    return [
      'tupleSize' => 3,
      'numberOfTuples' => 3,
    ];
  }

}
