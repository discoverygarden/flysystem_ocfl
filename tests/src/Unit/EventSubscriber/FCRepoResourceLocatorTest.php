<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Drupal\flysystem_ocfl\EventSubscriber\FCRepoResourceLocator;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Locate "main" binary resource, as per FCRepo.
 *
 * @see https://wiki.lyrasis.org/display/FEDORA6x/Fedora+OCFL+Object+Structure#FedoraOCFLObjectStructure-FedoraAtomicResource-Binary
 */
class FCRepoResourceLocatorTest extends UnitTestCase {

  /**
   * Instance of the class under test.
   *
   * @var \Drupal\flysystem_ocfl\EventSubscriber\FCRepoResourceLocator
   */
  protected FCRepoResourceLocator $locator;

  /**
   * String representing a filename to probe.
   *
   * @var string
   */
  protected string $mockFile;

  /**
   * String representing the contents of the file being probed.
   *
   * @var string
   */
  protected string $mockContents;

  /**
   * Structure of the object root for merging in tests, if relevant.
   *
   * @var array
   */
  protected array $objectRootStructure;

  /**
   * OCFL object root for testing.
   *
   * @var \org\bovigo\vfs\vfsStreamDirectory
   */
  protected vfsStreamDirectory $objectRoot;

  /**
   * Mock event object to assert interactions.
   *
   * @var \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent|\PHPUnit\Framework\MockObject\MockObject
   */
  protected OCFLResourceLocationEvent $mockEvent;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->locator = new FCRepoResourceLocator();

    $this->mockFile = $this->randomMachineName();
    $this->mockContents = $this->randomMachineName();

    $this->objectRootStructure = [
      '0=ocfl_object_1.0' => "ocfl_object_1.0\n",
      'v1' => [
        'content' => [
          $this->mockFile => $this->mockContents,
        ],
      ],
    ];
    $this->objectRoot = vfsStream::setup('root', NULL, $this->objectRootStructure);

    $this->mockEvent = $this->createMock(OCFLResourceLocationEvent::class);
    $this->mockEvent->expects($this->any())
      ->method('getObjectRoot')
      ->with()
      ->willReturn($this->objectRoot->url());
  }

  /**
   * Test successful use of manifests.
   */
  public function testSuccessfulManifestLookup() {
    vfsStream::create(array_merge_recursive($this->objectRootStructure, [
      'v1' => [
        'content' => [
          '.fcrepo' => [
            'fcr-root.json' => json_encode([
              'interactionModel' => 'http://www.w3.org/ns/ldp#NonRDFSource',
              'contentPath' => $this->mockFile,
            ]),
          ],
        ],
      ],
    ]), $this->objectRoot);

    $this->mockEvent->expects($this->once())
      ->method('setResourcePath')
      ->with("{$this->objectRoot->url()}/v1/content/{$this->mockFile}");

    $this->mockEvent->expects($this->atLeastOnce())
      ->method('getInventory')
      ->with()
      ->willReturn([
        'head' => 'v1',
        'manifest' => [
          'asdf' => [
            'v1/content/.fcrepo/fcr-root.json',
          ],
          'qwer' => [
            "v1/content/{$this->mockFile}",
          ],
        ],
        'versions' => [
          'v1' => [
            'state' => [
              'asdf' => [
                '.fcrepo/fcr-root.json',
              ],
              'qwer' => [
                $this->mockFile,
              ],
            ],
          ],
        ],
      ]);

    $this->locator->getResourceFromFcrepoManifest($this->mockEvent);
  }

  /**
   * Test that not being present leads to skipping out.
   */
  public function testNoManifest() {
    $this->mockEvent->expects($this->never())
      ->method('setResourcePath')
      ->with($this->anything());

    $this->mockEvent->expects($this->atLeastOnce())
      ->method('getInventory')
      ->with()
      ->willReturn([
        'head' => 'v1',
        'manifest' => [
          'qwer' => [
            "v1/content/{$this->mockFile}",
          ],
        ],
        'versions' => [
          'v1' => [
            'state' => [
              'qwer' => [
                $this->mockFile,
              ],
            ],
          ],
        ],
      ]);

    $this->locator->getResourceFromFcrepoManifest($this->mockEvent);
  }

}
