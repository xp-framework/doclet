<?php namespace text\doclet;

use lang\reflect\ClassParser;

/**
 * Represents annotated doc classes.
 *
 * @see   xp://text.doclet.ClassDoc
 * @see   xp://text.doclet.MethodDoc
 */
class AnnotatedDoc extends Doc {
  public $annotations= null;
  public $_parsed    = null;
    
  /**
   * Parse annotations from string
   *
   * @throws  lang.FormatException in case the annotations cannot be parsed
   */    
  protected function parse() {
    if (is_array($this->_parsed)) return;   // Short-cuircuit: We've already parsed it
    
    $this->_parsed= [];
    if ($this->annotations) {
      $parser= new ClassParser();
      foreach ($parser->parseAnnotations($this->annotations, $this->getClassName())[0] as $name => $value) {
        $this->_parsed[$name]= new AnnotationDoc($name, $value);
      }
    }
  }
   
  /**
   * Retrieves a list of all annotations
   *
   * @return  text.doclet.AnnotationDoc[]
   */ 
  public function annotations() {
    $this->parse();
    return array_values($this->_parsed);
  }
}
