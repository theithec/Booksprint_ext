<?php
/**
 * Booksprint_ext SpecialPage for Booksprint_ext extension
 *
 * @file
 * @ingroup Extensions
 */

require_once(__DIR__."/../validateUser.php");
class SpecialBookCreator extends SpecialPage {
  public function __construct() {
    parent::__construct( 'BookCreator' );
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
    $out = $this->getOutput();
    $out->addModuleStyles( array(
			'mediawiki.special', 'mediawiki.special.search', 'mediawiki.ui', 'mediawiki.ui.button',
			'mediawiki.ui.input',
		) );

    $out->setPageTitle( $this->msg( 'bookmaker-bookmaker' ) );

    //$out->addHelpLink( 'How to become a MediaWiki hacker' );

      $formDescriptor = array(
        'booktitle' => array(
          'section' => 'bookdata/main',
          'label-message' => 'book-title',
          'type' => 'text',
          'required' => true,

        ),
        'bookabstract' => array(
          'section' => 'bookdata/main',
          'label-message' => 'book-description',
          'type' => 'textarea',
          'rows' => 8,
          'cols' => 70,
          'required' => true,
        ),
        'book_show_authors_or_editors' => array(
          'section' => 'bookdata/main',
          'type' => 'radio',
          'label' => 'radio',
          'label-message' => 'book-use-editors-or-authors',
          'options' => array( # The options available within the checkboxes (displayed => value)
            'Autoren' => 'authors',
            'Herausgeber' => 'editors',
          ),
          'default' => 'authors', # The option selected by default (identified by value)
          'required' => true,
        ),
        'book_authors_or_editors' => array( // Herausgeber
          'section' => 'bookdata/main',
          'label-message' => 'book-editors-or-authors',
          'type' => 'text',
          'required' => true,
        ),

        'bookcontributors' => array( // Herausgeber
          'section' => 'bookdata/main',
          'label-message' => 'book-contributors',
          'type' => 'text',
        ),

      );

      for ($i=0; $i<20; $i++){
        $formDescriptor["c".$i] = array(
          'section' => 'bookdata/toc',
          'class' => 'HTMLTextField',
          'label' => $i+1,
        );
      } 

      $htmlForm = new HTMLForm( $formDescriptor, $this->getContext(), 'Book' );

      $htmlForm->setSubmitText( 'submit' );
      $htmlForm->setSubmitCallback( array( 'SpecialBookCreator', 'processFormInput' ) );

    if (sizeof($_POST)==0){
      $out->addWikiMsg( 'bookmaker-bookmaker-intro' );
    }
      $htmlForm->show();
  }

  function processFormInput( $formData, $form ) {
    //var_dump($form->getContext()); die();
    $context = $form->getContext();
    $info = array(
      'abstract' => $formData['bookabstract'],
      'kontributoren' => $formData['bookcontributors'],
      'autoren' => $formData['book_authors_or_editors'],
      'stand' => date("d.m.Y.")
    );
    $pages = array();
    $i = 0;
    while(array_key_exists('c'.$i, $formData) && ($c=$formData['c'.$i])!==""){
      $d = $formData['c'. $i];
      $pages[] = array('title'=>$d, 'body' => '\nHier entsteht ' . $d);  
      $i++;
    }
    //var_dump($formData);
    //die();
    //if ($formData['book_authors_or_editors'] == 'at
    $bookdata = array(
      'title' => $formData['booktitle'],
      'info' => $info,
      'pages' => $pages
    );
    $json_data = json_encode($bookdata);
    //var_dump($form);
    $request = $context->getRequest();
    $params = new DerivativeRequest(
      $request, // Fallback upon $wgRequest if you can't access context.
      array(
        'action' => 'bmaker',
        'cmd' => 'create',
        'args' => "'$json_data'",
        'token' =>  $context->getUser()->getEditToken(), 
      ) ,true  
    );
    $api = new ApiMain( $params,  true );
    $api->execute();
    $data = $api->getResult()->getResultData();
    $result = json_decode($data['Result'][0]);
    $errors = $result->errors;
    $out = $context->getOutput();
    var_dump($data);
    if (sizeof($errors)>0){

      $out->addWikiMsg( 'bookmaker-bookmaker-errors' );
      $out->addHTML( '<div class="errorbox"<ul>');
      foreach($errors as $err){
        $out->addHTML( '<li class="error">' . $err . '</li>');
      }
      $out->addHTML( "</ul></div>");
    } else {
      $out->addHTML('<div class="success-box"><h2 class="success">Das Buch wurde erstellt.</h2>');
      global $wgScriptPath;

      $out->addHTML('Zum Buch: <b><a href="' . $wgScriptPath . '/' . $formData['booktitle']. '">' . $formData['booktitle'] . '</a></b></div><style>#bodyContent Form { display:none !important;}</style>');

    }
    
  }
}
