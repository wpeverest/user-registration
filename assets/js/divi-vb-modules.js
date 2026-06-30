( function () {
	'use strict';

	// Root cause: Divi sets window.divi.moduleLibrary.registerModule only AFTER
	// all enqueued VB plugin scripts have executed. The core script is enqueued
	// first (pro depends on it), so its setTimeout(fn,0) fires before registerModule
	// exists. The pro script is last, so its timeout fires when registerModule is
	// available.
	//
	// Fix: expose the core registration logic as window.urmDivi.registerCoreModules
	// synchronously (during script parse) so the pro script can call it from inside
	// its own working timeout. The timeout below handles the free-plugin case where
	// core is the last enqueued script and registerModule is available by then.
	window.urmDivi = window.urmDivi || {};

	window.urmDivi.registerCoreModules = function () {

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

		var vbData = window.urmDiviVbData || {};

		var AJAX_URL   = vbData.ajaxUrl      || '';
		var AJAX_NONCE = vbData.previewNonce || '';

		/**
		 * Builds a React component that fetches the module's server-rendered HTML
		 * via a custom WP AJAX endpoint and injects it via direct DOM mutation.
		 *
		 * Using useRef + innerHTML (no useState) keeps the root DOM node stable
		 * across fetches so D5 never loses its reference to the module.
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
							node.innerHTML = html
								? '<div style="pointer-events:none;user-select:none">' + html + '</div>'
								: '';
						} )
						.catch( function() {} );
				}, [ cacheKey ] );

				return React.createElement( 'div', { ref: ref } );
			};
		}

		// Expose on window so the pro JS can reuse the same component instance.
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

		var userStateOptions = {
			'':           { label: '-- Select User State --' },
			'logged_in':  { label: 'Logged In' },
			'logged_out': { label: 'Logged Out' },
		};

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
				{ key: 'formId',    label: 'Registration Form', desc: 'Select the registration form to display.',       options: vbData.forms || {} },
				{ key: 'userState', label: 'User State',        desc: 'Show this module to specific user states only.', options: userStateOptions },
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
				{ key: 'groupId',    label: 'Membership Groups', desc: 'Select the membership group to display.',  options: vbData.membershipGroups || {} },
				{ key: 'buttonText', label: 'Button Text',       desc: 'Label for the sign up button.' },
			]
		);

		register( 'urm/membership-thank-you', 'urm-membership-thank-you', 'URM Membership Thank You', null );

	}; // end registerCoreModules

	// Free-plugin path: core is the last enqueued VB script, so registerModule is
	// available by the time this timeout fires.
	window.setTimeout( function () {
		window.urmDivi.registerCoreModules();
	}, 0 );

} )();
