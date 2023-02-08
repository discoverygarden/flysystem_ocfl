<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\FlatOmitPrefixStorageLayout;
use Drupal\Tests\UnitTestCase;

class FlatOmitPrefixStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   * @dataProvider exampleThreeDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) {
    $instance = new FlatOmitPrefixStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * @return array[]
   *   Data provider for testing.
   */
  public function exampleOneDataProvider() {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => ':',
    ];

    return [
      [$config, 'namespace:12887296', '12887296'],
      [$config, 'urn:uuid:6e8bc430-9c3a-11d9-9669-0800200c9a66', '6e8bc430-9c3a-11d9-9669-0800200c9a66'],
    ];
  }

  public function exampleTwoDataProvider() {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => 'edu/',
    ];

    return [
      [$config, 'https://institution.edu/3448793', '3448793'],
      [$config, 'https://institution.edu/abc/edu/f8.05v', 'f8.05v'],
    ];
  }

  public function exampleThreeDataProvider() {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => 'info:',
    ];

    return [
      [$config, 'info:fedora/object-01', 'fedora/object-01'],
      [$config, 'https://example.org/info:/12345/x54xz321/s3/f8.05v', '/12345/x54xz321/s3/f8.05v'],
    ];
  }

}
