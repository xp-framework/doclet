<?php namespace text\doclet;



/**
 * Represents an @param documentation tag
 *
 * @see      xp://text.doclet.TagTag
 * @purpose  Tag
 */
class ParamTag extends Tag {
  public
    $type      = '',
    $parameter = '';

  /**
   * Constructor
   *
   * @param   string type
   * @param   string name
   * @param   string label
   */
  public function __construct($type, $name, $label) {
    parent::__construct('param', $label);
    $this->type= $type;
    $this->parameter= $name;
  }  
}
