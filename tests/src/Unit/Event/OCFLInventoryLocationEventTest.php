<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Event;

use Drupal\flysystem_ocfl\Event\OCFLInventoryLocationEvent;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Test inventory location event class.
 *
 * @group flysystem_ocfl
 */
class OCFLInventoryLocationEventTest extends UnitTestCase {

  /**
   * Test ::getObjectPath().
   */
  public function testGetObjectPath() : void {
    $name = $this->randomMachineName();
    $instance = new OCFLInventoryLocationEvent($name);
    $this->assertEquals($instance->getObjectPath(), $name);
  }

  /**
   * Test ::setInventoryByPath() method.
   */
  public function testSetInventoryByPath() : void {
    $marker = $this->randomMachineName();
    $root = vfsStream::setup('root', NULL, [
      'inventory.json' => json_encode([
        'head' => $marker,
      ]),
    ]);
    $instance = new OCFLInventoryLocationEvent($root->url());
    $instance->setInventoryByPath("{$root->url()}/inventory.json");
    $this->assertTrue($instance->isPropagationStopped(), 'Event propagation is stopped.');
    $inventory = $instance->getInventory();
    $this->assertIsArray($inventory, 'The inventory is an array.');
    $this->assertArrayHasKey('head', $inventory, 'Appears to have the key.');
    $this->assertEquals($marker, $inventory['head'], 'Key has the expected value.');
  }

  /**
   * Test ::setInventory() method.
   */
  public function testSetInventory() : void {
    $marker = $this->randomMachineName();

    $instance = new OCFLInventoryLocationEvent($this->randomMachineName());
    $instance->setInventory([
      'head' => $marker,
    ]);
    $this->assertTrue($instance->isPropagationStopped(), 'Event propagation is stopped.');
    $inventory = $instance->getInventory();
    $this->assertIsArray($inventory, 'The inventory is an array.');
    $this->assertArrayHasKey('head', $inventory, 'Appears to have the key.');
    $this->assertEquals($marker, $inventory['head'], 'Key has the expected value.');
  }

  /**
   * Test error of attempting to ::getInventory() without it having been set.
   */
  public function testGetInventoryWithoutSet() : void {
    $this->expectException(\LogicException::class);
    $instance = new OCFLInventoryLocationEvent($this->randomMachineName());
    $instance->getInventory();
  }

}
