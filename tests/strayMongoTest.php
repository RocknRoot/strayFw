<?php

class strayMongoTest extends PHPUnit_Framework_TestCase
{
  private $_dbName = '_strayTestSuite';

  public function testGetDb()
  {
    $db = strayMongo::fGetInstance()->GetDb($this->_dbName);
    $this->assertInstanceOf('MongoDB', $db);
    $this->assertEquals($this->_dbName, $db->__toString());
    $this->assertEquals(1, $db->lastError()['ok']);
  }

  public function testInsert()
  {
    return;
    $mongo = strayMongo::fGetInstance()->GetDb($this->_dbName);
    $db = strayMongoDb::fGetInstance($this->_dbName);
    $this->assertTrue($db->Insert('simple_data', [ 'lol' => true, 'inner' => [ 'rockin\' all night' ] ]));
    $this->assertTrue($mongo->selectCollection('simple_data')->findOne([ ], [ 'lol' ]));
    $this->assertEquals('rockin\' all night', $mongo->simple_data->inner->findOne());
  }

  public function testQueryFetch()
  {
  }

  public function testQueryFetchAll()
  {
  }

  public function testQueryFetchRemoveOne()
  {
  }

  public function testQueryFetchRemove()
  {
  }
}
