<?php

namespace Autoloader;

class Autoloader
{

  public static $namespaceSeparator = "\\";
  public static $includePath = "";
  public static $registered = array(
                                "directory" => array(),
                                "psr-4" => array()
                              );

  public static function register()
  {
    spl_autoload_register(array(__CLASS__,'autoloadPsr4'));
    spl_autoload_register(array(__CLASS__,'autoloadFromDirectory'));
  }

  public static function autoloadFromDirectory($name)
  {
    if (self::typeExists($name)) return;

    foreach (static::$registered["directory"] as $path) {
      // Change namespace separator to directory separator
      $name = str_replace(static::$namespaceSeparator, DIRECTORY_SEPARATOR, $name);

      $filename = static::$includePath . $path . DIRECTORY_SEPARATOR . $name . ".php";

      // remove double directory Separator
      $filename = str_replace("//", DIRECTORY_SEPARATOR, $filename);
      if(file_exists($filename)) {
        require $filename;
        return;
      } else {
        echo $filename;
      }
    }
  }

  public static function autoloadPsr4($name)
  {
    if (self::typeExists($name)) return;

    foreach(static::$registered["psr-4"] as $ns => $path){
      var_dump(strpos($name, $ns));
      if( strpos($name, $ns) === 0 ){
        return self::loadFromPsr4($name, $ns, $path);
      }
    }
  }

  public static function loadFromPsr4($name, $namespace, $path)
  {
    /* Remove prefix */
    $name = str_replace($namespace,'', $name);

    // Change namespace separator to directory separator
    $name = str_replace(static::$namespaceSeparator, DIRECTORY_SEPARATOR, $name);

    $filename = static::$includePath . $path . $name;
    // remove double directory Separator
    $filename = str_replace("//", DIRECTORY_SEPARATOR, $filename);

    if(file_exists($filename)) {
      require $filename;
      return;
    } else {
      echo $filename . "\n";
    }
  }

  public static function add($path) {
    if(!in_array($path, static::$registered["directory"])) {
      static::$registered["directory"][] = $path;
    }
  }

  public static function removePsr4($namespace, $path) {
    if(in_array($path, static::$registered["psr-4"])) {
      $key = array_search($path, static::$registered["psr-4"]);
      unset(static::$registered["psr-4"][$key]);
    }
  }

  public static function addPsr4($namespace, $path) {
    if(!in_array($path, static::$registered["psr-4"])) {
      static::$registered["psr-4"][$namespace] = $path;
    }
  }

  public static function remove($path) {
    if(in_array($path, static::$registered["directory"])) {
      $key = array_search($path, static::$registered["directory"]);
      unset(static::$registered["directory"][$key]);
    }
  }

  private static function typeExists($type, $autoload = false)
    {
      return class_exists($type, $autoload)
          || interface_exists($type, $autoload)
          || trait_exists($type, $autoload);
    }
}
