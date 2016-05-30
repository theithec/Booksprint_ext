<?php
/**
 * Booksprint_ext SpecialPage for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/AbstractSpecialBookhelper.php");

class SpecialBookPublisher extends AbstractSpecialBookHelper {
  public function __construct() {
    $this->pageTitleKey = "bookmaker-publisher";
    parent::__construct( 'BookPublisher');
  }


  function handleBookData($book){
    $out = $this->getOutput();
    
    if ($this->doAction) {
      $params = new DerivativeRequest(
        $this->getRequest(), // Fallback upon $wgRequest if you can't access context.
        array(
          'action' => 'bmaker',
          'cmd' => 'publish',
          'args' => $book,
          'token' =>  $this->user->getEditToken(), 
        ) ,true  
      );
      $api = new ApiMain( $params,  true );
      $api->execute();
      $data = $api->getResult()->getResultData();
      $status = $data['Result'][0];
      $json_result = json_decode($status);
      $errs = $json_result->errors;
      if (sizeof($errs) > 0 ){
        $this->showErrors();
              }
      else {
        $out->addHtml("<H2>Vorgang läuft</h2>");
        $out->addHtml('<div id="result" data-key="' .$json_result->result . '"></div>');
        // from now js does the job
      }  
    }

    else {
      $html = '<form method="POST"><input name="book" type="hidden" value="'. $book . '">' .
        '<h2>' . $book . '</h2>' .
        '<input name="action" type="hidden" value="1">'.
        '<button class="button" id="publishbookbutton" type="submit">Buch publizieren</button></form>';
      $out->addHTML($html);
    }

  }
  


  protected function getGroupName() {
    return 'bookmaker';
  }
}
