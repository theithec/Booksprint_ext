<?php
/**
 * Booksprint_ext SpecialPage for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/AbstractSpecialBookhelper.php");

class SpecialBookVersionizer extends AbstractSpecialBookHelper {
    public function __construct() {
        parent::__construct( 'BookVersionizer');
    }

    function handleBookData($book){

        $out = $this->getOutput();
        $book = $this->validation->bookTitleStr;
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
		//echo "DATA<br>"; var_dump($data); echo "<br>";

            $errs = array();
            if ( !array_key_exists('Result', $data)
                || sizeof($data['Result']) == 0
            ){
                $errs[] = $out->msg("Bad result");
                debuglog("BAD DATA: ");
                debuglog($data);
            }else {
                $result =  $data['Result'];
                $status = $result[0];
		//die(var_dump($status));
		//array(1) { [0]=> string(76) "{"result": "FAILURE", "errors": ["Page already exists: VIVO-Handbuch/1.2a"]}" } 1string(76) "{"result": "FAILURE", "errors": ["Page already exists: VIVO-Handbuch/1.2a"]}"
                //$json_result = json_decode($status);
                $json_result = json_decode($status);
		//echo "JSONRESULT<br>"; var_dump($json_result);echo "<br>";
                $errs = $json_result->errors;
                if (sizeof($errs) > 0 ){
                    $this->showErrors($errs);
                    debuglog($data);
                }
                else {
                    $out->addHtml('<div id="result" data-key="' .$json_result->result . '"><H2>Vorgang läuft</h2></div>');
                    // from now js takes over
                }
            }
        }

        else { // no action
            $html = '<form method="POST"><input name="book" type="hidden" value="'. $book . "/" . $this->version . '">' .
                '<h2>' . $book . '</h2>' .
                '<input name="action" type="hidden" value="1">'.
                '<button class="button" id="publishbookbutton" type="submit">Version "' . $this->version . '" erstellen </button></form>';
            $out->addHTML($html);
        }

    }

    protected function getGroupName() {
        return 'booksprint_ext';
    }
}
