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
		$parser->setHook("bookinfo", "BookinfoRenderer::renderTagBookinfo");
		$parser->setHook("booklist", "Booksprint_extHooks::renderTagBooklist");
	}


	public static function renderTagBooklist( $input, array $args, Parser $parser, PPFrame $frame ) {


		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'categorylinks', 'page' ),                                   // $table
			array( 'cl_from', 'cl_to', 'page_id', 'page_title' ),
			'cl_from=page_id AND cl_to="Buch"  AND ' .
			'cl_from NOT IN (SELECT cl_from FROM categorylinks where cl_to="Versteckt")',
		       	__METHOD__,
			array( 'ORDER BY' => 'page_title ASC' )
		);
		$html = "<ul>";
		foreach( $res as $row ) {
			$btitle =  $row->page_title ;
			if ( strpos($btitle, "/") !== false){
				continue;
			}
			//mysqli_real_escape_string
			//$sel =$vtitle == str_replace(" ", "_", $this->title ) ? 'selected': '';
			$t = Title::newFromText( $btitle );
			//$txt = str_replace($this->baseTitle, "", $vtitle);
			//$txt = $txt == "" ? "Live" : $txt;
			if(strpos($txt, "/") === 0){
				$txt = substr($txt, 1, strlen($txt));
			}
			$html .= '<li><a href="' . $t->getLinkUrl() . '">' . $btitle . '</a></li>';
		}
		$html .= "</ul>";
		return $html;
	}


}
