<?php

namespace Drupal\Tests\flysystem_ocfl\Unit;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\NTupleOmitPrefixStorageLayout;
use Drupal\Tests\UnitTestCase;

class NTupleOmitPrefixStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) {
    $instance = new NTupleOmitPrefixStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * @return array[]
   *   Data provider for testing.
   */
  public function exampleOneDataProvider() {
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
      [$config, 'urn:uuid:6e8bc430-9c3a-11d9-9669-0800200c9a66', '66a9/c002/6e8bc430-9c3a-11d9-9669-0800200c9a66'],
      [$config, 'abc123', '321c/ba00/abc123'],
    ];
  }

  public function exampleTwoDataProvider() {
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
