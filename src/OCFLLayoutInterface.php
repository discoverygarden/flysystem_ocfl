<?php

namespace Drupal\flysystem_ocfl;

interface OCFLLayoutInterface {
  public function mapToPath($id) : string;
}
