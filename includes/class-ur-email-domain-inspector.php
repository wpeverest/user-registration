<?php
/**
 * Email Domain Inspector.
 *
 * Looks up what a sending domain publishes about itself — MX, SPF and DMARC —
 * so the health checkup can say whether this site is actually allowed to send
 * mail as the configured "From" address, rather than only whether that address
 * is spelled correctly.
 *
 * Lookups are not cached: the OS resolver already caches by TTL (a repeat pass
 * measures well under a millisecond), and a second cache layer would only serve
 * stale records to someone who just fixed their DNS and re-ran the scan.
 *
 * @class    UR_Email_Domain_Inspector
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Email_Domain_Inspector' ) ) :

	/**
	 * UR_Email_Domain_Inspector Class.
	 */
	class UR_Email_Domain_Inspector {

		/**
		 * Per-request memo, keyed by domain. Every identity check needs the same
		 * three records, so they're resolved once and shared.
		 *
		 * @var array
		 */
		private static $lookups = array();

		/**
		 * TLDs reserved for local/development use (RFC 2606 plus the ".local"
		 * convention most local dev tools use). These never have public records,
		 * so their absence isn't a misconfiguration.
		 *
		 * @var array
		 */
		private static $local_tlds = array( 'local', 'test', 'example', 'invalid', 'localhost' );

		/**
		 * Mailbox providers a site owner cannot publish DNS for. Sending "as" one
		 * of these from your own server can never be authenticated — no setting
		 * fixes it, because you don't control the domain.
		 *
		 * @var array
		 */
		private static $mailbox_providers = array(
			'gmail.com',
			'googlemail.com',
			'yahoo.com',
			'yahoo.co.uk',
			'yahoo.in',
			'ymail.com',
			'outlook.com',
			'hotmail.com',
			'hotmail.co.uk',
			'live.com',
			'msn.com',
			'aol.com',
			'icloud.com',
			'me.com',
			'mac.com',
			'gmx.com',
			'gmx.net',
			'gmx.de',
			'mail.com',
			'zoho.com',
			'yandex.com',
			'yandex.ru',
			'mail.ru',
			'proton.me',
			'protonmail.com',
			'pm.me',
			'tutanota.com',
			'fastmail.com',
			'qq.com',
			'163.com',
			'126.com',
			'naver.com',
			'rediffmail.com',
		);

		/**
		 * @param string $domain Domain to check.
		 * @return bool
		 */
		public static function is_local_domain( $domain ) {
			if ( empty( $domain ) ) {
				return false;
			}

			$labels = explode( '.', $domain );
			$tld    = end( $labels );

			return in_array( strtolower( $tld ), self::$local_tlds, true );
		}

		/**
		 * @param string $domain Domain to check.
		 * @return bool
		 */
		public static function is_mailbox_provider( $domain ) {
			return in_array( strtolower( (string) $domain ), self::$mailbox_providers, true );
		}

		/**
		 * The SMTP hosts each mailbox provider serves its own users from, where
		 * that host isn't simply a subdomain of the address (which the generic
		 * check below already covers). Microsoft and Apple both send several
		 * brands through one host, so they can't be inferred.
		 *
		 * @var array
		 */
		private static $provider_smtp_hosts = array(
			'outlook.com'    => array( 'smtp.office365.com', 'smtp-mail.outlook.com' ),
			'hotmail.com'    => array( 'smtp.office365.com', 'smtp-mail.outlook.com' ),
			'hotmail.co.uk'  => array( 'smtp.office365.com', 'smtp-mail.outlook.com' ),
			'live.com'       => array( 'smtp.office365.com', 'smtp-mail.outlook.com' ),
			'msn.com'        => array( 'smtp.office365.com', 'smtp-mail.outlook.com' ),
			'icloud.com'     => array( 'smtp.mail.me.com' ),
			'me.com'         => array( 'smtp.mail.me.com' ),
			'mac.com'        => array( 'smtp.mail.me.com' ),
			'gmail.com'      => array( 'smtp.gmail.com', 'smtp-relay.gmail.com', 'smtp.googlemail.com' ),
			'googlemail.com' => array( 'smtp.gmail.com', 'smtp.googlemail.com' ),
			'proton.me'      => array( 'smtp.protonmail.ch' ),
			'protonmail.com' => array( 'smtp.protonmail.ch' ),
			'pm.me'          => array( 'smtp.protonmail.ch' ),
		);

		/**
		 * Whether an SMTP host is the mailbox provider's own — i.e. the one
		 * party that *can* authenticate mail for that address.
		 *
		 * Sending as `you@gmail.com` through `smtp.gmail.com` is a fully
		 * authenticated, aligned send: Gmail signs it. Without this, the single
		 * most common working setup on the planet reads as a hard failure.
		 *
		 * @param string $domain Mailbox-provider domain, e.g. 'gmail.com'.
		 * @param string $host   SMTP host currently configured.
		 * @return bool
		 */
		public static function is_provider_smtp_host( $domain, $host ) {
			$domain = strtolower( trim( (string) $domain ) );
			$host   = strtolower( trim( (string) $host ) );

			if ( '' === $domain || '' === $host ) {
				return false;
			}

			if ( isset( self::$provider_smtp_hosts[ $domain ] )
				&& in_array( $host, self::$provider_smtp_hosts[ $domain ], true ) ) {
				return true;
			}

			// Covers the providers that do serve from their own domain, e.g.
			// smtp.mail.yahoo.com for yahoo.com.
			return self::domains_align( $host, $domain );
		}

		/**
		 * Everything published about a domain, in one pass.
		 *
		 * Returns:
		 *  - resolvable  bool   Whether DNS answered at all.
		 *  - has_mx      bool
		 *  - spf         array  { found, record, qualifier } qualifier is -all/~all/?all/+all/''.
		 *  - dmarc       array  { found, record, policy } policy is reject/quarantine/none/''.
		 *
		 * A failed lookup is reported as `resolvable => false` and never as
		 * "no record": telling someone their SPF is missing when the truth is
		 * that DNS is unreachable sends them off to fix the wrong thing.
		 *
		 * @param string $domain Domain to inspect.
		 * @return array
		 */
		public static function inspect( $domain ) {
			$domain = strtolower( trim( (string) $domain ) );

			if ( '' === $domain ) {
				return self::unresolvable();
			}

			if ( isset( self::$lookups[ $domain ] ) ) {
				return self::$lookups[ $domain ];
			}

			if ( ! function_exists( 'dns_get_record' ) || ! function_exists( 'checkdnsrr' ) ) {
				self::$lookups[ $domain ] = self::unresolvable();

				return self::$lookups[ $domain ];
			}

			$has_mx    = (bool) @checkdnsrr( $domain, 'MX' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$root_txt  = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$dmarc_txt = @dns_get_record( '_dmarc.' . $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			// An A record proves the resolver is working even when a domain
			// publishes no mail records at all, which distinguishes "nothing
			// published" from "DNS didn't answer".
			$resolvable = $has_mx || is_array( $root_txt ) || (bool) @checkdnsrr( $domain, 'A' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			$result = array(
				'resolvable' => $resolvable,
				'has_mx'     => $has_mx,
				'spf'        => self::parse_spf( $root_txt ),
				'dmarc'      => self::parse_dmarc( $dmarc_txt ),
			);

			self::$lookups[ $domain ] = $result;

			return $result;
		}

		/**
		 * Shape returned when DNS can't be consulted.
		 *
		 * @return array
		 */
		private static function unresolvable() {
			return array(
				'resolvable' => false,
				'has_mx'     => false,
				'spf'        => array(
					'found'     => false,
					'record'    => '',
					'qualifier' => '',
				),
				'dmarc'      => array(
					'found'  => false,
					'record' => '',
					'policy' => '',
				),
			);
		}

		/**
		 * Pull the SPF record out of a domain's TXT records and read how strict
		 * its `all` mechanism is. `-all` tells receivers to reject anything not
		 * listed; `~all` asks them to accept but mark it.
		 *
		 * @param mixed $txt_records Result of dns_get_record().
		 * @return array { found, record, qualifier }
		 */
		private static function parse_spf( $txt_records ) {
			$record = self::find_txt( $txt_records, 'v=spf1' );

			if ( '' === $record ) {
				return array(
					'found'     => false,
					'record'    => '',
					'qualifier' => '',
				);
			}

			$qualifier = '';

			if ( preg_match( '/([-~?+])all\b/i', $record, $matches ) ) {
				$qualifier = $matches[1] . 'all';
			}

			return array(
				'found'     => true,
				'record'    => $record,
				'qualifier' => $qualifier,
			);
		}

		/**
		 * Read the published DMARC policy — what receivers should do when a
		 * message fails authentication.
		 *
		 * @param mixed $txt_records Result of dns_get_record().
		 * @return array { found, record, policy }
		 */
		private static function parse_dmarc( $txt_records ) {
			$record = self::find_txt( $txt_records, 'v=DMARC1' );

			if ( '' === $record ) {
				return array(
					'found'  => false,
					'record' => '',
					'policy' => '',
				);
			}

			$policy = '';

			if ( preg_match( '/\bp\s*=\s*(none|quarantine|reject)\b/i', $record, $matches ) ) {
				$policy = strtolower( $matches[1] );
			}

			return array(
				'found'  => true,
				'record' => $record,
				'policy' => $policy,
			);
		}

		/**
		 * First TXT record starting with a given prefix. Long records arrive
		 * split into 255-character chunks, so the `entries` array is joined
		 * before matching.
		 *
		 * @param mixed  $txt_records Result of dns_get_record().
		 * @param string $prefix      Prefix to match, e.g. 'v=spf1'.
		 * @return string
		 */
		private static function find_txt( $txt_records, $prefix ) {
			if ( ! is_array( $txt_records ) ) {
				return '';
			}

			foreach ( $txt_records as $record ) {
				$value = '';

				if ( isset( $record['entries'] ) && is_array( $record['entries'] ) ) {
					$value = implode( '', $record['entries'] );
				} elseif ( isset( $record['txt'] ) ) {
					$value = $record['txt'];
				}

				if ( '' !== $value && 0 === stripos( trim( $value ), $prefix ) ) {
					return trim( $value );
				}
			}

			return '';
		}

		/**
		 * Domain part of an email address, lowercased.
		 *
		 * @param string $address Email address.
		 * @return string
		 */
		public static function domain_of( $address ) {
			$at_pos = strrpos( (string) $address, '@' );

			return false === $at_pos ? '' : strtolower( substr( $address, $at_pos + 1 ) );
		}

		/**
		 * Registrable-ish comparison between two domains, ignoring a leading
		 * `www.` and matching a subdomain against its parent — `mail.site.com`
		 * and `site.com` are the same organisation for alignment purposes.
		 *
		 * @param string $one Domain.
		 * @param string $two Domain.
		 * @return bool
		 */
		public static function domains_align( $one, $two ) {
			$one = preg_replace( '/^www\./i', '', strtolower( (string) $one ) );
			$two = preg_replace( '/^www\./i', '', strtolower( (string) $two ) );

			if ( '' === $one || '' === $two ) {
				return false;
			}

			if ( $one === $two ) {
				return true;
			}

			return (bool) preg_match( '/\.' . preg_quote( $two, '/' ) . '$/i', $one )
				|| (bool) preg_match( '/\.' . preg_quote( $one, '/' ) . '$/i', $two );
		}
	}

endif;
