<?php namespace text\doclet;



/**
 * A taglet that represents the test tag. 
 *
 * @see      xp://text.doclet.TagletManager
 * @purpose  Taglet
 */
class TestTaglet extends \lang\Object implements Taglet {

  /**
   * Create tag from text
   *
   * @param   text.doclet.Doc holder
   * @param   string kind
   * @param   string text
   * @return  text.doclet.Tag
   */ 
  public function tagFrom($holder, $kind, $text) {
    sscanf($text, '%[^:]://%s', $scheme, $urn);
    return new TestTag($kind, $scheme, $urn);
  }
} 
