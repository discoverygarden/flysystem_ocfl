<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\NTupleOmitPrefixStorageLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Test ID to path mapping for extension 0007.
 *
 * @see https://ocfl.github.io/extensions/0007-n-tuple-omit-prefix-storage-layout.html
 *
 * @group flysystem_ocfl
 */
class NTupleOmitPrefixStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) : void {
    $instance = new NTupleOmitPrefixStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * Data provider for "example one".
   *
   * @see https://ocfl.github.io/extensions/0007-n-tuple-omit-prefix-storage-layout.html#example-1
   */
  public function exampleOneDataProvider() : array {
    $config = [
      'extensionName' => '0007-n-tuple-omit-prefix-storage-layout',
      'delimiter' => ':',
      'tupleSize' => 4,
      'numberOfTuples' => 2,
      'zeroPadding' => 'left',
      'reverseObjectRoot' => TRUE,
    ];

    return [
      [$config, 'namespace:12887296', '6927/8821/12887296'],
      [
        $config,
        'urn:uuid:6e8bc430-9c3a-11d9-9669-0800200c9a66',
        '66a9/c002/6e8bc430-9c3a-11d9-9669-0800200c9a66',
      ],
      [$config, 'abc123', '321c/ba00/abc123'],
    ];
  }

  /**
   * Data provider for "example two".
   *
   * @see https://ocfl.github.io/extensions/0007-n-tuple-omit-prefix-storage-layout.html#example-2
   */
  public function exampleTwoDataProvider() : array {
    $config = [
      'extensionName' => '0007-n-tuple-omit-prefix-storage-layout',
      'delimiter' => 'edu/',
      'tupleSize' => 3,
      'numberOfTuples' => 3,
      'zeroPadding' => 'right',
      'reverseObjectRoot' => FALSE,
    ];

    return [
      [$config, 'https://institution.edu/3448793', '344/879/300/3448793'],
      [$config, 'https://institution.edu/abc/edu/f8.05v', 'f8./05v/000/f8.05v'],
    ];
  }

}
