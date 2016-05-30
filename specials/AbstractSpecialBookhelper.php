<?php
/**
 * Booksprint_ext SpecialPage for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/../BookValidation.php");
require_once(__DIR__."/../validateUser.php");

abstract class AbstractSpecialBookHelper extends SpecialPage {
  public function __construct($name) {
    parent::__construct( $name);
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
    $this->user = $wgUser;
    $out = $this->getOutput();
    $out->addModules('ext.bookmaker');
    $out->addModuleStyles( array(
      'mediawiki.special', 'mediawiki.special.search', 'mediawiki.ui', 'mediawiki.ui.button',
      'mediawiki.ui.input',
    ) );
    $out->setPageTitle( $this->msg( $this->pageTitleKey ) );
    $out->addWikiMsg( $this->pageTitleKey . '-intro' );
    $this->doAction = isset($_REQUEST['action']) and ($_REQUEST['action'] == true);
    $this->handleBookFromDataOrShowForm();
  }

  protected function getForm(){
    return '<form method="POST"><input name="book" class="mw-ui-input mw-ui-input-inline"/><button class="mw-ui-button mw-ui-progressive" type="submit">Buch suchen</button></form>' ;
  }

  private function handleBookFromDataOrShowForm(){
    //var_dump($_REQUEST);
    $out = $this->getOutput();
    $book = isset($_REQUEST['book']) ? str_replace(" ", "_",htmlspecialchars($_REQUEST['book'])) : null;
    if($book == null){
      $this->getOutput()->addHTML( $this->getForm());
      return;
    }else {
      //die("book $book");
      $this->validation = new BookValidation($book);
      if ($this->validation->hasErrors) { 
        $html = "<ul>";
        foreach ($this->validation->results as $k=>$v){
          $html .= '<li><span class="result-key">' . "$k</span> ... "; 
          if(sizeof($v)==0){
            $html .= '<span class="result-value-ok">OK</<span></li>'; 
          } else {
            $html .= '<span class="result-value-err">' . implode(",", $v) . '</<span></li>'; 
          }
        }
        $html .= "</ul>";
        $out->addHTML($html);
        $out->addHTML("<h2>Bitte erst die Fehler beheben</h2>");
        }
        else {
          $this->handleBookData($book);
        }
      }
    }
    function showErrors($result){
    $out = $this->getOutput();
    $out->addHtml("<H2>Fehler sind aufgetreten</h2><ul>");
        foreach($result as $err){
          $out->addHtml("<li>$err</li>");
        }    
        $out->addHtml('</ul>');

  }
    protected abstract function handleBookData($book);

    protected function getGroupName() {
      return 'bookmaker';
    }
  }
