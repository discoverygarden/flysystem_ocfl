<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\EventSubscriber\MutableHeadInventoryLocator;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Test resolution of mutable head inventory.
 *
 * @see https://ocfl.github.io/extensions/0005-mutable-head.html
 *
 * @group flysystem_ocfl
 */
class MutableHeadInventoryLocatorTest extends UnitTestCase {

  /**
   * Instance of the class under test.
   *
   * @var \Drupal\flysystem_ocfl\EventSubscriber\MutableHeadInventoryLocator
   */
  protected MutableHeadInventoryLocator $locator;

  /**
   * {@inheritDoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->locator = new MutableHeadInventoryLocator();
  }

  /**
   * Test successful identification of the inventory.
   */
  public function testSuccess() : void {
    $root = vfsStream::setup('root', NULL, [
      'extensions' => [
        '0005-mutable-head' => [
          'head' => [
            'inventory.json' => '{}',
          ],
        ],
      ],
    ]);

    $mock_event = $this->createMock(OCFLInventoryLocationEvent::class);
    $mock_event->expects($this->atLeastOnce())
      ->method('getObjectPath')
      ->with()
      ->willReturn($root->url());
    $mock_event->expects($this->atLeastOnce())
      ->method('setInventoryByPath')
      ->with("{$root->url()}/extensions/0005-mutable-head/head/inventory.json");

    $this->locator->setMutableHeadInventoryIfPresent($mock_event);
  }

  /**
   * Test error condition of extension directory being present, with 'head' dir.
   */
  public function testMissingInventoryButHead() : void {
    $root = vfsStream::setup('root', NULL, [
      'extensions' => [
        '0005-mutable-head' => [
          'head' => [],
        ],
      ],
    ]);

    $mock_event = $this->createMock(OCFLInventoryLocationEvent::class);
    $mock_event->expects($this->atLeastOnce())
      ->method('getObjectPath')
      ->with()
      ->willReturn($root->url());
    $mock_event->expects($this->never())
      ->method('setInventoryByPath');

    $this->expectException(\LogicException::class);
    $this->locator->setMutableHeadInventoryIfPresent($mock_event);
  }

  /**
   * Test error condition of extension directory being present, without 'head'.
   */
  public function testMissingInventoryOrHead() {
    $root = vfsStream::setup('root', NULL, [
      'extensions' => [
        '0005-mutable-head' => [],
      ],
    ]);

    $mock_event = $this->createMock(OCFLInventoryLocationEvent::class);
    $mock_event->expects($this->atLeastOnce())
      ->method('getObjectPath')
      ->with()
      ->willReturn($root->url());
    $mock_event->expects($this->never())
      ->method('setInventoryByPath');

    $this->expectException(\LogicException::class);
    $this->locator->setMutableHeadInventoryIfPresent($mock_event);
  }

  /**
   * Test that evaluation skips out when not present.
   */
  public function testNotMutableHead() : void {
    $root = vfsStream::setup('root', NULL, []);

    $mock_event = $this->createMock(OCFLInventoryLocationEvent::class);
    $mock_event->expects($this->atLeastOnce())
      ->method('getObjectPath')
      ->with()
      ->willReturn($root->url());
    $mock_event->expects($this->never())
      ->method('setInventoryByPath');

    $this->locator->setMutableHeadInventoryIfPresent($mock_event);
  }

}
