'use strict';

/* global wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const UserRegistrationIcon = createElement( 'svg', { width: 20, height: 20, viewBox: '0 0 20 20', className: 'dashicon' },
	createElement( 'path', { fill: 'currentColor', d: 'M4.5 0v3H0v17h20V0H4.5zM9 19H1V4h8v15zm10 0h-9V3H5.5V1H19v18zM6.5 6h-4V5h4v1zm1 2v1h-5V8h5zm-5 3h3v1h-3v-1z' } )
);

registerBlockType( 'user-registration/form-selector', {
    title: 'User Registration',

    icon: UserRegistrationIcon,

    category: 'widgets',

    edit() {
        return 'Shortcode Here.';
    },

    save() {
        return 'Shortcode Contents Here.';
    },
} );
