<?php namespace text\doclet;



/**
 * Represents an annotation.
 *
 * @purpose  Documents an annotation
 */
class AnnotationDoc extends Doc {
  public
    $value= null;

  /**
   * Constructor
   *
   * @param   string name
   * @param   var value
   */
  public function __construct($name, $value) {
    $this->name= $name;
    $this->value= $value;
  }
}
