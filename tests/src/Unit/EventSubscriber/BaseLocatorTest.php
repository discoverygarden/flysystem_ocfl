<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\EventSubscriber;

use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Drupal\flysystem_ocfl\EventSubscriber\BaseLocator;
use Drupal\Tests\UnitTestCase;

/**
 * Test the responses of the base locator implementation.
 */
class BaseLocatorTest extends UnitTestCase {

  /**
   * Instance of the class under test.
   *
   * @var \Drupal\flysystem_ocfl\EventSubscriber\BaseLocator
   */
  protected BaseLocator $baseLocator;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->baseLocator = new BaseLocator();
  }

  /**
   * Test setting of path based on object path.
   */
  public function testInventoryResponse() {
    $mock_object_path = $this->randomMachineName();
    $mock_event = $this->createMock(OCFLInventoryLocationEvent::class);
    $mock_event->expects($this->atLeastOnce())
      ->method('getObjectPath')
      ->willReturn($mock_object_path);
    $mock_event->expects($this->once())
      ->method('setInventoryByPath')
      ->with("{$mock_object_path}/inventory.json");

    $this->baseLocator->loadObjectRootInventory($mock_event);
  }

  /**
   * Test exception throwing with unknown resource lookup method.
   */
  public function testResourceResponse() {
    $this->expectException(\LogicException::class);
    $mock_event = $this->createMock(OCFLResourceLocationEvent::class);
    $this->baseLocator->resourceNotFound($mock_event);
  }

}
