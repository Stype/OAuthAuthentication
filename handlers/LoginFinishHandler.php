<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class LoginFinishHandler implements OAuthLoginHandler {


	public function process( \WebRequest $request, SessionStore $session, $client ) {
		$verifyCode = $request->getVal( 'oauth_verifier', false );
		$recKey = $request->getVal( 'oauth_token', false );

		if ( !$verifyCode || ! $recKey ) {
			throw new Exception( 'oauthauth-failed-handshake' );
		}
wfDebugLog( "OAuthAuth", __METHOD__ . "Got from session: " . $session->get( 'oauthreqtoken' ) );
wfDebugLog( "OAuthAuth", "Session: " . print_r( $_SESSION, true ) );
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

		$exUser = OAuthExternalUser::newFromRemoteId( $identity->sub, $identity->username, wfGetDB( DB_MASTER ) ); #TODO: don't do this, do storage for realz

wfDebugLog( "OAuthAuth", __METHOD__ . " identity: " . print_r( $identity, true ) );
wfDebugLog( "OAuthAuth", __METHOD__ . " ExUser: " . print_r( $exUser, true ) );

		if ( $exUser->attached() ) {
			$status = AuthenticationHandler::doLogin( $exUser, $request );
wfDebugLog( "OAuthAuth", "Status From doLogin: " . print_r( $status, true ) );
			$s = \Status::newGood( array( 'successfulLogin', $status->getValue() ) );
			$s->merge( $status );
		} else {
			$status = AuthenticationHandler::doCreateAndLogin( $exUser, $request );
wfDebugLog( "OAuthAuth", "Status From doCreateAndLogin: " . print_r( $status, true ) );
			$s = \Status::newGood( array( 'successfulCreation', $status->getValue() ) );
			$s->merge( $status );
		}
wfDebugLog( "OAuthAuth", __METHOD__ . " returning Status: " . print_r( $s, true ) );
		return $s;
	}

}
