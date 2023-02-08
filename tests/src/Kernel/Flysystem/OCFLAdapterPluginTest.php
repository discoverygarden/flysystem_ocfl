<?php

namespace Drupal\Tests\flysystem_ocfl\Kernel\Flysystem;

use Drupal\Core\File\FileSystem;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\flysystem_ocfl\Flysystem\OCFLAdapterPlugin;
use Drupal\Tests\token\Kernel\KernelTestBase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Test out adapter plugin instantiation.
 */
class OCFLAdapterPluginTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'flysystem',
    'flysystem_ocfl',
  ];

  /**
   * Generated OCFL root VFS directory structure.
   *
   * @var \org\bovigo\vfs\vfsStreamDirectory
   */
  protected vfsStreamDirectory $ocflRoot;

  /**
   * Flysystem/Stream-wrapper scheme as which to register.
   *
   * @var string
   */
  protected string $scheme;

  /**
   * {@inheritDoc}
   */
  public function setUp() : void {
    parent::setUp();

    $this->ocflRoot = vfsStream::setup(
      'root',
      FileSystem::CHMOD_FILE,
      [
        'ocfl_layout.json' => json_encode([
          'extension' => '0002-flat-direct-storage-layout',
        ]),
        '0=ocfl_1.0' => "ocfl_1.0\n",
      ]
    );
    $this->scheme = strtolower($this->randomMachineName());
    $this->setSetting('flysystem', [
      $this->scheme => [
        'driver' => 'ocfl',
        'config' => [
          'root' => $this->ocflRoot->url(),
          'id_prefix' => '',
        ],
      ],
    ]);

    /** @var \Drupal\Core\DrupalKernelInterface $kernel */
    $kernel = $this->container->get('kernel');
    $this->container = $kernel->rebuildContainer();

    /** @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager */
    $stream_wrapper_manager = $this->container->get('stream_wrapper_manager');
    $wrapper = $stream_wrapper_manager->getViaScheme($this->scheme);
    $stream_wrapper_manager->registerWrapper($this->scheme, get_class($wrapper), StreamWrapperInterface::READ);
  }

  /**
   * Test attempt to instantiate without a "root" specified.
   */
  public function testMissingRoot() {
    $this->expectException(\InvalidArgumentException::class);
    OCFLAdapterPlugin::create($this->container, [], '', []);
  }

  /**
   * Test error state.
   */
  public function testUnknownResourceResolution() {
    $this->ocflRoot = vfsStream::create([
      'silly-object-id' => [
        '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
        'inventory.json' => json_encode([
          'head' => 'v1',
          'versions' => [
            'v1' => [
              'state' => [],
            ],
          ],
        ]),
      ],
    ], $this->ocflRoot);

    // XXX: Does not seem to match just one the "message", so... gonna leave
    // this like this, I guess?
    $this->expectWarning();
    $this->expectWarningMessage('Unknown resource structure.');
    $this->expectWarningMessageMatches('/^Unknown resource structure\\./');
    file_exists("{$this->scheme}://silly-object-id");
  }

  /**
   * Test resolution from base inventory.
   *
   * @see https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure
   */
  public function testKnownResourceResolution() {
    $test_value = $this->randomMachineName();
    $this->ocflRoot = vfsStream::create([
      'object-02' => [
        '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
        'inventory.json' => json_encode([
          'id' => 'object-02',
          'head' => 'v1',
          'manifest' => [
            'fake-hash' => [
              'v1/content/.fcrepo/fcr-root.json',
            ],
            'yahash' => [
              'v1/content/our_file',
            ],
          ],
          'versions' => [
            'v1' => [
              'state' => [
                'fake-hash' => [
                  '.fcrepo/fcr-root.json',
                ],
                'yahash' => [
                  'our_file',
                ],
              ],
            ],
          ],
        ]),
        'v1' => [
          'content' => [
            '.fcrepo' => [
              'fcr-root.json' => json_encode([
                'interactionModel' => 'http://www.w3.org/ns/ldp#NonRDFSource',
                'contentPath' => 'our_file',
              ]),
            ],
            'our_file' => $test_value,
          ],
        ],
      ],
    ], $this->ocflRoot);

    $this->assertTrue(file_exists("{$this->scheme}://object-02"), 'FCRepo resource resolution seems to work.');
    $this->assertEquals($test_value, file_get_contents("{$this->scheme}://object-02"), 'FCRepo resource resolution found the value.');
  }

  /**
   * Test resource dereference via mutable head.
   *
   * @see https://ocfl.github.io/extensions/0005-mutable-head.html
   * @see https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure
   */
  public function testKnownResourceViaMutableHeadResolution() {
    $test_value = $this->randomMachineName();
    $test_value2 = $this->randomMachineName();
    vfsStream::create([
      'object-02' => [
        '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
        'inventory.json' => json_encode([
          'id' => 'object-02',
          'head' => 'v1',
          'manifest' => [
            'fake-hash' => [
              'v1/content/.fcrepo/fcr-root.json',
            ],
            'yahash' => [
              'v1/content/our_file',
            ],
          ],
          'versions' => [
            'v1' => [
              'state' => [
                'fake-hash' => [
                  '.fcrepo/fcr-root.json',
                ],
                'yahash' => [
                  'our_file',
                ],
              ],
            ],
          ],
        ]),
        'v1' => [
          'content' => [
            '.fcrepo' => [
              'fcr-root.json' => json_encode([
                'interactionModel' => 'http://www.w3.org/ns/ldp#NonRDFSource',
                'contentPath' => 'our_file',
              ]),
            ],
            'our_file' => $test_value,
          ],
        ],
        'extensions' => [
          '0005-mutable-head' => [
            'head' => [
              'inventory.json' => json_encode([
                'id' => 'object-02',
                'head' => 'v2',
                'manifest' => [
                  'fake-hash' => [
                    'v1/content/.fcrepo/fcr-root.json',
                  ],
                  'yahash' => [
                    'v1/content/our_file',
                  ],
                  'some-hash' => [
                    'extensions/0005-mutable-head/head/content/r1/mutable-head-silliness',
                  ],
                  'another-hash' => [
                    'extensions/0005-mutable-head/head/content/r1/.fcrepo/fcr-root.json',
                  ],
                ],
                'versions' => [
                  'v1' => [
                    'state' => [
                      'fake-hash' => [
                        '.fcrepo/fcr-root.json',
                      ],
                      'yahash' => [
                        'our_file',
                      ],
                    ],
                  ],
                  'v2' => [
                    'state' => [
                      'another-hash' => [
                        '.fcrepo/fcr-root.json',
                      ],
                      'yahash' => [
                        'our_file',
                      ],
                      'some-hash' => [
                        'mutable-head-silliness',
                      ],
                    ],
                  ],
                ],
              ]),
              'content' => [
                'r1' => [
                  'mutable-head-silliness' => $test_value2,
                  '.fcrepo' => [
                    'fcr-root.json' => json_encode([
                      'interactionModel' => 'http://www.w3.org/ns/ldp#NonRDFSource',
                      'contentPath' => 'mutable-head-silliness',
                    ]),
                  ],
                ],
              ],
            ],
            'revisions' => [
              'r1' => 'r1',
            ],
          ],
        ],
      ],
    ], $this->ocflRoot);

    $this->assertTrue(file_exists("{$this->scheme}://object-02"), 'FCRepo resource resolution seems to work from mutable head.');
    $this->assertEquals($test_value2, file_get_contents("{$this->scheme}://object-02"), 'FCRepo resource resolution found the value from mutable head.');
  }

}
