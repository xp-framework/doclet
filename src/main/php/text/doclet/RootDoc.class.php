<?php namespace text\doclet;
 
use util\cmd\ParamString;
use lang\FileSystemClassLoader;
use lang\archive\ArchiveClassLoader;

define('OPTION_ONLY', 0x0000);
define('HAS_VALUE',   0x0001);

/**
 * Represents the root of the program structure information for one 
 * run. From this root all other program structure information can 
 * be extracted.
 *
 * Example:
 * <code>
 *   class TreeDoclet extends Doclet {
 *     // ...
 *   }
 *
 *   create(new RootDoc())->start(new TreeDoclet(), new ParamString());
 * </code>
 *
 * @test     xp://net.xp_framework.unittest.text.doclet.OptionsParserTest
 * @see      xp://text.doclet.Doclet
 * @purpose  Entry point
 */
class RootDoc extends \lang\Object {
  protected $sourcepath= array();

  const ST_INITIAL            = 'initial';
  const ST_CLASS_BODY         = 'classbody';
  const ST_USES               = 'uses';
  const ST_IMPLEMENTS         = 'implements';
  const ST_CLASS              = 'class';
  const ST_CLASS_VAR          = 'classvar';
  const ST_VARIABLE_VALUE     = 'variablevalue';
  const ST_FUNCTION           = 'function';
  const ST_FUNCTION_ARGUMENTS = 'functionarguments';
  const ST_ARGUMENT_VALUE     = 'argumentvalue';
  const ST_FUNCTION_BODY      = 'functionbody';
  const ST_DEFINE             = 'define';
  const ST_DEFINE_VALUE       = 'definevalue';
  const T_USES                = 0x1000;
  const T_PACKAGE             = 0x1001;
  const T_DEFINE              = 0x1002;

  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->setSourcePath(\xp::$classpath);
  }

  /**
   * Sets source path
   *
   * @param   string[] paths
   */
  public function setSourcePath(array $paths) {
    $this->sourcepath= array();
    foreach ($paths as $element) {
      $this->addSourcePath($element);
    }
  }
  
  /**
   * Adds a source path element
   *
   * @param   string element
   */
  public function addSourcePath($element) {
    $resolved= realpath($element);
    if (is_dir($resolved)) {
      $l= FileSystemClassLoader::instanceFor($resolved, false);
    } else if (is_file($resolved)) {
      $l= ArchiveClassLoader::instanceFor($resolved, false);
    } else {
      throw new \lang\IllegalArgumentException('Sourcepath element ['.$element.'] not found');
    }
    $this->sourcepath[$l->hashCode()]= $l;
  }

  /**
   * Adds a source loader
   *
   * @param   lang.IClassLoader
   */
  public function addSourceLoader(\lang\IClassLoader $l) {
    $this->sourcepath[$l->hashCode()]= $l;
  }

  /**
   * Start a doclet
   *
   * @param   text.doclet.Doclet doclet
   * @param   util.cmd.ParamString params
   * @return  var
   * @throws  lang.XPException in case doclet setup fails
   */
  public function start(Doclet $doclet, ParamString $params) {
    
    // BC Hack: Make this method callable statically, too (RootDoc::start(...))
    // This is deprecated but was the advertised way up until XP 5.7.3
    if (!isset($this)) $self= new self(); else $self= $this;
    
    // Separate options from classes
    $classes= array();
    $valid= $doclet->validOptions();
    for ($i= 1; $i < $params->count; $i++) {
      $option= $params->list[$i];
      if (0 == strncmp($option, '--', 2)) {        // Long: --foo / --foo=bar
        $p= strpos($option, '=');
        $name= substr($option, 2, false === $p ? strlen($option) : $p- 2);
        if (isset($valid[$name])) {
          if ($valid[$name] == HAS_VALUE) {
            $doclet->options[$name]= false === $p ? null : substr($option, $p+ 1);
          } else {
            $doclet->options[$name]= true;
          }
        }
      } else if (0 == strncmp($option, '-', 1)) {   // Short: -f / -f bar
        $name= substr($option, 1);
        if (isset($valid[$name])) {
          if ($valid[$name] == HAS_VALUE) {
            $doclet->options[$name]= $params->list[++$i];
          } else {
            $doclet->options[$name]= true;
          }
        }          
      } else {
        $classes[]= $option;
      }
    }
    
    // Set up class iterator
    $doclet->classes= $doclet->iteratorFor($self, $classes);

    // Start the doclet
    return $doclet->start($self);
  }
  
  /**
   * Finds a package by a given name
   *
   * @param   string package
   * @return  lang.IClassLoader the classloader providing the package
   */
  public function findPackage($package) {
    foreach (\lang\ClassLoader::getDefault()->getLoaders() as $loader) {
      if ($loader->providesPackage($package)) return $loader;
    }
    return null;
  }

  /**
   * Finds a resource file by a given name
   *
   * @param   string name
   * @return  lang.IClassLoader the classloader providing the resource
   */
  public function findResource($name) {
    foreach (\lang\ClassLoader::getDefault()->getLoaders() as $loader) {
      if ($loader->providesResource($name)) return $loader;
    }
    return null;
  }
  
  /**
   * Finds a class by a given class name
   *
   * @param   string classname
   * @return  lang.IClassLoader the classloader providing the class
   */
  public function findClass($classname) {
    foreach (\lang\ClassLoader::getDefault()->getLoaders() as $loader) {
      if ($loader->providesClass($classname)) return $loader;
    }
    return null;
  }

  /**
   * Gets all classes by a given package
   *
   * @param   string package
   * @param   bool recursive
   * @return  string[] fully qualified class names
   */
  public function classesIn($package, $recursive) {
    $r= array();
    $l= -strlen(\xp::CLASS_FILE_EXT);
    foreach ($this->sourcepath as $loader) {
      foreach ($loader->packageContents($package) as $name) {
        $appx= $package ? $package.'.' : '';
        if (\xp::CLASS_FILE_EXT === substr($name, $l)) {
          $r[]= $appx.substr($name, 0, $l);
        } else if ($recursive && '/' === substr($name, -1)) {
          $r= array_merge($r, $this->classesIn($appx.substr($name, 0, -1), $recursive));
        }
      }
    }
    return $r;
  }

  /**
   * Qualifies a class name by looking at known or used classes.
   *
   * @param   text.doclet.Doc doc
   * @param   string name
   * @return  string qualified name
   */
  public function qualifyName($doc, $name) {
    if (isset(\xp::$cn[$name])) {
      foreach ($doc->usedClasses->classes as $class) {
        if (literal($class) === $name) return $class;
      }
      $lookup= \xp::$cn[$name];
    } else {
      $lookup= null;
    }

    // Nothing found!
    if (!$lookup) throw new \lang\IllegalStateException(sprintf(
      'Could not find class %s in %s',
      \xp::stringOf($name),
      \xp::stringOf($this->sourcepath)
    ));
    
    return $lookup;
  }

  /**
   * Parses a package description file and returns a packagedoc element
   *
   * @param   string package
   * @return  text.doclet.PackageDoc
   * @throws  lang.IllegalArgumentException if package could not be found or parsed
   */
  public function packageNamed($package) {
    static $cache= array();
    static $map= array('package' => self::T_PACKAGE);

    if (isset($cache[$package])) return $cache[$package];

    // Find package
    if (!($loader= $this->findPackage($package))) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Could not find %s in %s',
        \xp::stringOf($package),
        \xp::stringOf($this->sourcepath)
      ));
    }

    with ($doc= new PackageDoc($package), $doc->setLoader($loader), $doc->setRoot($this)); {

      // Find package-info file. If we cannot find one, ignore it!
      $packageInfo= strtr($package, '.', '/').'/package-info.xp';
      if ($loader= $this->findResource($packageInfo)) {

        // Tokenize contents
        if (!($c= $loader->getResource($packageInfo))) {
          throw new \lang\IllegalArgumentException('Could not parse "'.$packageInfo.'"');
        }

        $tokens= token_get_all('<?php '.$c.' ?>');
        $annotations= $comment= null;
        $name= '';
        $state= self::ST_INITIAL;          
        for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
          $t= $tokens[$i];
          if (is_array($t) && isset($map[$t[1]])) $t[0]= $map[$t[1]];

          switch ($state.$t[0]) {
            case self::ST_INITIAL.T_DOC_COMMENT:
              $comment= $t[1];
              break;
          
            case self::ST_INITIAL.self::T_PACKAGE:
              $state= self::ST_CLASS;
              break;
            
            case self::ST_CLASS.T_STRING:
              $name.= $t[1];
              break;

            case self::ST_CLASS.'.':    // Package separator
              $name.= '.';
              break;
            
            case self::ST_CLASS.'{':
              if ($name !== $package) {
                throw new \lang\IllegalArgumentException('Package "'.$package.'" contains package "'.$name.'"');
              }
              $doc->name= $name;
              $doc->rawComment= $comment;
              $doc->annotations= $annotations;
              $comment= $annotations= null;
              $name= '';
              $state= self::ST_CLASS_BODY;
              break;
            
            case self::ST_CLASS_BODY.'}':
              $state= self::ST_INITIAL;
              break;
          }
        }
      }
    }      
    return $cache[$package]= $doc;
  }
  
  /**
   * Parses a class file and returns a classdoc element
   *
   * @param   string classname fully qualified class name
   * @return  text.doclet.ClassDoc
   * @throws  lang.IllegalArgumentException if class could not be found or parsed
   */
  public function classNamed($classname) {
    static $cache= array();
    static $map= array('uses' => self::T_USES, 'define' => self::T_DEFINE);

    // Check cache
    if (isset($cache[$classname])) return $cache[$classname];
    
    // Check for php namespace - in this case, we have a builtin class. These
    // classes will not be documented for the moment.
    if ('php.' == substr($classname, 0, 4)) return null;

    // Find class
    if (!($loader= $this->findClass($classname))) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Could not find %s in %s',
        \xp::stringOf($classname),
        \xp::stringOf($this->sourcepath)
      ));
    }
    
    // Tokenize contents
    $tokens= @token_get_all($loader->loadClassBytes($classname));
    if (!$tokens || T_OPEN_TAG !== $tokens[0][0]) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Could not parse "%s" from %s, first token: %s',
        $classname,
        \xp::stringOf($loader),
        \xp::stringOf($tokens[0])
      ));
    }

    with ($doc= new ClassDoc(), $doc->setLoader($loader), $doc->setRoot($this)); {
      $annotations= $comment= $package= null;
      $modifiers= array();
      $state= self::ST_INITIAL;          
      for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
        $t= $tokens[$i];
        if (is_array($t) && isset($map[$t[1]])) $t[0]= $map[$t[1]];

        switch ($state.$t[0]) {
          case self::ST_INITIAL.T_DOC_COMMENT:
          case self::ST_CLASS_BODY.T_DOC_COMMENT:
            $comment= $t[1];
            break;

          case self::ST_INITIAL.T_COMMENT:
          case self::ST_CLASS_BODY.T_COMMENT:
            if (strncmp('#[@', $t[1], 3) == 0) {
              $annotations= substr($t[1], 2);
            } else if (strncmp('#', $t[1], 1) == 0) {
              $annotations.= substr($t[1], 1);
            }

            if (']' == substr(rtrim($t[1]), -1)) {
              $annotations= '['.trim($annotations);
            }
            break;
            
          case self::ST_INITIAL.T_VARIABLE:
            if ('$package' === $t[1]) {   // RFC #0037: $package= 'lang.reflect';
              while (T_CONSTANT_ENCAPSED_STRING !== $tokens[$i][0] && $i < $s) $i++;
              $package= trim($tokens[$i][1], '\'"');
            }
            break;

          case self::ST_INITIAL.self::T_USES:
            $state= self::ST_USES;
            break;

          case self::ST_USES.T_CONSTANT_ENCAPSED_STRING:
            $cn= trim($t[1], '"\'');
            if (!$this->findClass($cn)) throw new \lang\IllegalStateException(
              'Could not find used class "'.$cn.'" for class '.$classname.' in '.\xp::stringOf($this->sourcepath)
            );
            $doc->usedClasses->classes[]= $cn;
            break;

          case self::ST_USES.')':
            $state= self::ST_INITIAL;
            break;

          case self::ST_INITIAL.self::T_DEFINE:
            $state= self::ST_DEFINE;
            break;

          case self::ST_INITIAL.T_NAMESPACE:
            $package= '';
            $i+= 2; // Eat "namespace"
            while (';' !== $tokens[$i][0] && $i < $s) {
              $package.= trim($tokens[$i++][1]);
            }

            $package= strtr($package, '\\', '.');
            break;

          case self::ST_DEFINE.T_CONSTANT_ENCAPSED_STRING:
            $state= self::ST_DEFINE_VALUE;
            $define= trim($t[1], '"\'');
            break;

          case self::ST_DEFINE_VALUE.T_CONSTANT_ENCAPSED_STRING:
          case self::ST_DEFINE_VALUE.T_LNUMBER:
          case self::ST_DEFINE_VALUE.T_DNUMBER:
          case self::ST_DEFINE_VALUE.T_STRING:
            $doc->constants[$define]= $t[1];
            break;

          case self::ST_DEFINE_VALUE.')':
            $state= self::ST_INITIAL;
            break;

          case self::ST_INITIAL.T_INTERFACE:
            $doc->type= INTERFACE_CLASS;
            // Fall-through intended

          case self::ST_INITIAL.T_CLASS:
            while (T_STRING !== $tokens[$i][0] && $i < $s) $i++;

            $name= $tokens[$i][1];
            if ($package && substr(strtr($name, '�', '.'), 0, strlen($package)) == $package) {
              $name= substr($name, strlen($package)+ 1);
            }

            $doc->name= $name;
            $doc->qualifiedName= $classname;
            $doc->rawComment= $comment;
            $doc->annotations= $annotations;
            $doc->modifiers= $modifiers;
            $comment= $annotations= null;
            $modifiers= array();
            $state= self::ST_CLASS;
            break;

          case self::ST_CLASS.T_EXTENDS:
            while (T_STRING !== $tokens[$i][0] && $i < $s) $i++;

            $doc->superclass= $this->classNamed($this->qualifyName($doc, $tokens[$i][1]));
            break;

          case self::ST_CLASS.T_IMPLEMENTS:
            $state= self::ST_IMPLEMENTS;
            break;
          
          case self::ST_IMPLEMENTS.T_STRING:
            $doc->interfaces->classes[]= $this->qualifyName($doc, $t[1]);
            break;

          case self::ST_CLASS.'{':
          case self::ST_IMPLEMENTS.'{':
            $state= self::ST_CLASS_BODY;
            break;

          case self::ST_CLASS_BODY.T_VARIABLE;
            $state= self::ST_CLASS_VAR;
            // Fall-through intended

          case self::ST_CLASS_VAR.T_VARIABLE;
            unset($field);
            $field= new FieldDoc();
            $field->declaring= $doc;
            $field->name= $t[1];
            $field->modifiers= $modifiers;
            break;

          case self::ST_CLASS_VAR.'=':
            $state= self::ST_VARIABLE_VALUE;
            break;

          case self::ST_CLASS_VAR.',':
            $doc->fields[]= $field;
            break;

          case self::ST_CLASS_VAR.';':
            $doc->fields[]= $field;
            $state= self::ST_CLASS_BODY;
            $modifiers= array();
            break;

          case self::ST_VARIABLE_VALUE.T_CONSTANT_ENCAPSED_STRING:
          case self::ST_VARIABLE_VALUE.T_LNUMBER:
          case self::ST_VARIABLE_VALUE.T_DNUMBER:
          case self::ST_VARIABLE_VALUE.T_STRING:
            $field->constantValue= $t[1];
            $state= self::ST_CLASS_VAR;
            break;

          case self::ST_VARIABLE_VALUE.T_ARRAY:
            $brackets= 0;
            $src= '';
            do {
              $t= $tokens[$i];
              $src.= is_array($t) ? $t[1] : $t;
              if ('(' == $t[0]) {
                $brackets++;
              } else {
                if (')' == $t[0] and --$brackets <= 0) break;
              }
            } while (++$i < $s);

            $field->constantValue= $src;
            $state= self::ST_CLASS_VAR;
            break;
         
          // Before member declaration (e.g. public static $..., protected function ...)
          case self::ST_CLASS_BODY.T_PUBLIC:
          case self::ST_CLASS_BODY.T_PRIVATE:
          case self::ST_CLASS_BODY.T_PROTECTED:
          case self::ST_CLASS_BODY.T_STATIC:
          case self::ST_CLASS_BODY.T_FINAL:
          case self::ST_CLASS_BODY.T_ABSTRACT:
          
          // Before class declaration (e.g. abstract class ...)
          case self::ST_INITIAL.T_FINAL:
          case self::ST_INITIAL.T_ABSTRACT:
            $modifiers[$t[1]]= true;
            break;
          
          case self::ST_CLASS_BODY.T_FUNCTION:
            while (T_STRING !== $tokens[$i][0] && $i < $s) $i++;

            with ($method= new MethodDoc(), $method->setRoot($this)); {
              $method->name= $tokens[$i][1];
              $method->rawComment= $comment;
              $method->annotations= $annotations;
              $method->modifiers= $modifiers;
              $method->declaring= $doc;
              
              // Omit static initializer, it's not a real function
              if ('__static' != $method->name) $doc->methods[]= $method;
            }
            $comment= $annotations= null;
            $modifiers= array();
            $state= self::ST_FUNCTION;
            break;

          case self::ST_FUNCTION.'(':
            $state= self::ST_FUNCTION_ARGUMENTS;
            $argument= null;
            break;

          case self::ST_FUNCTION_ARGUMENTS.T_VARIABLE:
            $argument= $t[1];
            break;

          case self::ST_FUNCTION_ARGUMENTS.',':
            $method->arguments[$argument]= null;
            break;

          case self::ST_FUNCTION_ARGUMENTS.'=':
            $state= self::ST_ARGUMENT_VALUE;
            break;

          case self::ST_ARGUMENT_VALUE.T_CONSTANT_ENCAPSED_STRING:
          case self::ST_ARGUMENT_VALUE.T_LNUMBER:
          case self::ST_ARGUMENT_VALUE.T_DNUMBER:
          case self::ST_ARGUMENT_VALUE.T_STRING:
            $method->arguments[$argument]= $t[1];
            break;

          case self::ST_ARGUMENT_VALUE.T_ARRAY:
            $brackets= 0;
            $src= '';
            do {
              $t= $tokens[$i];
              $src.= is_array($t) ? $t[1] : $t;
              if ('(' == $t[0]) {
                $brackets++;
              } else {
                if (')' == $t[0] and --$brackets <= 0) break;
              }
            } while (++$i < $s);

            $method->arguments[$argument]= $src;
            break;

          case self::ST_ARGUMENT_VALUE.',':
            $state= self::ST_FUNCTION_ARGUMENTS;
            break;

          case self::ST_ARGUMENT_VALUE.')':
            $state= self::ST_FUNCTION;
            break;

          case self::ST_FUNCTION_ARGUMENTS.')':
            $argument && $method->arguments[$argument]= null;
            $state= self::ST_FUNCTION;
            break;

          case self::ST_FUNCTION.';':   // Interface and abstract methods have no body
            $state= self::ST_CLASS_BODY;
            break;        

          case self::ST_FUNCTION.'{':       
            $brackets= 0;
            do {
              $c= $tokens[$i][0];
              if ('{' == $c) {
                $brackets++; 
              } else { 
                if ('}' == $c and --$brackets <= 0) break;
              }
            } while (++$i < $s);

            $state= self::ST_CLASS_BODY;
            break;        

          case self::ST_CLASS_BODY.'}':
            $state= self::ST_INITIAL;
            break;
        }
      }
    }
    
    return $cache[$classname]= $doc;
  }
}
