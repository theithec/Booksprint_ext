<?php
/**
 * Bookmaker SpecialPage for Bookmaker extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/AbstractSpecialBookhelper.php");

class SpecialBookVersionizer extends AbstractSpecialBookHelper {
  public function __construct() {
    $this->pageTitleKey = "bookmaker-versionizer";
    parent::__construct( 'BookVersionizer');
  }


  function handleBookData($book){
    $out = $this->getOutput();
    $book = $this->validation->bookTitleStr;
    $this->version = $this->validation->version;
    if ($this->version == null){
      $this->showErrors(array("Version fehlt"));
      return;
    }

    if ($this->doAction) {
      $params = new DerivativeRequest(
        $this->getRequest(), // Fallback upon $wgRequest if you can't access context.
        array(
          'action' => 'bmaker',
          'cmd' => 'versionize',
          'args' => "$book -v " . $this->version,
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
        $this->showErrors($errs);
              }
      else {
        $out->addHtml("<H2>Vorgang läuft</h2>");
        $out->addHtml('<div id="result" data-key="' .$json_result->result . '"></div>');
        // from now js does the job
      }  
    }

    else {
      $html = '<form method="POST"><input name="book" type="hidden" value="'. $book . "/" . $this->version . '">' .
        '<h2>' . $book . '</h2>' .
        '<input name="action" type="hidden" value="1">'.
        '<button class="button" id="publishbookbutton" type="submit">Version "' . $this->version . '" erstellen </button></form>';
      $out->addHTML($html);
    }

  }
  
  

  protected function getGroupName() {
    return 'bookmaker';
  }
}
