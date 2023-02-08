<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\FlatDirectStorageLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Test ID to path mapping for extension 0002.
 *
 * @see https://ocfl.github.io/extensions/0002-flat-direct-storage-layout.html
 *
 * @group flysystem_ocfl
 */
class FlatDirectStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider dataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) : void {
    $instance = new FlatDirectStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * Data provider for "example 1".
   *
   * In addition, random string passing through.
   *
   * @see https://ocfl.github.io/extensions/0002-flat-direct-storage-layout.html#example-1
   */
  public function dataProvider() : array {
    $config = [
      'extensionName' => '0002-flat-direct-storage-layout',
    ];
    $other = $this->randomMachineName();
    return [
      [$config, 'object-01', 'object-01'],
      [$config, '..hor_rib:lé-$id', '..hor_rib:lé-$id'],
      [$config, $other, $other],
    ];
  }

}
