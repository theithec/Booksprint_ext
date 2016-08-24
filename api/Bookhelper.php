<?php

require_once(__DIR__."/../validateUser.php");
require_once(__DIR__."/../debug.php");

class Booksprint_extAPI extends APIBase{

    public function execute() {
        validateUserInBookhelperGroup($this->getMain()->getUser());
        global $wgBookhelperCommand;
        $cmd = $this->getMain()->getVal( 'cmd' );
        $args = $this->getMain()->getVal( 'args' );
        $pre_cmd_args = "";
        if ( $cmd==="publish" || $cmd==="versionize"){
            $pre_cmd_args = "--queued";

        }
        $full_cmd = "$wgBookhelperCommand $pre_cmd_args $cmd $args";
	//echo "$full_cmd <br>";
        $this->getResult()->addValue(null, "Full Command", $full_cmd);
		exec($full_cmd, $result, $return_var);
		debuglog($full_cmd, $result, $return_var);
		//if ($return_var == 0){
		if (sizeof($result) == 0){
			$this->getResult()->addValue(null, "Result",
				[json_encode([
					"result"=>"FAILURE",
					"errors"=>["SOMETHING BAD"]
				])]);

		}else {

			$this->getResult()->addValue(null, "Result", $result);
		}

		$this->getResult()->addValue(null, "Return Var", $return_var);
		/*} else {
			$this->getResult()->addValue(null, "Result",
				["result" => "FAILED",
				 "errors" => json_encode(['errors'=>["ERROR"]])]);
			$this->getResult()->addValue(null, "Return Var", -1);
			}*/
		//die(var_dump($this->getResult()));
		//echo $return_var;


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
