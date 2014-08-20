<?php
/**
 * @group OAuthAuthentication
 */
class OAuthAuthDBTest extends MediaWikiTestCase {

	public function __construct( $name = null, array $data = array(), $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
	}

	protected function setUp() {
		parent::setUp();
		if ( $this->db->tableExists( 'oauthauth_user' ) ) {
			$this->db->dropTable( 'oauthauth_user' );
		}
		$this->db->sourceFile( __DIR__ . '/../store/oauthauth.sql' );

		// TODO: Setup some test data
	}

	protected function tearDown() {
		$this->db->dropTable( 'oauthauth_user' );
		parent::tearDown();
	}

	public function needsDB() {
		return true;
	}

	// Stub to make sure db handling is working
	public function testInit() {
		$this->assertSame( true, true );
	}

}
