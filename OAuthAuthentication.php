<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "OAuth extension\n";
	exit( 1 ) ;
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'OAuthAuthentication',
	'descriptionmsg' => 'mwoauth-desc',
	'author'         => array( 'CSteipp' ),
	'url'            => 'https://www.mediawiki.org/wiki/Extension:OAuthAuthentication',
);

/**
 * Must be configured in LocalSettings.php!
 * The OAuth special page on the wiki. Passing the title as a parameter
 * is usually more reliable E.g., http://en.wikipedia.org/w/index.php?title=Special:OAuth
 */
$wgOAuthAuthenticationUrl = null;

/**
 * Must be configured in LocalSettings.php!
 * The Key and Secret that were generated for you when you registered
 * your consumer. RSA private key isn't currently supported.
 */
$wgOAuthAuthenticationConsumerKey = null;
$wgOAuthAuthenticationConsumerSecret = null;

/**
 * Optionally set the Canonical url that the server will return,
 * if it's different from the OAuth endpoint. OAuth will use
 * wgCannonicalServer when generating the identity JWT, and this
 * code will compare the iss to this value, or $wgOAuthAuthenticationUrl
 * if this isn't set.
 */
$wgOAuthAuthenticationCanonicalUrl = null;

/**
 * Allow usurpation of accounts. If accounts on the OAuth provider have the same
 * name as an already created local account, this flag decides if the user is allowed
 * to login, or if the login will fail with an error message.
 */
$wgOAuthAuthenticationAccountUsurpation = false;

$dir = __DIR__;
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\SpecialOAuthLogin'] = "$dir/specials/SpecialOAuthLogin.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\Config'] = "$dir/utils/Config.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\Exception'] = "$dir/utils/Exception.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\Hooks'] = "$dir/utils/Hooks.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\OAuthExternalUser'] = "$dir/utils/OAuthExternalUser.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\OAuthLoginHandler'] = "$dir/handlers/OAuthLoginHandler.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\LoginFinishHandler'] = "$dir/handlers/LoginFinishHandler.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\AuthenticationHandler'] = "$dir/handlers/AuthenticationHandler.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\LoginInitHandler'] = "$dir/handlers/LoginInitHandler.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\SessionStore'] = "$dir/store/SessionStore.php";
$wgAutoloadClasses['MediaWiki\Extensions\OAuthAuthentication\PhpSessionStore'] = "$dir/store/PhpSessionStore.php";

## i18n
$messagesDirs['OAuthAuthentication'] = "$dir/i18n";
#$messagesFiles['OAuthAuthentication'] = "$langDir/OAuthAuthentication.alias.php";


## Use mwoauth-php. Cool Kids can use composer to do this.
$wgAutoloadClasses['MWOAuthClientConfig'] = "$dir/libs/mwoauth-php/MWOAuthClient.php";
$wgAutoloadClasses['MWOAuthClient'] = "$dir/libs/mwoauth-php/MWOAuthClient.php";



$wgSpecialPages['OAuthLogin'] = 'MediaWiki\Extensions\OAuthAuthentication\SpecialOAuthLogin';

$wgHooks['PersonalUrls'][] = 'MediaWiki\Extensions\OAuthAuthentication\Hooks::onPersonalUrls';
$wgHooks['PostLoginRedirect'][] = 'MediaWiki\Extensions\OAuthAuthentication\Hooks::onPostLoginRedirect';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'MediaWiki\Extensions\OAuthAuthentication\Hooks::onLoadExtensionSchemaUpdates';
$wgHooks['GetPreferences'][] = 'MediaWiki\Extensions\OAuthAuthentication\Hooks::onGetPreferences';

$wgHooks['UnitTestsList'][] = function( array &$files ) {
	$directoryIterator = new \RecursiveDirectoryIterator( __DIR__ . '/tests/' );
	foreach ( new \RecursiveIteratorIterator( $directoryIterator ) as $fileInfo ) {
		if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
			$files[] = $fileInfo->getPathname();
		}
	}
	return true;
};




