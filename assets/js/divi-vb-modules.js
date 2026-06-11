( function () {
	'use strict';

	if (
		! window.vendor ||
		! window.vendor.React ||
		! window.vendor.wp ||
		! window.vendor.wp.hooks
	) {
		return;
	}

	var React = window.vendor.React;

	var regFn = ( window.divi &&
	              window.divi.moduleLibrary &&
	              typeof window.divi.moduleLibrary.registerModule === 'function' )
	            ? window.divi.moduleLibrary.registerModule
	            : null;

	if ( ! regFn ) {
		return;
	}

	var placeholderStyle = {
		padding:    '20px',
		textAlign:  'center',
		background: '#f5f5f5',
		border:     '1px dashed #ccc',
		fontFamily: 'sans-serif',
		fontSize:   '13px',
		color:      '#555',
	};

	// Dynamic data injected by PHP via wp_localize_script — must be read before first use.
	var vbData = window.urmDiviVbData || {};

	// AJAX preview endpoint.
	var AJAX_URL   = vbData.ajaxUrl      || '';
	var AJAX_NONCE = vbData.previewNonce || '';

	/**
	 * Builds a React component that fetches the module's server-rendered HTML
	 * via a custom WP AJAX endpoint and injects it via direct DOM mutation.
	 *
	 * Using useRef + innerHTML (no useState) keeps the root DOM node stable
	 * across fetches so D5 never loses its reference to the module — which is
	 * what caused the "Row selected instead of module" regression.
	 *
	 * pointer-events: none is applied inside the injected wrapper so form
	 * elements don't swallow clicks; the root ref div (pointer-events: auto)
	 * receives them and they bubble to D5's module container for selection.
	 */
	function createSSR() {
		if ( ! React.useRef || ! React.useEffect || ! AJAX_URL ) {
			return null;
		}

		return function BlockPreview( props ) {
			var ref      = React.useRef( null );
			var cacheKey = JSON.stringify( props.attributes || {} );

			React.useEffect( function() {
				var node = ref.current;
				if ( ! node ) { return; }

				var body = new FormData();
				body.append( 'action', 'urm_d5_preview' );
				body.append( 'nonce',  AJAX_NONCE );
				body.append( 'block',  props.block );
				body.append( 'attrs',  cacheKey );

				window.fetch( AJAX_URL, { method: 'POST', body: body } )
					.then( function( r ) { return r.json(); } )
					.then( function( data ) {
						if ( ! node.isConnected ) { return; }
						var html = ( data.success && data.data.html ) ? data.data.html : '';
						// Always write — clears stale content when HTML is empty.
						// pointer-events:none on the inner wrapper lets clicks
						// pass up to the root ref div → D5 module container.
						node.innerHTML = html
							? '<div style="pointer-events:none;user-select:none">' + html + '</div>'
							: '';
					} )
					.catch( function() {} );
			}, [ cacheKey ] );

			// Single stable root div — same DOM node across every re-render,
			// so D5 always has a valid reference for module selection.
			return React.createElement( 'div', { ref: ref } );
		};
	}

	// Expose on window so the pro JS IIFE can reuse the same component.
	window.urmBlockPreview = createSSR();
	var SSR = window.urmBlockPreview;

	function makeRenderer( blockName, title ) {
		return function EditRenderer( props ) {
			if ( SSR ) {
				var attrs = ( props && props.attrs && typeof props.attrs === 'object' )
					? props.attrs
					: ( props && props.attributes && typeof props.attributes === 'object' )
						? props.attributes
						: {};
				return React.createElement( SSR, { block: blockName, attributes: attrs } );
			}
			return React.createElement( 'div', { style: placeholderStyle }, title );
		};
	}

	// Static select options shared across modules.
	var userStateOptions = {
		'':           { label: '-- Select User State --' },
		'logged_in':  { label: 'Logged In' },
		'logged_out': { label: 'Logged Out' },
	};

	/**
	 * Each field gets its own unique groupSlug so Divi's divi/composite
	 * accordion renders all fields instead of collapsing them into one.
	 *
	 * Field definition shape:
	 *   { key, label, desc, options? }
	 * When `options` is provided the field renders as divi/select,
	 * otherwise as divi/text.
	 */
	function register( name, d4Shortcode, title, fields ) {
		var attrs = {
			module: { type: 'object', settings: { meta: { meta: {} } } },
		};
		var modSettings = { content: 'auto' };

		if ( fields && fields.length ) {
			var contentGroups  = {};
			var settingsGroups = {};

			for ( var i = 0; i < fields.length; i++ ) {
				var f    = fields[ i ];
				var slug = 'content_' + f.key;

				var component = f.options
					? { type: 'field', name: 'divi/select', props: { options: f.options } }
					: { type: 'field', name: 'divi/text' };

				contentGroups[ f.key ] = {
					groupType: 'group-item',
					item: {
						groupSlug:   slug,
						priority:    10,
						render:      true,
						subName:     f.key,
						label:       f.label,
						description: f.desc || '',
						category:    'basic_option',
						features:    { sticky: false, hover: false },
						component:   component,
					},
				};

				settingsGroups[ slug ] = {
					panel:         'content',
					priority:      ( i + 1 ) * 10,
					groupName:     f.key,
					multiElements: true,
					component:     { name: 'divi/composite', props: { groupLabel: f.label } },
				};
			}

			attrs.content = {
				type:     'object',
				settings: {
					innerContent: {
						groupType: 'into-multiple-groups',
						groups:    contentGroups,
					},
				},
			};
			modSettings.groups = settingsGroups;
		}

		regFn(
			{
				name:        name,
				d4Shortcode: d4Shortcode,
				title:       title,
				moduleIcon:  'divi/module',
				category:    'module',
				attributes:  attrs,
				settings:    modSettings,
			},
			{ renderers: { edit: makeRenderer( name, title ) } }
		);
	}

	register(
		'urm/registration-form', 'urm-registration-form', 'URM Registration Form',
		[
			{ key: 'formId',    label: 'Registration Form', desc: 'Select the registration form to display.',           options: vbData.forms || {} },
			{ key: 'userState', label: 'User State',        desc: 'Show this module to specific user states only.',     options: userStateOptions },
		]
	);

	register(
		'urm/login-form', 'urm-login-form', 'URM Login Form',
		[
			{ key: 'redirectUrl',    label: 'Redirect URL',        desc: 'Redirect the user to this URL after login.' },
			{ key: 'logoutRedirect', label: 'Logout Redirect URL', desc: 'Redirect the user to this URL after logout.' },
			{ key: 'userState',      label: 'User State',          desc: 'Show this module to specific user states only.', options: userStateOptions },
		]
	);

	register(
		'urm/myaccount', 'urm-myaccount', 'URM My Account',
		[
			{ key: 'redirectUrl',    label: 'Redirect URL',        desc: 'Redirect the user to this URL after login.' },
			{ key: 'logoutRedirect', label: 'Logout Redirect URL', desc: 'Redirect the user to this URL after logout.' },
			{ key: 'userState',      label: 'User State',          desc: 'Show this module to specific user states only.', options: userStateOptions },
		]
	);

	register( 'urm/edit-profile',  'urm-edit-profile',  'URM Edit Profile',  null );
	register( 'urm/edit-password', 'urm-edit-password', 'URM Edit Password', null );

	register(
		'urm/membership-groups', 'urm-membership-groups', 'URM Membership Groups',
		[
			{ key: 'groupId',    label: 'Membership Groups', desc: 'Select the membership group to display.',        options: vbData.membershipGroups || {} },
			{ key: 'buttonText', label: 'Button Text',       desc: 'Label for the sign up button.' },
		]
	);

	register( 'urm/membership-thank-you', 'urm-membership-thank-you', 'URM Membership Thank You', null );

	register(
		'urm/content-restriction', 'urm-content-restriction', 'URM Content Restriction',
		[
			{ key: 'userRole',        label: 'User Role', desc: 'User role required to view the restricted content.', options: vbData.roles || {} },
			{ key: 'restrictContent', label: 'Content',   desc: 'HTML content visible only to users with the required role.' },
		]
	);

} )();
