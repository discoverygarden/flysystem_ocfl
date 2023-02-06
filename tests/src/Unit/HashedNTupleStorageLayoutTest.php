<?php

namespace Drupal\Tests\flysystem_ocfl\Unit;

use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\HashedNTupleStorageLayout;
use Drupal\Tests\UnitTestCase;

class HashedNTupleStorageLayoutTest extends UnitTestCase {

  /**
   * Test the mapping.
   *
   * @dataProvider exampleOneDataProvider
   * @dataProvider exampleTwoDataProvider
   * @dataProvider exampleThreeDataProvider
   */
  public function testMapToPath(array $config, string $id, string $expected) {
    $instance = new HashedNTupleStorageLayout($config, '', []);
    $this->assertEquals($expected, $instance->mapToPath($id));
  }

  /**
   * @return array[]
   *   Data provider for testing.
   */
  public function exampleOneDataProvider() {
    $config = [
      'extensionName' => '0004-hashed-n-tuple-storage-layout',
      'digestAlgorithm' => 'sha256',
      'tupleSize' =>  3,
      'numberOfTuples' => 3,
      'shortObjectRoot' => FALSE,
    ];

    return [
      [$config, 'object-01', '3c0/ff4/240/3c0ff4240c1e116dba14c7627f2319b58aa3d77606d0d90dfc6161608ac987d4'],
      [$config, '..hor/rib:le-$id', '487/326/d8c/487326d8c2a3c0b885e23da1469b4d6671fd4e76978924b4443e9e3c316cda6d'],
    ];
  }

  public function exampleTwoDataProvider() {
    $config = [
      'extensionName' => '0004-hashed-n-tuple-storage-layout',
      'digestAlgorithm' => 'md5',
      'tupleSize' =>  2,
      'numberOfTuples' => 15,
      'shortObjectRoot' => TRUE,
    ];

    return [
      [$config, 'object-01', 'ff/75/53/44/92/48/5e/ab/b3/9f/86/35/67/28/88/4e'],
      [$config, '..hor/rib:le-$id', '08/31/97/66/fb/6c/29/35/dd/17/5b/94/26/77/17/e0'],
    ];
  }

  public function exampleThreeDataProvider() {
    $config = [
      'extensionName' => '0004-hashed-n-tuple-storage-layout',
      'digestAlgorithm' => 'sha256',
      'tupleSize' =>  0,
      'numberOfTuples' => 0,
      'shortObjectRoot' => FALSE,
    ];

    return [
      [$config, 'object-01', '3c0ff4240c1e116dba14c7627f2319b58aa3d77606d0d90dfc6161608ac987d4'],
      [$config, '..hor/rib:le-$id', '487326d8c2a3c0b885e23da1469b4d6671fd4e76978924b4443e9e3c316cda6d'],
    ];
  }

}
