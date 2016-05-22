<?php
/**
 * Hooks for Bookmaker extension
 *
 * @file
 * @ingroup Extensions
 */

require_once("BookValidation.php");

class BookmakerHooks {
  public static function onArticlePageDataBefore($article, $fields){
    global $wgDefaultSkin;
    $wgDefaultSkin = "handbuchio2"; //getSkinForBookOrDefault($bookSkinPrefix , $bookSkinMode);
  
  }
}
