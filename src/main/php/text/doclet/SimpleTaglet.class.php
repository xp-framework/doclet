<?php namespace text\doclet;



/**
 * A taglet that represents simple tags.
 *
 * @see      xp://text.doclet.TagletManager
 * @purpose  Taglet
 */
class SimpleTaglet extends \lang\Object implements Taglet {
   
  /**
   * Create tag from text
   *
   * @param   text.doclet.Doc holder
   * @param   string kind
   * @param   string text
   * @return  text.doclet.Tag
   */ 
  public function tagFrom($holder, $kind, $text) {
    return new Tag($kind, $text);
  }
} 
