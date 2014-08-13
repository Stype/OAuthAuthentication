<?php
namespace MediaWiki\Extensions\OAuthAuthentication;

class Hooks {

	public static function onPersonalUrls( &$personal_urls, &$title ) {
		global $wgUser, $wgRequest;
		if ( $wgUser->getID() == 0 ) {
			$query = array();
			$query['returnto'] = $title->getPrefixedText();
			$returntoquery = $wgRequest->getValues();
			unset( $returntoquery['title'] );
			unset( $returntoquery['returnto'] );
			unset( $returntoquery['returntoquery'] );
			$query['returntoquery'] = wfArrayToCgi( $returntoquery );
			$personal_urls['login']['href'] = \SpecialPage::getTitleFor( 'OAuthLogin', 'init' )->getFullURL( $query );
		}
		return true;
	}

	public static function onPostLoginRedirect( &$returnTo, &$returnToQuery, &$type ) {
wfDebugLog( 'OAUTHAUTH', " here" );
		global $wgRequest;
		$session = new PhpSessionStore( $wgRequest );

		$title = $session->get( 'oauth-init-returnto' );
		$query = $session->get( 'oauth-init-returntoquery' );

		if ( $title ) {
			$returnTo = $title;
		}

		if ( $query ) {
			$returnToQuery = $query;
		}
	}

	public static function onLoadExtensionSchemaUpdates( $updater = null ) {
		$updater->addExtensionTable( 'oauthauth_user', __DIR__ . '../store/oauthauth.sql' );
	}

	public static function onGetPreferences( $user, &$preferences ) {
		global $wgRequirePasswordforEmailChange;

		$resetlink = \Linker::link(
			\SpecialPage::getTitleFor( 'PasswordReset' ),
			wfMessage( 'passwordreset' )->escaped(),
			array(),
			array( 'returnto' => \SpecialPage::getTitleFor( 'Preferences' ) )
		);
wfDebugLog( "PrefsHook", print_r( $preferences, true ) );
		if ( empty( $user->mPassword ) && empty( $user->mNewpassword ) ) {

			if ( $user->isEmailConfirmed() ) {
				$preferences['password'] = array(
					'section' => 'personal/info',
					'type' => 'info',
					'raw' => true,
					'default' => $resetlink,
					'label-message' => 'yourpassword',
				);
			} else {
				unset( $preferences['password'] );
			}

			if ( $wgRequirePasswordforEmailChange ) {
				$preferences['emailaddress'] = array(
					'type' => 'info',
					'raw' => 1,
					'default' => wfMessage( 'oauthauth-set-email' )->escaped(),
					'section' => 'personal/email',
					'label-message' => 'youremail',
					'cssclass' => 'mw-email-none',
				);
			}

		} else {
			$preferences['resetpassword'] = array(
				'section' => 'personal/info',
				'type' => 'info',
				'raw' => true,
				'default' => $resetlink,
				'label-message' => null,
			);
		}
	}

}
