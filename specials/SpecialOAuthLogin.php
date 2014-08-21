<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class SpecialOAuthLogin extends \UnlistedSpecialPage {

	function __construct() {
		parent::__construct( 'OAuthLogin' );
	}


	public function execute( $subpage ) {
		global $wgUser;
		$request = $this->getRequest();

		$this->setHeaders();

		if ( !$this->getUser()->isAnon() ) {
			throw new \ErrorPageError( 'oauthauth-error', 'oauthauth-already-logged-in' );
		}

		$handler = false;
		$session = new PhpSessionStore( $request );

		switch ( trim( $subpage ) ) {
			case 'init':
				$handler = new LoginInitHandler();

				// Keep around returnto/returntoquery and set with PostLoginRedirect hook
				$session->set(
					'oauth-init-returnto',
					$request->getVal( 'returnto', 'Main_Page' )
				);
				$session->set(
					'oauth-init-returntoquery',
					$request->getVal( 'returntoquery' )
				);
				break;
			case 'finish':
				$handler = new LoginFinishHandler();
				break;
			default:
				throw new \ErrorPageError( 'oauthauth-error', 'oauthauth-invalid-subpage' );
		}

		if ( $handler ) {
			list( $config, $cmrToken ) = Config::getDefaultConfigAndToken();
			$client = new \MWOAuthClient( $config, $cmrToken );
			try {
				$status = $handler->process( $this->getRequest(), $session, $client );
			} catch ( Exception $e ) {
				throw new \ErrorPageError( 'oauthauth-error', $e->getMessage() );
			}

			if ( !$status->isGood() ) {
				throw new \ErrorPageError( 'oauthauth-error', $status->getMessage() );
			}

			list( $method, $u ) = $status->getValue();

			$this->getContext()->setUser( $u );
			$wgUser = $u;

			$lp = new \LoginForm();

			// Call LoginForm::successfulCreation() on create, or successfulLogin()
			$lp->$method();
		}
	}

}
