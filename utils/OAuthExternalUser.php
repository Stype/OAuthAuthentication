<?php

namespace MediaWiki\Extensions\OAuthAuthentication;

class OAuthExternalUser {

	// Local user_id
	private $userId;

	// Remote Username
	private $username;

	// Remote unique id
	private $remoteid;

	public function __construct( $rid, $uid, $name ) {
		$this->remoteId = $rid;
		$this->userId = $uid; // OIDC specifies this is unique for the IdP
		$this->username = $name;
	}

	public static function newFromRemoteId( $rid, $username, \DatabaseBase $db ) {
		$row = $db->selectRow(
			'oauthauth_user',
			array( 'oaau_rid', 'oaau_uid', 'oaau_username' ),
			array( 'oaau_rid' => $rid ),
			__METHOD__
		);

		if ( !$row ) {
			return new self( 0, 0, $username );
		} else {
			return new self( $rid, $row->oaau_uid, $row->oaau_username );
		}
	}

	public static function addToDatabase( \DatabaseBase $db ) {
		$db->insert(
			'oauthauth_user',
			array(
				'oaau_rid' => $this->remoteId,
				'oaau_uid' => $this->userId,
				'oaau_username' => $this->username,
			),
			__METHOD__
		);
	}

	public function getName() {
		return $this->username;
	}

	public function getLocalId() {
		return $this->userId;
	}

	public function attached() {
		return ( $this->userId !== 0 );
	}

}
