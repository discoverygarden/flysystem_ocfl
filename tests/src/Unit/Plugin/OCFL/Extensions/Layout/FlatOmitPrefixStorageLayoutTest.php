<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\FlatOmitPrefixStorageLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Test ID to path mapping for extension 0006.
 *
 * @see https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html
 *
 * @group flysystem_ocfl
 */
class FlatOmitPrefixStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   * @dataProvider exampleThreeDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) : void {
    $instance = new FlatOmitPrefixStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * Data provider for "example 1".
   *
   * @see https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html#example-1
   */
  public function exampleOneDataProvider() : array {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => ':',
    ];

    return [
      [$config, 'namespace:12887296', '12887296'],
      [
        $config,
        'urn:uuid:6e8bc430-9c3a-11d9-9669-0800200c9a66',
        '6e8bc430-9c3a-11d9-9669-0800200c9a66',
      ],
    ];
  }

  /**
   * Data provider for "example 2".
   *
   * @see https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html#example-2
   */
  public function exampleTwoDataProvider() : array {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => 'edu/',
    ];

    return [
      [$config, 'https://institution.edu/3448793', '3448793'],
      [$config, 'https://institution.edu/abc/edu/f8.05v', 'f8.05v'],
    ];
  }

  /**
   * Data provider for "example 3".
   *
   * @see https://ocfl.github.io/extensions/0006-flat-omit-prefix-storage-layout.html#example-3
   */
  public function exampleThreeDataProvider() : array {
    $config = [
      'extensionName' => '0006-flat-omit-prefix-storage-layout',
      'delimiter' => 'info:',
    ];

    return [
      [$config, 'info:fedora/object-01', 'fedora/object-01'],
      [
        $config,
        'https://example.org/info:/12345/x54xz321/s3/f8.05v',
        '/12345/x54xz321/s3/f8.05v',
      ],
    ];
  }

}
