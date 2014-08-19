<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class AuthenticationHandler {

	public static function doCreateAndLogin( OAuthExternalUser $exUser ) {
		global $wgAuth, $wgOAuthAuthenticationAccountUsurpation;
wfDebugLog( "OAuthAuth", __METHOD__ . " Running" );
		wfDebugLog( "OAuthAuth", "Doing create for user " . $exUser->getName() );
		$u = \User::newFromName( $exUser->getName(), 'creatable' );
wfDebugLog( "OAuthAuth", __METHOD__ . " User is: " . print_r( $u, true ) );
		if ( !is_object( $u ) ) {
wfDebugLog( "OAuthAuth", __METHOD__ . ": Bad User" );
			return Status::newFatal( 'oauthauth-create-noname' );
		} elseif ( 0 !== $u->idForName() ) {
wfDebugLog( "OAuthAuth", __METHOD__ . ": User exists and no usurpation" );
			if ( !$wgOAuthAuthenticationAccountUsurpation ) {
				return \Status::newFatal( 'oauthauth-create-userexists' );
			}
			$exUser->setLocalId( $u->idForName() );
		} else {
wfDebugLog( "OAuthAuth", __METHOD__ . ": Creating user" );
			# TODO: Does this need to call $wgAuth->addUser? This could potentially coexist
			# with another auth plugin.

			$status = $u->addToDatabase();
			if ( !$status->isOK() ) {
				return $status;
			}

			/* TODO: Set email, realname, and language, once we can get them via /identify
			$u->setEmail( $exUser->getEmail() );
			$u->setRealName( $exUser->getRealName() );
			$u->setOption( 'language', $exUser->getLanguage() );
			*/

			$u->setToken();
			\DeferredUpdates::addUpdate( new \SiteStatsUpdate( 0, 0, 0, 0, 1 ) );
			$u->addWatch( $u->getUserPage(), \WatchedItem::IGNORE_USER_RIGHTS );
			$u->saveSettings();

			wfRunHooks( 'AddNewAccount', array( $u, false ) );
		}

		$exUser->addToDatabase( wfGetDB( DB_MASTER ) ); //TODO: di

		$u->setCookies();
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
