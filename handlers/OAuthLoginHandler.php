<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

interface OAuthLoginHandler {

	public function process( \WebRequest $request, SessionStore $session, $client );

}
