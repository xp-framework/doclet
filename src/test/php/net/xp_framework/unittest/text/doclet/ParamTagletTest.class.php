<?php namespace net\xp_framework\unittest\text\doclet;

use unittest\TestCase;
use text\doclet\TagletManager;
use text\doclet\RootDoc;


/**
 * TestCase for param taglet
 *
 * @see      xp://text.doclet.ParamTaglet
 * @purpose  Unittest for text.doclet API
 */
class ParamTagletTest extends TestCase {
  protected
    $holder  = null;

  /**
   * Sets up test case
   *
   */
  public function setUp() {
    $this->holder= new RootDoc();
    $this->holder->addSourceLoader($this->getClass()->getClassLoader());
  }
  
  /**
   * Create a param tag for a given text
   *
   * @param   string text
   * @return  text.doclet.ParamTag
   */
  protected function makeParam($text) {
    return TagletManager::getInstance()->make($this->holder, 'param', $text);
  }
  
  /**
   * Test class parameter
   *
   */
  #[@test]
  public function classParam() {
    $t= $this->makeParam('io.File file');
    $this->assertEquals('io.File', $t->type);
    $this->assertEquals('file', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test class parameter
   *
   */
  #[@test]
  public function classParamWithText() {
    $t= $this->makeParam('io.File file The file to be deleted. Must be closed!');
    $this->assertEquals('io.File', $t->type);
    $this->assertEquals('file', $t->parameter);
    $this->assertEquals('The file to be deleted. Must be closed!', $t->text);
  }

  /**
   * Test primitive parameter
   *
   */
  #[@test]
  public function primitiveParam() {
    $t= $this->makeParam('string filename');
    $this->assertEquals('string', $t->type);
    $this->assertEquals('filename', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test array parameter
   *
   */
  #[@test]
  public function arrayParam() {
    $t= $this->makeParam('string[] names');
    $this->assertEquals('string[]', $t->type);
    $this->assertEquals('names', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test vararg parameter
   *
   */
  #[@test]
  public function varargParam() {
    $t= $this->makeParam('string* names');
    $this->assertEquals('string*', $t->type);
    $this->assertEquals('names', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test generic parameter
   *
   */
  #[@test]
  public function mapParam() {
    $t= $this->makeParam('[:string] map');
    $this->assertEquals('[:string]', $t->type);
    $this->assertEquals('map', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test generic parameter with fully qualified class names
   *
   */
  #[@test]
  public function qualifiedGenericParam() {
    $t= $this->makeParam('util.collection.HashTable<lang.types.Number, lang.types.String> map');
    $this->assertEquals('util.collection.HashTable<lang.types.Number, lang.types.String>', $t->type);
    $this->assertEquals('map', $t->parameter);
    $this->assertEquals('', $t->text);
  }

  /**
   * Test generic parameter with fully qualified class names
   *
   */
  #[@test]
  public function genericsOfGenericsParam() {
    $t= $this->makeParam('HashTable<string, Vector<int>> map');
    $this->assertEquals('HashTable<string, Vector<int>>', $t->type);
    $this->assertEquals('map', $t->parameter);
    $this->assertEquals('', $t->text);
  }
}
