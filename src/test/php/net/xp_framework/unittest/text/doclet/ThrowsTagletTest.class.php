<?php namespace net\xp_framework\unittest\text\doclet;

use unittest\TestCase;
use text\doclet\TagletManager;
use text\doclet\RootDoc;
use text\doclet\Doc;


/**
 * TestCase for return taglet
 *
 * @see      xp://text.doclet.ThrowsTaglet
 * @purpose  Unittest for text.doclet API
 */
class ThrowsTagletTest extends TestCase {
  protected
    $holder  = null;

  /**
   * Sets up test case
   *
   */
  public function setUp() {
    $root= new RootDoc();
    $root->addSourceLoader($this->getClass()->getClassLoader());
    $this->holder= new Doc();
    $this->holder->setRoot($root);
  }
  
  /**
   * Create a return tag for a given text
   *
   * @param   string text
   * @return  text.doclet.ReturnTag
   */
  protected function makeThrows($text) {
    return TagletManager::getInstance()->make($this->holder, 'throws', $text);
  }

  /**
   * Test lang.IllegalArgumentException
   *
   */
  #[@test]
  public function illegalArgumentException() {
    $t= $this->makeThrows('lang.IllegalArgumentException');
    $this->assertClass($t->exception, 'text.doclet.ClassDoc');
    $this->assertEquals('lang.IllegalArgumentException', $t->exception->qualifiedName());
    $this->assertEquals('', $t->text);
  }

  /**
   * Test lang.IllegalArgumentException
   *
   */
  #[@test]
  public function illegalArgumentExceptionWithText() {
    $t= $this->makeThrows('lang.IllegalArgumentException In case the argument is less than zero');
    $this->assertClass($t->exception, 'text.doclet.ClassDoc');
    $this->assertEquals('lang.IllegalArgumentException', $t->exception->qualifiedName());
    $this->assertEquals('In case the argument is less than zero', $t->text);
  }

  /**
   * Test with an exception class that does not exist
   *
   */
  #[@test, @expect('lang.ElementNotFoundException')]
  public function nonExistantException() {
    $this->makeThrows('@@DOES-NOT-EXIST@@');
  }
}
