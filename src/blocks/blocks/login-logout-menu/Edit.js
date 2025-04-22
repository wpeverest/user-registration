import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { loginLabel, logoutLabel } = attributes;
	const [loginState, setLoginState] = useState( 'loggedIn' );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Login Logout Menu Settings', 'user-registration' ) }>
					<TextControl
						label={ __( 'Login Label', 'user-registration' ) }
						value={ loginLabel }
						onChange={ ( value ) => setAttributes( { loginLabel: value } ) }
					/>
					<TextControl
						label={ __( 'Logout Label', 'user-registration' ) }
						value={ logoutLabel }
						onChange={ ( value ) => setAttributes( { logoutLabel: value } ) }
					/>
					<SelectControl
						label={ __( 'Login State', 'user-registration' ) }
						value={ loginState }
						options={ [
							{ label: __( 'Logged In', 'user-registration' ), value: 'loggedIn' },
							{ label: __( 'Logged Out', 'user-registration' ), value: 'loggedOut' },
						] }
						onChange={ ( value ) => setLoginState( value ) }
					/>
				</PanelBody>
			</InspectorControls>
		<a { ...blockProps } className="ur-login-logout-block-editor-preview">
			{ loginState === 'loggedIn' ? logoutLabel : loginLabel }
		</a>
		</>
	);
}
