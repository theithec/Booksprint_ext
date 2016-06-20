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

	private static function infoRow($name, $val) {
		return '<tr><td><b>' . $name . '</b></td><td>' . $val . '</td><tr>';
	}
	public static function renderTagBookinfo($input, array $args, Parser $parser, PPFrame $frame){
		$html = "";
		global $wgOut;
		$title = $parser->getTitle();
		$baseTitle = $title->getBaseText();
		//var_dump($title);
		//die($title->getBaseText());
		$required = array( "abstract", "authors");
		// allowed allso defines the order
		$allowed = array_merge($required, array("info", "contributors"));
		//die(var_dump($allowed));
		$argsKeys = array_keys($args);
		if($input != ""){
			$args['abstract'] = $input;
		}
		//die(var_dump($args));
		$vals = array();
		foreach($args as $k=>$v){
			if(! in_array($k, $allowed)){
				$html .=  '<b class="error">Unbekannter Parameter: '. $k. '  </b><br>';
				continue;
			}
			//$html .=  "<br>RE NOW" . implode(", ", $required) . "<br>";
			//
			if(substr($v, 0, 3) !== "{{{"){
				if (in_array($k, $required)){
					unset($required[array_search($k, $required)]);
					//die(var_dump($required));
				}
				$vals[$k] = htmlspecialchars($v);
			}

		}

		foreach ($required as $r){
			$html .= '<b class="error">Angabe fehlt: '. $r. 
				'.Bitte die Angaben korrigieren.</b>';
		} 

		//die(var_dump($allowed));

		$html .= "<table>";	 
		$html .= '<tr><td colspan="2">' .  $vals[array_shift($allowed)] . '</tr></td>'; // abstract
		foreach($allowed as $key){
			if (!isset($vals[$key])){
				continue;
			}

			$html .= Booksprint_extHooks::infoRow($wgOut->msg($key), $vals[$key]);
		}
		$html .= "</table>";	 

		$anyFile = false;
		$fileTypes = array("pdf", "odt", "epub");
		$filelinksHtml = "";
		foreach($fileTypes as $type){

			$name = "${title}_live.${type}";
			$f = wfFindFile($name);
			if ($f){
				$filelinksHtml .= '<a class="' . $type . '-link" href="' . $f->getCanonicalUrl() . '">' .
					$name . '</a> ';
			}else {
				//	$html .= "$name missing";
			}
		}		
		if($filelinksHtml != ""){
			$html .= '<div class="bookfilelinks">' . $filelinksHtml . '</a>';
		}

		//versions
		//
		$params = new DerivativeRequest(
			$wgOut->getRequest(), // Fallback upon $wgRequest if you can't access context.
			array(
				'action' => 'query',
				'generator' => 'allpages',
				'gaplimit' => 500,
				'gapfrom' => $baseTitle,
				'gapto' => "$baseTitle/ZZZ",
				'prop' => 'categories|info',
				'inprop' => 'url',
				'continue' => ''
				//categories'
				//api.php?action=query&generator=allpages&gaplimit=3&gapfrom=Ba&prop=links|categories
			)  
		);
		$api = new ApiMain( $params );
		$api->execute();
		$res = $api->getResult()->getResultData();
		$pages = $res['query']['pages'];
		//die(var_dump($res));
		$versionsHtml = "";
		foreach($pages as $id => $page){
			if (	!isset($page['title']) ){   // dunno why, some have none
				continue;
			}
			$isBook = false;
			if (! isset($page['categories']) ){
				continue;
			}
			$cats = $page['categories'];
			foreach($cats as $cat){
				if (! is_array($cat)) {
					continue;
				}
				if(strpos( $cat['title'],":Buch") !== false){
					$isBook = true;
					break;
				}	
			}
			if ($isBook ){
				$sel = $page['title'] == $title? 'selected': '';
				$versionsHtml .= '<option ' . $sel . ' value="' . $page['fullurl'] . '">' .
					$page['title'] . '</option>';
			}

		}
		if ($versionsHtml != ""){
			$html .= '<div class="row"><div class="large-6 columns"><label>Version ' .
				'<select id="bookversion-selector" style="width: 80%" onchange="location = this.value;">' .
				$versionsHtml . '</select></label></div></div>';
		}
		return $html;
	}
}
