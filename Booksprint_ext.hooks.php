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
		//$wgDefaultSkin = "Booksprint_skin"; //getSkinForBookOrDefault($bookSkinPrefix , $bookSkinMode);

	}

	public static function onParserSetup(Parser $parser){
		$parser->setHook("bookinfo", "Booksprint_extHooks::renderTagBookinfo");
	}
	public static function renderTagBookinfo($input, array $args, Parser $parser, PPFrame $frame){
		$html = "";
		$title = $parser->getTitle();
		$required = array( "abstract", "status", "info", "authors");
		$missing = array();
		$argsKeys = array_keys($args);
		foreach ($required as $r){
			if (! in_array($r , $argsKeys)){
				$missing[] = '<b class="error">Angabe fehlt: '. $r. '  </b>';
			}

		} 
		if (sizeof($missing)){
			$html .= implode($missing, "<br>");
		} //else {

			foreach($args as $key=>$value){
				$html .= "<h2>K $key : V $value";
			}
		//}
		//die("HERE");
		$anyFile = false;
		$fileTypes = array("pdf", "odt", "epub");
		foreach($fileTypes as $type){

			$name = "${title}_live.${type}";
			$f = wfFindFile($name);
			$html .= "<hr>$name: ";
			if ($f){
				$html .= " exists";
			}else {
				$html .= " NOT exists";
			}
		}		
		return $html;
	}
}
