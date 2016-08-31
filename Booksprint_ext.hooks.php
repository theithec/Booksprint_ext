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
		//global $wgDefaultSkin;
		//$wgDefaultSkin = "Booksprint_skin"; //getSkinForBookOrDefault($bookSkinPrefix , $bookSkinMode);
		// i'll bet we'll need this again
	}

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		if (method_exists($skin, "getBookname")){
			$bookName = $skin->getBookname();
			if ($bookName !== null){
				$items = $skin->getBookHeadItems();
				foreach($items as $k=>$v){
					$out->addHeadItem($k, $v);
				}
			}
		}

	    return true;
	}

	public static function onParserSetup(Parser $parser){
		$parser->setHook("bookinfo", "BookinfoRenderer::renderTagBookinfo");
		$parser->setHook("booklist", "Booksprint_extHooks::renderTagBooklist");
		$parser->setHook("chapterdoi", "Booksprint_extHooks::renderChapterDoi");
	}

	private static function getAbstractFromTitle($title){
		$article = Article::newFromID($title->getArticleID());
		$content = $article->getContent();
		$start = strpos($content, "ABSTRACT");
		if ($start === false) {
			return "";
		}
		else {
			$start += 10;
		}

		$abs = substr($content, $start);
		$end = strpos($abs, "|");
		if ($end === false) {
			$end = strpos($abs, "}}");
		}
		return trim(substr($abs, 0, $end));
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
			$t = Title::newFromText( $btitle );
			$html .= '<li><a href="' . $t->getLinkUrl() . '">' . str_replace("_", " ", $btitle)  . '</a>' .
				'<br />' .Booksprint_extHooks::getAbstractFromTitle($t)  . '</li>';
		}
		$html .= "</ul>";
		return $html;
	}


	public static function renderChapterDoi( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->disableCache();
		$html = "";
		$rev = isset($_GET['stableid']) ? intval($_GET['stableid']) : null;
		if ($rev !== null){
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				array( 'booksprint_ext_doi4rev' ),                                   // $table
				array( 'doi' ),
				'rev=' . $rev,
				__METHOD__
			);

			foreach( $res as $row ) {
				$html .= "<h3>Chapterdoi:" . $row->doi . " </h3>";
			}
		}
		return $html;
	}


	public static function onLoadExtensionSchemaUpdates (DatabaseUpdater $updater) {
		$updater->addExtensionTable( 'booksprint_ext_alloc_dois',
					__DIR__ . '/sql/booksprint_ext_alloc_dois.sql' );
	    	return true;

	}
}
//global $wgHooks;
//$wgHooks['LoadExtensionSchemaUpdates'][] = "Booksprint_extHooks::onLoadExtensionSchemaUpdates";
