<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class AuthenticationHandler {

	public static function doCreateAndLogin( OAuthExternalUser $exUser ) {
		global $wgAuth;

		wfDebugLog( "OAuthAuth", "Doing create for user " . $exUser->getName() );
		$u = \User::newFromName( $exUser->getName(), 'creatable' );

		if ( !is_object( $u ) ) {
			return Status::newFatal( 'oauthauth-create-noname' );
		} elseif ( 0 != $u->idForName() ) {
			return \Status::newFatal( 'oauthauth-create-userexists' );
		}

		# TODO: Does this need to call $wgAuth->addUser? This could potentially coexist
		# with another auth plugin.

		$status = $u->addToDatabase();
		if ( !$status->isOK() ) {
			return $status;
		}

		$exUser->addToDatabase();

		/* TODO: Set email, realname, and language, once we can get them via /identify
		$u->setEmail( $exUser->getEmail() );
		$u->setRealName( $exUser->getRealName() );
		$u->setOption( 'language', $exUser->getLanguage() );
		*/

		$u->setToken();
		\DeferredUpdates::addUpdate( new \SiteStatsUpdate( 0, 0, 0, 0, 1 ) );
		$u->addWatch( $u->getUserPage(), \WatchedItem::IGNORE_USER_RIGHTS );

		$u->saveSettings();
		$u->setCookies();
		wfRunHooks( 'AddNewAccount', array( $u, false ) );
		$u->addNewUserLogEntry( 'create' );

		return \Status::newGood( $u );

	}


	public static function doLogin( OAuthExternalUser $exUser, \WebRequest $request ) {
		global $wgSecureLogin, $wgCookieSecure;

		$u = \User::newFromId( $exUser->getLocalId() );

		if ( !is_object( $u ) ) {
			return Status::newFatal( 'oauthauth-login-noname' );
		} elseif ( $u->isAnon() ) {
			return \Status::newFatal( 'oauthauth-login-usernotexists' );
		}

		$u->invalidateCache();

		if ( !$wgSecureLogin ) {
			$u->setCookies( $request, null );
		} elseif ( $u->requiresHTTPS() ) {
			$u->setCookies( $request, true );
		} else {
			$u->setCookies( $request, false );
			$wgCookieSecure = false;
		}

		wfResetSessionID();

		return \Status::newGood( $u );
	}
}
