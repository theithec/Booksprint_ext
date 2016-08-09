<?php

require_once(__DIR__."/../validateUser.php");
require_once(__DIR__."/../debug.php");

class SetDoiAPI extends APIBase{

    public function execute() {
        validateUserInBookhelperGroup($this->getMain()->getUser());
        $doi = $this->getMain()->getVal( 'doi' );
        $rev = $this->getMain()->getVal( 'rev' );
	$dbw = wfGetDB( DB_MASTER );
	$dbw->insert( "booksprint_ext_doi4rev", array( "doi" => $doi, "rev" => $rev ) );
        $this->getResult()->addValue(null, "Result", "1");
    }


    // Description
    public function getDescription() {
        return 'Sets a DOI for an stable Reversion"';
    }
    public function needsToken() {
        return 'csrf';
    }

    // Face parameter.
    public function getAllowedParams() {
        return array_merge( parent::getAllowedParams(), array(
            'rev' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ),
            'doi' => array (
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
                'doi' => 'The doi',
                'rev' => 'The reversion',
            )
        );
    }

    // Get examples
    public function getExamplesMessages() {
        return array(
            // apisampleoutput-book-1 is the key to an i18n message explaining the example
            'api.php?action=setdoi&doi=xyz&rev=123'
            => 'ok');
    }

}
