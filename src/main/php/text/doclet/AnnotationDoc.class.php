<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.doclet.Doc');

  /**
   * Represents an annotation.
   *
   * @purpose  Documents an annotation
   */
  class AnnotationDoc extends Doc {
    public
      $value= NULL;
  
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
?>
