<?php

namespace Drupal\Tests\flysystem_ocfl\Kernel;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\flysystem_ocfl\OCFLLayoutInterface;
use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\FlatDirectStorageLayout;
use Drupal\flysystem_ocfl\Plugin\OCFL\Extensions\Layout\HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout;
use Drupal\Tests\token\Kernel\KernelTestBase;
use org\bovigo\vfs\vfsStream;

/**
 * Test base usage of the layout plugin manager.
 */
class OCFLLayoutManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'flysystem_ocfl',
    'flysystem',
  ];

  /**
   * Test that we return a PluginException when there is no `ocfl_layout.json`.
   */
  public function testMissingLayout() {
    $this->expectException(PluginException::class);
    /** @var \Drupal\flysystem_ocfl\OCFLLayoutFactoryInterface $manager */
    $manager = $this->container->get('plugin.manager.flysystem_ocfl_layout');

    // Write the Namaste tokens to the expected locations of the VFS.
    $root = vfsStream::setup('root', NULL, [
      '0=ocfl_1.0' => "ocfl_1.0\n",
    ]);

    // Target the config as the root.
    $manager->getLayout($root->url());
  }

  /**
   * Test loading and basic use of layout.
   *
   * @dataProvider dataProvider
   */
  public function testLayoutLoad(array $structure, string $class, string $id, string $path) {
    /** @var \Drupal\flysystem_ocfl\OCFLLayoutFactoryInterface $manager */
    $manager = $this->container->get('plugin.manager.flysystem_ocfl_layout');

    $root = vfsStream::setup('root', NULL, $structure);

    // Target the config as the root.
    $layout = $manager->getLayout($root->url());
    $this->assertInstanceOf(OCFLLayoutInterface::class, $layout, 'Class has the expected interface.');
    $this->assertInstanceOf($class, $layout, 'Loaded the correct layout.');

    // Ensure that the given ID gets maps to the given path.
    $this->assertEquals($path, $layout->mapToPath($id), 'ID mapped appropriately.');
  }

  /**
   * Data provider.
   *
   * @return array
   *   An array of arrays, each containing:
   *   - an array representing a vfs directory structure.
   *   - a string identifying a target class
   *   - a string representing an ID to map
   *   - a string representing the path to which the ID should map.
   */
  public function dataProvider() {
    return [
      [
        // Base test.
        [
          'ocfl_layout.json' => json_encode([
            'extension' => '0002-flat-direct-storage-layout',
          ]),
          '0=ocfl_1.0' => "ocfl_1.0\n",
          'silly-object-id' => [
            '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
          ],
        ],
        FlatDirectStorageLayout::class,
        'silly-object-id',
        'silly-object-id',
      ],
      [
        // Another base test, targeting another extension.
        [
          'ocfl_layout.json' => json_encode([
            'extension' => '0003-hash-and-id-n-tuple-storage-layout',
          ]),
          'extensions' => [
            '0003-hash-and-id-n-tuple-storage-layout' => [
              'config.json' => json_encode([
                'extensionName' => '0003-hash-and-id-n-tuple-storage-layout',
              ]),
            ],
          ],
          '0=ocfl_1.0' => "ocfl_1.0\n",
          '3c0/ff4/240/object-01' => [
            '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
            'inventory.json' => '{}',
          ],
        ],
        HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout::class,
        'object-01',
        '3c0/ff4/240/object-01',
      ],
      [
        // Targeting another extension, with non-default configuration.
        [
          'ocfl_layout.json' => json_encode([
            'extension' => '0003-hash-and-id-n-tuple-storage-layout',
          ]),
          'extensions' => [
            '0003-hash-and-id-n-tuple-storage-layout' => [
              'config.json' => json_encode([
                'extensionName' => '0003-hash-and-id-n-tuple-storage-layout',
                'digestAlgorithm' => 'md5',
                'tupleSize' => 2,
                'numberOfTuples' => 15,
              ]),
            ],
          ],
          '0=ocfl_1.0' => "ocfl_1.0\n",
          'ff/75/53/44/92/48/5e/ab/b3/9f/86/35/67/28/88/object-01' => [
            '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
            'inventory.json' => '{}',
          ],
        ],
        HashedTruncatedNTupleTreesWithObjectIDEncapsulatingDirectoryLayout::class,
        'object-01',
        'ff/75/53/44/92/48/5e/ab/b3/9f/86/35/67/28/88/object-01',
      ],
    ];
  }

}
