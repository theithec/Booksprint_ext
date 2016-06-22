<?php
/**
 * Booksprint_ext SpecialPage for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/../BookValidation.php");
require_once(__DIR__."/../validateUser.php");
require_once(__DIR__."/../debug.php");


abstract class AbstractSpecialBookHelper extends SpecialPage {
    public function __construct($name) {
        parent::__construct( $name);
        //$out->setPageTitle( $this->msg( 'viewdeletedpage' ) );
    }

    /**
     * Show the page to the user
     *
     * @param string $sub The subpage string argument (if any).
     *  [[Special:Booksprint_ext/subpage]].
     */

    public function execute( $sub ) {
        global $wgUser;
        validateUserInBookhelperGroup($wgUser);
        //debuglog("ALL ABOUT THE BASE (class)");
        $this->user = $wgUser;
        $out = $this->getOutput();
        $out->addModules('ext.booksprint_ext');
        $out->addModuleStyles( array(
            'mediawiki.special', 'mediawiki.special.search', 'mediawiki.ui', 'mediawiki.ui.button',
            'mediawiki.ui.input',
        ) );
        $key = strtolower($this->getName());
        $out->setPageTitle( $this->msg( $key . '-title' ) );
        $out->addWikiMsg( $key . '-intro' );
        $this->doAction = isset($_REQUEST['action']) and ($_REQUEST['action'] == true);
        $this->handleBookFromDataOrShowForm();
    }

    protected function getForm(){
        $out = $this->getOutput();
        return '<form method="POST"><input name="book" class="mw-ui-input mw-ui-input-inline"/><button class="mw-ui-button mw-ui-progressive" type="submit">' . $out->msg("booksprint_ext-validate_book") . '</button></form>' ;
    }

    private function handleBookFromDataOrShowForm(){
        $out = $this->getOutput();
        $book = isset($_REQUEST['book']) ? str_replace(" ", "_",htmlspecialchars($_REQUEST['book'])) : null;
        if($book == null){
            $this->getOutput()->addHTML( $this->getForm());
            return;
        }else {
            $this->validation = new BookValidation($book);
            if ($this->validation->hasErrors) {
                $html = "<ul>";
                foreach ($this->validation->results as $k=>$v){
                    $html .= '<li><span class="result-key">' . $this->msg("booksprint_ext-" . $k) . "</span> ... ";
                    if(sizeof($v)==0){
                        $html .= '<span class="result-value-ok success">OK</<span></li>';
                    } else {
                        $html .= '<span class="result-value-err error">' . implode(",", $v) . '</<span></li>';
                    }
                }
                $html .= "</ul>";
                $out->addHTML($html);
                $out->addHTML("<h2>Bitte erst die Fehler beheben</h2>");
            }
            else {
                $this->version = $this->validation->version;
                $this->handleBookData($book);
                $out->addHTML($out->parse( '<div class="booklink">[[' . $_POST['book'] . '|' . $this->msg('booksprint_ext-booklink'). ']]</div>'));
            }
        }
    }

    function showErrors($result){
        $out = $this->getOutput();
        $out->addHtml("<H2>Fehler sind aufgetreten</h2><ul>");
        foreach($result as $err){
            $out->addHtml('<li class="error">' . $err . '</li>');
            debuglog("RESULT ERROR : $err");
        }
        $out->addHtml('</ul>');
    }

    protected abstract function handleBookData($book);

    protected function getGroupName() {
        return 'booksprint_ext';
    }
}
