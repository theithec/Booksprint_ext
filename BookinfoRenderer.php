<?php

class BookinfoRenderer{
	public function __construct( $input, array $args, Parser $parser, PPFrame $frame ) {
		$this->html = '<div class="bookinfo">';
		global $wgOut;
		$this->out = $wgOut;
		$this->title = $parser->getTitle();
		// 1.27 bails without:
		$this->out->setTitle($this->title);

		$this->baseTitle = str_replace(" ", "_",$this->title->getBaseText());
		$this->required = array( "abstract", "authors" );
		$this->allowed = array_merge(
			$this->required,
			array( "info", "contributors", "doi", "stand", "version" )
		);
		if ( $input != "" ) {
			$args['abstract'] = $input;
		}
		$this->vals = array();
		foreach ( $args as $k=>$v ){
			if ( ! in_array( $k, $this->allowed ) ) {
				$this->html .=  '<b class="error">Unbekannter Parameter: '. $k. '  </b><br>';
				continue;
			}
			if ( substr( $v, 0, 3 ) !== "{{{" ) {
				if ( in_array( $k, $this->required ) ) {
					unset( $this->required[array_search( $k, $this->required )] );
				}
				$this->vals[$k] = htmlspecialchars( $v );
			}
		}
		foreach ( $this->required as $r ){
			$this->html .= '<b class="error">Angabe fehlt: '. $r .
				'. Bitte die Angaben korrigieren.</b>';
		}

		$this->addInfoTable();
		$this->html .= '<div class="row">';
		$this->addVersionLinks();
		$this->addFileLinks();
		$this->html .= '<div class="large-2 columns">' .
			$this->out->parse('[[' . $this->title . '/_Druckversion|Druckversion]]' ) . ' </div>';
		$this->html .= '</div>';
		$this->html .= '</div>';
	}

	private function infoRow( $name, $val ) {
		if ($name == "Doi"){
			$name = "Doi";
			$val = '<a href="http://doi.org/' . $val . '">' . $val . '<a>';
		}
		return '<tr><td><b>' . $name . '</b></td><td>' . $val . '</td></tr>';
	}

	private function addInfoTable() {
		$this->html .= "<table>";
		$this->html .= '<tr><td colspan="2">' .  $this->vals[array_shift( $this->allowed )] .
			'</td></tr>'; // abstract
		foreach ( $this->allowed as $key ) {
			if ( !isset( $this->vals[$key] ) ){
				continue;
			}

			$this->html .= $this->infoRow( $this->out->msg( $key ), $this->vals[$key] );
		}
		$this->html .= "</table>";
	}

	private function addFileLinks() {
		//files
		$fileTypes = array( "pdf", "odt", "epub" );
		$filelinksHtml = "";
		foreach ( $fileTypes as $type ){
			$name = $this->title;
			$foundSlash = strpos( $name, "/" );
			if ( $foundSlash === false) {
				$name .= "_live";
			} else {
				$name = str_replace("/", "_", $name);
			}
			$name .= ".${type}";
			//$filelinksHtml .= "<option>SEARCH($foundSlash) $name</option>";
			$f = wfFindFile( $name );
			if ( $f ){
				$filelinksHtml .= '<option class="' . $type . '-link" value="' . $f->getCanonicalUrl() . '">' .
					strtoupper( $type ) . '</option>';
			}
		}
		if ( $filelinksHtml != "" ) {
			$this->html .= '<div class="large-4 columns">' .
				'<select id="bookfile-selector" style="width: 80%" onchange="location = this.value;">' .
				'<option value="#">Download</option>' .
				$filelinksHtml . '</select></div>';
		}
	}

	private function addVersionLinks() {

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'categorylinks', 'page' ),                                   // $table
			array( 'cl_from', 'cl_to', 'page_id', 'page_title' ),
			'cl_from=page_id AND cl_to="Buch" AND page_title LIKE "' . $this->baseTitle . '%"',
		       	__METHOD__,
			array( 'ORDER BY' => 'page_title ASC' )
		);
		//echo "<pre>"; die(var_dump($res));
		$versionsHtml = "";
		foreach( $res as $row ) {
			$vtitle =  $row->page_title ;
			//mysqli_real_escape_string
			$sel =$vtitle == str_replace(" ", "_", $this->title ) ? 'selected': '';
			$t = Title::newFromText( $vtitle );
			$txt = str_replace($this->baseTitle, "", $vtitle);
			$txt = $txt == "" ? "Live" : $txt;
			if(strpos($txt, "/") === 0){
				$txt = substr($txt, 1, strlen($txt));
			}
			$versionsHtml .= '<option ' . $sel . ' value="' . $t->getLinkUrl() . '">' .
			       	 $txt . '</option>';
		}

		if ( $versionsHtml != "" ) {
			$this->html .= '<div class="large-6 columns"><label>Version ' .
				'<select id="bookversion-selector" style="width: 80%" onchange="location = this.value;">' .
				$versionsHtml . '</select></label></div>';
		}
	}


	public static function renderTagBookinfo( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->disableCache();
		$r = new BookinfoRenderer( $input, $args, $parser, $frame );
		return $r->html;
	}
}
