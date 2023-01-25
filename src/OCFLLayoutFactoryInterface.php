<?php

namespace Drupal\flysystem_ocfl;

interface OCFLLayoutFactoryInterface {
  public function getLayout(string $root) : OCFLLayoutInterface;
}
