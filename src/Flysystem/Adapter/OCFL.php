<?php

namespace Drupal\flysystem_ocfl\Flysystem\Adapter;

use Drupal\flysystem_ocfl\OCFLLayoutInterface;
use League\Flysystem\Adapter\Local;

class OCFL extends Local {

  protected OCFLLayoutInterface $layout;
  protected string $idPrefix;

  public function __construct($root, OCFLLayoutInterface $layout, $id_prefix = '') {
    $this->layout = $layout;
    $this->idPrefix = $id_prefix;
    parent::__construct($root);
  }

  public function applyPathPrefix($path) {
    $dereferenced_path = $this->layout->mapToPath("{$this->idPrefix}{$path}");
    // TODO: Acquire appropriate inventory and find the file representing the current item.
    return parent::applyPathPrefix($dereferenced_path);
  }

}
