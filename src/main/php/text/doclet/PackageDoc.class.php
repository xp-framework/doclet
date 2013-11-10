<?php namespace text\doclet;
 


/**
 * Represents an XP package.
 *
 * @test     xp://net.xp_framework.unittest.text.doclet.PackageDocTest
 * @purpose  Documents a package
 */
class PackageDoc extends Doc {
  protected
    $loader         = null;

  /**
   * Constructor
   *
   */
  public function __construct($name= null) {
    $this->name= $name;
  }

  /**
   * Set class loader this classdoc was loaded from
   *
   * @param   lang.IClassLoader loader
   */
  public function setLoader($loader) {
    $this->loader= $loader;
  }

  /**
   * Returns the source file name this doc was parsed from.
   *
   * @return  io.File
   */
  public function sourceFile() {
    return $this->loader
      ? $this->loader->getResourceAsStream(strtr($this->name, '.', '/').'/package-info.xp')
      : null
    ;
  }

  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'<'.$this->name.'>';
  }

  /**
   * Returns a hashcode for this object
   *
   * @return  string
   */
  public function hashCode() {
    return $this->getClassName().$this->name;
  }

  /**
   * Returns whether this package contains another package.
   *
   * @param   text.doclet.PackageDoc other
   * @return  bool
   */
  public function contains(PackageDoc $other) {
    if (false === ($p= strrpos($other->name, '.'))) return false;
    return $this->name === substr($other->name, 0, $p);
  }

  /**
   * Returns the package this class is contained in
   *
   * @return  text.doclet.PackageDoc or NULL if this is a top-level package
   */
  public function containingPackage() {
    if (false === ($p= strrpos($this->name, '.'))) return null;
    return $this->root->packageNamed(substr($this->name, 0, $p));
  }
}
