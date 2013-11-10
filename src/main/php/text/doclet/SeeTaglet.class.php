<?php namespace text\doclet;



/**
 * A taglet that represents the see tag. 
 *
 * @test     xp://net.xp_framework.unittest.doclet.SeeTagletTest
 * @see      xp://text.doclet.TagletManager
 * @purpose  Taglet
 */
class SeeTaglet extends \lang\Object implements Taglet {

  /**
   * Create tag from text
   *
   * @param   text.doclet.Doc holder
   * @param   string kind
   * @param   string text
   * @return  text.doclet.Tag
   */ 
  public function tagFrom($holder, $kind, $text) {
    sscanf($text, '%[^:]://%s %[^$]', $scheme, $urn, $comment);
    return new SeeTag($kind, (string)$comment, $scheme, $urn);
  }
} 
