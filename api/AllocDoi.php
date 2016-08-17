<?php

require_once ( __DIR__."/../validateUser.php" );
require_once ( __DIR__."/../debug.php" );

class AllocDoiAPI extends APIBase{

	public function execute() {
		validateUserInBookhelperGroup( $this->getMain()->getUser() );
		$doi = $this->getMain()->getVal( 'doi' );
		$cmd = $this->getMain()->getVal( 'cmd' );
		if ( !in_array( $cmd,  [ "set", "del" ] ) ) {
			throw new MWException( 'Unknown command' );
		}
		$dbw = wfGetDB( DB_MASTER );
		$res = "-1";
		if ( $cmd == "set" ) {
			// dbw->insert does not return a result,
			// so we cant't use options:IGNORE
			// if we need to know what happend
			// $i = $dbw->insert( "booksprint_ext_alloc_dois", array( "doi" => $doi ), \
			// "DatabaseBase::insert", [ 'IGNORE' ] );

			try {
				$dbw->insert( "booksprint_ext_alloc_dois", [ "doi" => $doi ] );
				$res = 0;
			}
			catch ( DBQueryError  $e ) {
				// assuming this means the doi already exists
			}
		} elseif ( $cmd == "del" ){
			$dbw->delete( "booksprint_ext_alloc_dois", [ "doi" => $doi ] );
		}
		$this->getResult()->addValue( null, "Result", $res );
	}

	// Description
	public function getDescription() {
		return 'Reserves a doi (or deletes one)"';
	}
	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return array_merge( parent::getAllowedParams(),
			[
				'cmd'=> [
					ApiBase::PARAM_TYPE=> 'string',
					ApiBase::PARAM_REQUIRED=> true
				],
				'doi'=> [
					ApiBase::PARAM_TYPE=> 'string',
					ApiBase::PARAM_REQUIRED=> true,
					ApiBase::PARAM_ISMULTI=> true
				],
				'continue'=> [
					ApiBase::PARAM_TYPE=> 'string',
					ApiBase::PARAM_REQUIRED=> False
				]
			]
	       	);
	}

	// Describe the parameter
	public function getParamDescription() {
		return array_merge( parent::getParamDescription(),
			[
				'doi' => 'The doi',
				'rev' => 'The reversion',
			]
		);
	}

	// Get examples
	public function getExamplesMessages() {
		return [
			'api.php?action=allocdoi&doi=xyz3' => 'ok'
		];
	}

}
