'use strict';

/* global ur_form_block_data, wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, ToggleControl, PanelBody, ServerSideRender, Placeholder } = wp.components;

const UserRegistrationIcon = createElement( 'svg', { width: 24, height: 24, viewBox: '0 0 32 32' },
	createElement( 'path', { fill: 'currentColor', d: 'M27.58 4a27.9 27.9 0 0 0-5.17 4 27 27 0 0 0-4.09 5.08 33.06 33.06 0 0 1 2 4.65A23.78 23.78 0 0 1 24 12.15V18a8 8 0 0 1-5.89 7.72l-.21.05a27 27 0 0 0-1.9-8.16A27.9 27.9 0 0 0 9.59 8a27.9 27.9 0 0 0-5.17-4L4 3.77V18a12 12 0 0 0 9.93 11.82h.14a11.72 11.72 0 0 0 3.86 0h.14A12 12 0 0 0 28 18V3.77zM8 18v-5.85a23.86 23.86 0 0 1 5.89 13.57A8 8 0 0 1 8 18zm8-16a3 3 0 1 0 3 3 3 3 0 0 0-3-3z' } )
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
					block="user-registration/form-selector"
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
