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

					var fallback = '<div style="padding:20px;text-align:center;background:#f5f5f5;border:1px dashed #ccc;font-family:sans-serif;font-size:13px;color:#555">'
					               + ( props.title || props.block ) + '</div>';

					// Buttons/inputs rendered inside the VB canvas preview are real,
					// live elements (e.g. an actual form submit button) — disable
					// them so editing the layout can't trigger real actions.
					function disableInteractiveElements( container ) {
						if ( ! container || ! container.querySelectorAll ) { return; }
						var els = container.querySelectorAll( 'button, input, select, textarea' );
						for ( var i = 0; i < els.length; i++ ) {
							els[ i ].setAttribute( 'disabled', 'disabled' );
						}
					}

					window.fetch( AJAX_URL, { method: 'POST', body: body } )
						.then( function( r ) { return r.json(); } )
						.then( function( data ) {
							if ( ! node.isConnected ) { return; }
							var html = ( data.success && data.data.html ) ? data.data.html : '';
							node.innerHTML = html
								? '<div style="pointer-events:none;user-select:none">' + html + '</div>'
								: fallback;
							disableInteractiveElements( node );
						} )
						.catch( function() {
							if ( node.isConnected ) { node.innerHTML = fallback; }
						} );
				}, [ cacheKey ] );

				return React.createElement( 'div', { ref: ref } );
			};
		}

		// Expose on window so the pro JS can reuse the same component instance.
		window.urmBlockPreview = createSSR();
		var SSR = window.urmBlockPreview;

		function makeRenderer( blockName, title ) {
			return function EditRenderer( props ) {
				var ModuleContainer = window.divi &&
				                      window.divi.module &&
				                      window.divi.module.ModuleContainer;

				var domRef = React.useRef( null );

				var attrs = ( props && props.attrs && typeof props.attrs === 'object' )
					? props.attrs
					: ( props && props.attributes && typeof props.attributes === 'object' )
						? props.attributes
						: {};

				var content = SSR
					? React.createElement( SSR, { block: blockName, attributes: attrs, title: title } )
					: React.createElement( 'div', { style: placeholderStyle }, title );

				if ( ModuleContainer && props && props.id ) {
					return React.createElement(
						ModuleContainer,
						{
							domRef:                   domRef,
							attrs:                    props.attrs || {},
							defaultPrintedStyleAttrs: props.defaultPrintedStyleAttrs,
							elements:                 props.elements,
							id:                       props.id,
							name:                     props.name,
							isFirst:                  props.isFirst,
							isLast:                   props.isLast,
						},
						content
					);
				}

				return content;
			};
		}

		var userStateOptions = {
			'':           { label: '-- Select User State --' },
			'logged_in':  { label: 'Logged In' },
			'logged_out': { label: 'Logged Out' },
		};

		var userStateOptionsNoEmpty = {
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

					var component;
					if ( f.component ) {
						component = f.component;
					} else if ( f.options ) {
						component = { type: 'field', name: 'divi/select', props: { options: f.options } };
					} else {
						component = { type: 'field', name: 'divi/text' };
					}

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

			try {
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
			} catch ( e ) {
				// A duplicate-registration error means this module was already
				// registered (e.g. by the pro script running after us). Silently
				// skip so the remaining modules in this batch still register.
			}
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
				{ key: 'userState',      label: 'User State',          desc: 'Show this module to specific user states only.', options: userStateOptionsNoEmpty },
			]
		);

		register(
			'urm/myaccount', 'urm-myaccount', 'URM My Account',
			[
				{ key: 'redirectUrl',    label: 'Redirect URL',        desc: 'Redirect the user to this URL after login.' },
				{ key: 'logoutRedirect', label: 'Logout Redirect URL', desc: 'Redirect the user to this URL after logout.' },
				{ key: 'userState',      label: 'User State',          desc: 'Show this module to specific user states only.', options: userStateOptionsNoEmpty },
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
