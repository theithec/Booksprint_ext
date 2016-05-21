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
  public static function onArticleSaveComplete( &$article, &$user, $text, $summary, $minoredit, $watchthis,
    $sectionanchor, &$flags, $revision, &$status, $baseRevId ){
    //$context = RequestContext::getMain(); 
    //$out = $context->getOutput();  
    return true;
    $title = $article->getTitle();
    $url = $title->getPrefixedUrl();
    $ar = explode("/", $url);
    $possibleBookname = $ar[0];
    $validation  = new BookValidation($possibleBookname);
    //var_dump($validation);
    $possibleBookTitle = Title::newFromText($possibleBookname);
    $categoryTree = $possibleBookTitle->getParentCategoryTree();
    $categories = array_keys($categoryTree);
    var_dump($categories);
    $isBook = in_array('Kategorie:Buch', $categories);
    echo("IS BOOK ". $isBook);

    global $wgOut ;
 
    //$status->fatal(new RawMessage("Your Error Message Here!"));
    $wgOut->redirect( $possibleBookTitle->getFullURL() );
    //exec (
    return true;
    //die( $possibleBookTitle->getFullURL());
    //$possibleBookpage = WikiPage::factory( $possibleBookTitle );
    //$base_data =  $bookpage->getText(Revision::FOR_PUBLIC); //pageDataFromTitle($db, $title);
    //die($base_data);
    //die($title);
    //die(var_dump(array_keys($parenttree)));
    //die($url);
    //$out->redirect("/w"); //$url);
    //return true;
  }

}
