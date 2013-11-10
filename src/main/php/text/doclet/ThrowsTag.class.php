<?php namespace text\doclet;



/**
 * Represents an @throws documentation tag
 *
 * @see      xp://Tag
 * @purpose  Tag
 */
class ThrowsTag extends Tag {
  public
    $exception = null;

  /**
   * Constructor
   *
   * @param   text.doclet.ClassDoc exception
   * @param   string label
   */
  public function __construct($exception, $label) {
    parent::__construct('throws', $label);
    $this->exception= $exception;
  }
}
