'use strict';

/* global ur_form_block_data, wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, ToggleControl, PanelBody, ServerSideRender, Placeholder } = wp.components;

const UserRegistrationIcon = createElement( 'svg', { width: 20, height: 20, viewBox: '0 0 20 20', className: 'dashicon' },
	createElement( 'path', { fill: 'currentColor', d: 'M4.5 0v3H0v17h20V0H4.5zM9 19H1V4h8v15zm10 0h-9V3H5.5V1H19v18zM6.5 6h-4V5h4v1zm1 2v1h-5V8h5zm-5 3h3v1h-3v-1z' } )
);

registerBlockType( 'user-registration/form-selector', {
    title: ur_form_block_data.i18n.title,
    icon: UserRegistrationIcon,
    category: 'widgets',
    attributes: {
		formId: {
			type: 'string',
		},
	},
	edit( props ) {
		const { attributes: { formId = '' }, setAttributes } = props;
		const formOptions = Object.keys( ur_form_block_data.forms ).map( ( index ) => (
			{ value: Number( index ), label: ur_form_block_data.forms[ index ] }
		) );
		let jsx;
 		formOptions.unshift( { value: '', label: ur_form_block_data.i18n.form_select } );
 		function selectForm( value ) {
			setAttributes( { formId: value } );
		}
 		jsx = [
			<InspectorControls key="ur-gutenberg-form-selector-inspector-controls">
				<PanelBody title={ ur_form_block_data.i18n.form_settings }>
					<SelectControl
						label={ ur_form_block_data.i18n.form_selected }
						value={ formId }
						options={ formOptions }
						onChange={ selectForm }
					/>
				</PanelBody>
			</InspectorControls>
		];
 		if ( formId ) {
			jsx.push(
				<ServerSideRender
					key="ur-gutenberg-form-selector-server-side-renderer"
					block="everest-forms/form-selector"
					attributes={ props.attributes }
				/>
			);
		} else {
			jsx.push(
				<Placeholder
					key="ur-gutenberg-form-selector-wrap"
					className="ur-gutenberg-form-selector-wrap">
					<img src={ ur_form_block_data.logo_url }/>
					<h2>{ ur_form_block_data.i18n.title }</h2>
					<SelectControl
						key="ur-gutenberg-form-selector-select-control"
						value={ formId }
						options={ formOptions }
						onChange={ selectForm }
					/>
				</Placeholder>
			);
		}
 		return jsx;
	},

    save() {
        return null;
    },
} );
