<?php

namespace Drupal\Tests\flysystem_ocfl\Kernel\Flysystem;

use Drupal\Core\DependencyInjection\Compiler\RegisterStreamWrappersPass;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\flysystem\FlysystemServiceProvider;
use Drupal\flysystem_ocfl\Flysystem\Adapter\UnknownObjectException;
use Drupal\flysystem_ocfl\Flysystem\OCFLAdapterPlugin;
use Drupal\Tests\token\Kernel\KernelTestBase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class OCFLAdapterPluginTest extends KernelTestBase {

  protected static $modules = [
    'flysystem',
    'flysystem_ocfl',
  ];

  protected vfsStreamDirectory $_vfsRoot;
  protected string $scheme;

  public function setUp() : void {
    parent::setUp();

    vfsStream::enableDotfiles();
    $this->_vfsRoot = vfsStream::setup(
      'root',
      NULL, //FileSystem::CHMOD_FILE,
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
          'root' => $this->_vfsRoot->url(),
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

  public function testMissingRoot() {
    $this->expectException(\InvalidArgumentException::class);
    OCFLAdapterPlugin::create($this->container, [], '', []);
  }

  public function testUnknownResourceResolution() {
    $this->_vfsRoot = vfsStream::create([
      'silly-object-id' => [
        '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
        'inventory.json' => json_encode([
          'head' => 'v1',
          'versions' => [
            'v1' => [
              'state' => [],
            ],
          ]
        ]),
      ],
    ], $this->_vfsRoot);

    // XXX: Does not seem to match just one the "message", so... gonna leave
    // this like this, I guess?
    $this->expectWarning();
    $this->expectWarningMessage('Unknown resource structure.');
    $this->expectWarningMessageMatches('/^Unknown resource structure\\./');
    file_exists("{$this->scheme}://silly-object-id");
  }

  public function testKnownResourceResolution() {
    $test_value = $this->randomMachineName();
    $this->_vfsRoot = vfsStream::create([
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
              ]
            ]
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
    ], $this->_vfsRoot);

    $this->assertTrue(file_exists("{$this->scheme}://object-02"), 'FCRepo resource resolution seems to work.');
    $this->assertEquals($test_value, file_get_contents("{$this->scheme}://object-02"), 'FCRepo resource resolution found the value.');
  }

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
              ]
            ]
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
    ], $this->_vfsRoot);

    $this->assertTrue(file_exists("{$this->scheme}://object-02"), 'FCRepo resource resolution seems to work from mutable head.');
    $this->assertEquals($test_value2, file_get_contents("{$this->scheme}://object-02"), 'FCRepo resource resolution found the value from mutable head.');
  }

}
