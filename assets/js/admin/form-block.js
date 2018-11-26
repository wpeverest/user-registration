'use strict';

/* global ur_form_block_data, wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, ToggleControl, PanelBody, ServerSideRender, Placeholder } = wp.components;

registerBlockType( 'user-registration/form-selector', {
    title: ur_form_block_data.i18n.title,
    icon: 'universal-access-alt',
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
