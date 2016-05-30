<?php
/**
 * Hooks for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once("BookValidation.php");

class Booksprint_extHooks {
  public static function onArticlePageDataBefore($article, $fields){
    global $wgDefaultSkin;
    $wgDefaultSkin = "handbuchio2"; //getSkinForBookOrDefault($bookSkinPrefix , $bookSkinMode);
  
  }
}
