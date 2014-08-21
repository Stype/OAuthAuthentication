<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class LoginFinishHandler implements OAuthLoginHandler {

	public function process( \WebRequest $request, SessionStore $session, $client ) {
		$verifyCode = $request->getVal( 'oauth_verifier', false );
		$recKey = $request->getVal( 'oauth_token', false );

		if ( !$verifyCode || ! $recKey ) {
			throw new Exception( 'oauthauth-failed-handshake' );
		}

		list( $requestKey, $requestSecret ) = explode( ':', $session->get( 'oauthreqtoken' ) );
		$requestToken = new \OAuthToken( $requestKey, $requestSecret );

		$session->delete( 'oauthreqtoken' );

		//check for csrf
		if ( $requestKey !== $recKey ) {
			throw new Exception( "oauthauth-csrf-detected" );
		}

		// Step 3 - Get access token
		$accessToken = $client->complete( $requestToken,  $verifyCode );

		// Get Identity
		$identity = $client->identify( $accessToken );

		if ( !Policy::checkWhitelists( $identity ) ) {
			return \Status::newFatal( 'oauthauth-nologin-policy' );
		}

		$exUser = OAuthExternalUser::newFromRemoteId(
			$identity->sub,
			$identity->username,
			wfGetDB( DB_MASTER )  #TODO: don't do this
		);

		if ( $exUser->attached() ) {
			$status = AuthenticationHandler::doLogin( $exUser, $request );
			$s = \Status::newGood( array( 'successfulLogin', $status->getValue() ) );
			$s->merge( $status );
		} else {
			$status = AuthenticationHandler::doCreateAndLogin( $exUser, $request );
			$s = \Status::newGood( array( 'successfulCreation', $status->getValue() ) );
			$s->merge( $status );
		}

		wfDebugLog( "OAuthAuth", __METHOD__ . " returning Status: " . (int) $s->isGood() );
		return $s;
	}

}
