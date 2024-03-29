<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Plugin\OCFL\Extensions\Layout;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Test ID to path mapping for extension 0003.
 *
 * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html
 *
 * @group flysystem_ocfl
 */
class HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   * @dataProvider exampleThreeDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) : void {
    $instance = new HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * Data provider for "example 1".
   *
   * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html#example-1
   */
  public function exampleOneDataProvider() : array {
    $config = [
      'extensionName' => '0003-hash-and-id-n-tuple-storage-layout',
      'digestAlgorithm' => 'sha256',
      'tupleSize' => 3,
      'numberOfTuples' => 3,
    ];

    return [
      [$config, 'object-01', '3c0/ff4/240/object-01'],
      [$config, '..hor/rib:le-$id', '487/326/d8c/%2e%2ehor%2frib%3ale-%24id'],
    ];
  }

  /**
   * Data provider for "example 2".
   *
   * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html#example-2
   */
  public function exampleTwoDataProvider() : array {
    $config = [
      'extensionName' => '0003-hash-and-id-n-tuple-storage-layout',
      'digestAlgorithm' => 'md5',
      'tupleSize' => 2,
      'numberOfTuples' => 15,
    ];

    return [
      [
        $config,
        'object-01',
        'ff/75/53/44/92/48/5e/ab/b3/9f/86/35/67/28/88/object-01',
      ],
      [
        $config,
        '..hor/rib:le-$id',
        '08/31/97/66/fb/6c/29/35/dd/17/5b/94/26/77/17/%2e%2ehor%2frib%3ale-%24id',
      ],
    ];
  }

  /**
   * Data provider for "example 3" and "encapsulation directory" examples.
   *
   * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html#example-3
   * @see https://ocfl.github.io/extensions/0003-hash-and-id-n-tuple-storage-layout.html#encapsulation-directory
   */
  public function exampleThreeDataProvider() : array {
    $config = [
      'extensionName' => '0003-hash-and-id-n-tuple-storage-layout',
      'digestAlgorithm' => 'sha256',
      'tupleSize' => 0,
      'numberOfTuples' => 0,
    ];

    return [
      [$config, 'object-01', 'object-01'],
      [$config, '..hor/rib:le-$id', '%2e%2ehor%2frib%3ale-%24id'],
      [
        $config,
        // XXX: The encoding of PHP source code seems to be a strange subject.
        mb_convert_encoding('..Hor/rib:lè-$id', 'UTF-8', 'ISO-8859-1'),
        '%2e%2eHor%2frib%3al%c3%a8-%24id',
      ],
      [
        $config,
        'abcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghija',
        'abcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghij-5cc73e648fbcff136510e330871180922ddacf193b68fdeff855683a01464220',
      ],
    ];
  }

}
