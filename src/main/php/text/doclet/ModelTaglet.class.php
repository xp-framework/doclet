<?php namespace text\doclet;



/**
 * A taglet that represents the model tag. 
 *
 * @see      xp://text.doclet.TagletManager
 * @purpose  Taglet
 */
class ModelTaglet extends \lang\Object implements Taglet {
   
  /**
   * Create tag from text
   *
   * @param   text.doclet.Doc holder
   * @param   string kind
   * @param   string text
   * @return  text.doclet.Tag
   */ 
  public function tagFrom($holder, $kind, $text) {
    return new ModelTag($kind, $text);
  }

} 
