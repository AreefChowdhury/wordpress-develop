<?php
/**
 * Test anonymization functions.
 *
 * @package WordPress\UnitTests
 *
 * @since 4.9.6
 *
 * @group functions
 * @group privacy
 *
 * @covers ::wp_privacy_anonymize_data
 */
class Tests_Functions_Anonymization extends WP_UnitTestCase {

	/**
	 * Tests that wp_privacy_anonymize_ip() properly anonymizes all possible IP address formats.
	 *
	 * @dataProvider data_wp_privacy_anonymize_ip
	 *
	 * @ticket 41083
	 * @ticket 43545
	 *
	 * @covers ::wp_privacy_anonymize_ip
	 *
	 * @param string $raw_ip          Raw IP address.
	 * @param string $expected_result Expected result.
	 */
	public function test_wp_privacy_anonymize_ip( $raw_ip, $expected_result ) {
		$actual_result = wp_privacy_anonymize_data( 'ip', $raw_ip );

		/* Todo test ipv6_fallback mode if keeping it.*/

		$this->assertSame( $expected_result, $actual_result );
	}

	/**
	 * Data provider for `test_wp_privacy_anonymize_ip()`.
	 *
	 * @since 4.9.6 Moved from `Test_WP_Community_Events::data_get_unsafe_client_ip_anonymization()`.
	 *
	 * @return array {
	 *     @type array {
	 *         @type string $raw_ip          Raw IP address.
	 *         @type string $expected_result Expected result.
	 *     }
	 * }
	 */
	public function data_wp_privacy_anonymize_ip() {
		return array(
			// Invalid IP.
			array(
				null,
				'0.0.0.0',
			),
			array(
				false,
				'0.0.0.0',
			),
			array(
				true,
				'0.0.0.0',
			),
			array(
				0,
				'0.0.0.0',
			),
			array(
				1,
				'0.0.0.0',
			),
			array(
				'',
				'0.0.0.0',
			),
			array(
				'0.0.0.0.0',
				'0.0.0.0',
			),
			array(
				'0000:0000:0000:0000:0000:0000:0127:2258',
				'::',
			),
			// Invalid IP. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'unknown',
				'0.0.0.0',
			),
			// Invalid IP. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'or=\"[1000:0000:0000:0000:0000:0000:0000:0001',
				'::',
			),
			// Invalid IP. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'or=\"1000:0000:0000:0000:0000:0000:0000:0001',
				'::',
			),
			// Invalid IP. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'1000:0000:0000:0000:0000:0000:0000:0001or=\"',
				'::',
			),
			// IPv4, no port.
			array(
				'10.20.30.45',
				'10.20.30.0',
			),
			// IPv4, port.
			array(
				'10.20.30.45:20000',
				'10.20.30.0',
			),
			// IPv4, netmask.
			array(
				'10.20.30.45/24',
				'10.20.30.0',
			),
			// IPv6, no port, reducible representation.
			array(
				'0000:0000:0000:0000:0000:0000:0000:0001',
				'::',
			),
			// IPv6, port, reducible representation.
			array(
				'[0000:0000:0000:0000:0000:0000:0000:0001]:1234',
				'::',
			),
			// IPv6, no port, reduced representation.
			array(
				'::',
				'::',
			),
			// IPv6, no port, reduced representation.
			array(
				'::1',
				'::',
			),
			// IPv6, port, reduced representation.
			array(
				'[::]:20000',
				'::',
			),
			// IPv6, address brackets without port delimiter and number, reduced representation.
			array(
				'[::1]',
				'::',
			),
			// IPv6, no port, compatibility mode.
			array(
				'::ffff:10.15.20.25',
				'::ffff:10.15.20.0',
			),
			// IPv6, port, compatibility mode.
			array(
				'[::FFFF:10.15.20.25]:30000',
				'::ffff:10.15.20.0',
			),
			// IPv6, no port, compatibility mode shorthand.
			array(
				'::127.0.0.1',
				'::ffff:127.0.0.0',
			),
			// IPv6, port, compatibility mode shorthand.
			array(
				'[::127.0.0.1]:30000',
				'::ffff:127.0.0.0',
			),
		);
	}

	/**
	 * Tests that wp_privacy_anonymize_ip() properly anonymizes all possible IP address formats.
	 *
	 * @dataProvider data_wp_privacy_anonymize_ip_with_inet_dependency
	 *
	 * @ticket 41083
	 * @ticket 43545
	 * @requires function inet_ntop
	 * @requires function inet_pton
	 *
	 * @covers ::wp_privacy_anonymize_ip
	 *
	 * @param string $raw_ip          Raw IP address.
	 * @param string $expected_result Expected result.
	 */
	public function test_wp_privacy_anonymize_ip_with_inet_dependency( $raw_ip, $expected_result ) {
		$this->test_wp_privacy_anonymize_ip( $raw_ip, $expected_result );
	}

	/**
	 * Data provider for `test_wp_privacy_anonymize_ip()`.
	 *
	 * @since 4.9.6 Moved from `Test_WP_Community_Events::data_get_unsafe_client_ip_anonymization()`.
	 *
	 * @return array {
	 *     @type array {
	 *         @type string $raw_ip          Raw IP address.
	 *         @type string $expected_result Expected result.
	 *     }
	 * }
	 */
	public function data_wp_privacy_anonymize_ip_with_inet_dependency() {
		return array(
			// Malformed string with valid IP substring. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'or=\"[1000:0000:0000:0000:0000:0000:0000:0001]:400',
				'1000::',
			),
			// Malformed string with valid IP substring. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'or=\"[1000:0000:0000:0000:0000:0000:0000:0001]',
				'1000::',
			),
			// Malformed string with valid IP substring. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'or=\"[1000:0000:0000:0000:0000:0000:0000:0001]400',
				'1000::',
			),
			// Malformed string with valid IP substring. Sometimes proxies add things like this, or other arbitrary strings.
			array(
				'[1000:0000:0000:0000:0000:0000:0000:0001]:235\"or=',
				'1000::',
			),
			// IPv6, no port.
			array(
				'2a03:2880:2110:df07:face:b00c::1',
				'2a03:2880:2110:df07::',
			),
			// IPv6, port.
			array(
				'[2a03:2880:2110:df07:face:b00c::1]:20000',
				'2a03:2880:2110:df07::',
			),
			// IPv6, no port, partially reducible representation.
			array(
				'1000:0000:0000:0000:0000:0000:0000:0001',
				'1000::',
			),
			// IPv6, port, partially reducible representation.
			array(
				'[1000:0000:0000:0000:0000:0000:0000:0001]:5678',
				'1000::',
			),
			// IPv6 with reachability scope.
			array(
				'fe80::b059:65f4:e877:c40%16',
				'fe80::',
			),
			// IPv6 with reachability scope.
			array(
				'FE80::B059:65F4:E877:C40%eth0',
				'fe80::',
			),
		);
	}

	/**
	 * Tests email anonymization of `wp_privacy_anonymize_data()`.
	 */
	public function test_anonymize_email() {
		$this->assertSame( 'deleted@site.invalid', wp_privacy_anonymize_data( 'email', 'bar@example.com' ) );
	}

	/**
	 * Tests URL anonymization of `wp_privacy_anonymize_data()`.
	 */
	public function test_anonymize_url() {
		$this->assertSame( 'https://site.invalid', wp_privacy_anonymize_data( 'url', 'https://example.com/author/username' ) );
	}

	/**
	 * Tests date anonymization of `wp_privacy_anonymize_data()`.
	 */
	public function test_anonymize_date() {
		$this->assertSame( '0000-00-00 00:00:00', wp_privacy_anonymize_data( 'date', '2003-12-25 12:34:56' ) );
	}

	/**
	 * Tests text anonymization of `wp_privacy_anonymize_data()`.
	 */
	public function test_anonymize_text() {
		$text = __( 'Four score and seven years ago' );
		$this->assertSame( '[deleted]', wp_privacy_anonymize_data( 'text', $text ) );
	}

	/**
	 * Tests long text anonymization of `wp_privacy_anonymize_data()`.
	 */
	public function test_anonymize_long_text() {
		$text = __( 'Four score and seven years ago' );
		$this->assertSame( 'This content was deleted by the author.', wp_privacy_anonymize_data( 'longtext', $text ) );
	}

	/**
	 * Tests text anonymization when a filter is added.
	 *
	 * @ticket 44141
	 */
	public function test_anonymize_with_filter() {
		add_filter( 'wp_privacy_anonymize_data', array( $this, 'filter_wp_privacy_anonymize_data' ), 10, 3 );
		$actual_url = wp_privacy_anonymize_data( 'url', 'https://example.com/author/username' );
		remove_filter( 'wp_privacy_anonymize_data', array( $this, 'filter_wp_privacy_anonymize_data' ), 10 );

		$this->assertSame( 'http://local.host/why-this-was-removed', $actual_url );
	}

	/**
	 * Changes the anonymized value for URLs.
	 *
	 * @since 4.9.8
	 *
	 * @param string  $anonymous Anonymized data.
	 * @param string  $type      Type of the data.
	 * @param string  $data      Original data.
	 * @return string Anonymized data.
	 */
	public function filter_wp_privacy_anonymize_data( $anonymous, $type, $data ) {
		if ( 'url' === $type && 'example.com' === parse_url( $data, PHP_URL_HOST ) ) {
			return 'http://local.host/why-this-was-removed';
		}
		return $anonymous;
	}
}
