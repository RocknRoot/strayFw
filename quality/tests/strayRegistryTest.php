<?php

class strayRegistryTest extends PHPUnit_Framework_TestCase
{
  public function testNull()
  {
    $this->assertNull(strayRegistry::fGetInstance()->Get('undefined'));
  }

  public function testSimple()
  {
    $this->assertEquals('so badass', strayRegistry::fGetInstance()->Get('simple', function($name)
    {
      $this->assertEquals('simple', $name);
      return 'so badass';
    }));
  }

  public function testAlreadyDefined()
  {
    strayRegistry::fGetInstance()->Get('already_defined', function($name)
    {
      return 'all hail the new flesh';
    });
    $this->assertNotEquals('room 429', strayRegistry::fGetInstance()->Get('already_defined', function($name)
    {
      return 'room 429';
    }));
  }
}
