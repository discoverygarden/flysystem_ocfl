<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\FlatDirectStorageLayout;
use Drupal\Tests\UnitTestCase;

class FlatDirectStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider dataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) {
    $instance = new FlatDirectStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * @return array[]
   *   Data provider for testing.
   */
  public function dataProvider() {
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
