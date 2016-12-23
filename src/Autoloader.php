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
    spl_autoload_register(array(__CLASS__,'autoloadFromPsr4'));
    spl_autoload_register(array(__CLASS__,'autoloadFromDirectory'));
  }

  public static function add($type, $opts) {
    switch ($type) {
      case 'psr-4':
      case 'psr4':
      foreach ($opts as $namespace => $path) {
        static::addPsr4($key, $path);
      }
      break;
      case 'directory':
      foreach ($opts as $path) {
        static::addDirectory($path);
      }
      break;
    }
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
      }
    }
  }

  public static function autoloadFromPsr4($name)
  {
    if (self::typeExists($name)) return;

    foreach(static::$registered["psr-4"] as $ns => $path){
      if( strpos($name, $ns) === 0 ){
        return self::loadFromPsr4($name, $ns, $path);
      }
    }
  }

  public static function addDirectory($path) {
    if(!in_array($path, static::$registered["directory"])) {
      static::$registered["directory"][] = $path;
    }
  }

  public static function addPsr4($namespace, $path) {
    if(!in_array($path, static::$registered["psr-4"])) {
      static::$registered["psr-4"][$namespace] = $path;
    }
  }

  public static function removeDirectory($path) {
    if(in_array($path, static::$registered["directory"])) {
      $key = array_search($path, static::$registered["directory"]);
      unset(static::$registered["directory"][$key]);
    }
  }

  public static function removePsr4($namespace, $path) {
    if(in_array($path, static::$registered["psr-4"])) {
      $key = array_search($path, static::$registered["psr-4"]);
      unset(static::$registered["psr-4"][$key]);
    }
  }

  private static function loadFromPsr4($name, $namespace, $path)
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
    }
  }

  private static function typeExists($type, $autoload = false)
  {
    return class_exists($type, $autoload)
        || interface_exists($type, $autoload)
        || trait_exists($type, $autoload);
  }
}
