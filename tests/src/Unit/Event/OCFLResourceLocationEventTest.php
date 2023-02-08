<?php

namespace Drupal\Tests\flysystem_ocfl\Unit\Event;

use Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent;
use Drupal\Tests\UnitTestCase;

/**
 * Test resource location event class.
 *
 * @group flysystem_ocfl
 */
class OCFLResourceLocationEventTest extends UnitTestCase {

  /**
   * Random string, for use as the "object root".
   *
   * @var string
   */
  protected string $marker;

  /**
   * Minimal requirements of an "inventory", for evaluation in the class tested.
   *
   * @var array
   */
  protected array $inventory;

  /**
   * Instance of the class under test.
   *
   * @var \Drupal\flysystem_ocfl\Event\OCFLResourceLocationEvent
   */
  protected OCFLResourceLocationEvent $instance;

  /**
   * {@inheritDoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->marker = $this->randomMachineName();
    $this->inventory = ['head' => $this->randomMachineName()];
    $this->instance = new OCFLResourceLocationEvent($this->marker, $this->inventory);
  }

  /**
   * Test base ::getObjectRoot() and ::getInventory() accessors.
   */
  public function testBaseAccessors() : void {
    $this->assertEquals($this->marker, $this->instance->getObjectRoot(), 'Object root checks out.');
    $this->assertEquals($this->inventory, $this->instance->getInventory(), 'Inventory checks out.');
  }

  /**
   * Test base set/get of resource path.
   */
  public function testResourcePath() : void {
    $nonce = $this->randomMachineName();
    $this->instance->setResourcePath($nonce);
    $this->assertTrue($this->instance->isPropagationStopped(), 'Event propagation stopped on set.');
    $this->assertEquals($nonce, $this->instance->getResourcePath(), 'Got the set path.');
  }

  /**
   * Test error of calling ::getResourcePath() without one having been set.
   */
  public function testGetResourcePathWithoutSet() : void {
    $this->expectException(\LogicException::class);
    $this->instance->getResourcePath();
  }

}
