<?php namespace text\doclet;

use lang\ElementNotFoundException;


/**
 * A taglet that represents the throws tag. 
 *
 * @test     xp://net.xp_framework.unittest.doclet.ThrowsTagletTest
 * @see      xp://text.doclet.TagletManager
 * @purpose  Taglet
 */
class ThrowsTaglet extends \lang\Object implements Taglet {
   
  /**
   * Create tag from text
   *
   * @param   text.doclet.Doc holder
   * @param   string kind
   * @param   string text
   * @return  text.doclet.Tag
   * @throws  lang.ElementNotFoundException if the class cannot be found
   */ 
  public function tagFrom($holder, $kind, $text) {
    sscanf($text, '%s %[^$]', $class, $condition);
    try {
      $classDoc= $holder->root->classNamed($class);
    } catch (\lang\IllegalArgumentException $e) {
      throw new ElementNotFoundException('@'.$holder->toString().': '.$e->getMessage());
    }
    return new ThrowsTag($classDoc, (string)$condition);
  }
} 
