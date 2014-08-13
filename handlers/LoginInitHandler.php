<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class LoginInitHandler implements OAuthLoginHandler {


	public function process( \WebRequest $request, SessionStore $session, $client ) {
		// Step 1 - Get a request token
		list( $redir, $requestToken ) = $client->initiate();
		$session->set( 'oauthreqtoken', "{$requestToken->key}:{$requestToken->secret}" );

		// Step 2 - Have the user authorize your app.
		$request->response()->header( "Location: $redir", true );
	}

}
