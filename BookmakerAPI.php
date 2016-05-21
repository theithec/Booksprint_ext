<?php 

require_once(__DIR__."/validateUser.php");
//die(__DIR__."/../validateUser.php");

class BookmakerAPI extends APIBase{

  public function execute() {
    validateUserInBookhelperGroup($this->getMain()->getUser()); 
    //var_dump($this->getMain()->getRequest()->getUser());
    //    die($wgPublishCommand);
    global $wgBookhelperCommand;
    $cmd = $this->getMain()->getVal( 'cmd' );
    $args = $this->getMain()->getVal( 'args' );
    $pre_cmd_args = "";
    if ( $cmd==="publish" ){
      $pre_cmd_args = "--queued";

    }
    $full_cmd = "$wgBookhelperCommand $pre_cmd_args $cmd $args";
    $this->getResult()->addValue(null, "Full Command", $full_cmd);

    exec($full_cmd, $result, $return_var);
    echo "<hr>";
    var_dump($result);
    echo ("<hr> $return_var</hr>");
    
    $this->getResult()->addValue(null, "Result", $result);
    $this->getResult()->addValue(null, "Return Var", $return_var);
  }


  // Description
  public function getDescription() {
    return 'Run actions for "books"';
  }
  public function needsToken() {
    return 'csrf';
  }

  // Face parameter.
  public function getAllowedParams() {
    return array_merge( parent::getAllowedParams(), array(
      'cmd' => array (
        ApiBase::PARAM_TYPE => 'string',
        ApiBase::PARAM_REQUIRED => true,
      ),
      'args' => array (
        ApiBase::PARAM_TYPE => 'string',
        ApiBase::PARAM_REQUIRED => true,
        ApiBase::PARAM_ISMULTI => true
      ),
    ));
  }

  // Describe the parameter
  public function getParamDescription() {
    return array_merge( parent::getParamDescription(),
      array(
        'cmd' => 'The (@see: bookhelper-)command',
        'args' => 'The arguments for the command',
      )
    );
  }

  // Get examples
  public function getExamplesMessages() {
    return array(
      // apisampleoutput-book-1 is the key to an i18n message explaining the example
      'api.php?action=bhelper&cmd=publish&args=Beispielbuch'
      => '{"result": key of the queued task or cmd output, "return var": return var from  queueing the task from the command');
  }

}
