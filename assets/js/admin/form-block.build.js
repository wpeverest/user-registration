/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/* global ur_form_block_data, wp */

var createElement = wp.element.createElement;
var registerBlockType = wp.blocks.registerBlockType;
var InspectorControls = wp.editor.InspectorControls;
var _wp$components = wp.components,
    SelectControl = _wp$components.SelectControl,
    ToggleControl = _wp$components.ToggleControl,
    PanelBody = _wp$components.PanelBody,
    ServerSideRender = _wp$components.ServerSideRender,
    Placeholder = _wp$components.Placeholder;


registerBlockType('user-registration/form-selector', {
	title: ur_form_block_data.i18n.title,
	icon: 'universal-access-alt',
	category: 'widgets',
	attributes: {
		formId: {
			type: 'string'
		}
	},
	edit: function edit(props) {
		var _props$attributes$for = props.attributes.formId,
		    formId = _props$attributes$for === undefined ? '' : _props$attributes$for,
		    setAttributes = props.setAttributes;

		var formOptions = Object.keys(ur_form_block_data.forms).map(function (index) {
			return { value: Number(index), label: ur_form_block_data.forms[index] };
		});
		var jsx = void 0;
		formOptions.unshift({ value: '', label: ur_form_block_data.i18n.form_select });
		function selectForm(value) {
			setAttributes({ formId: value });
		}
		jsx = [wp.element.createElement(
			InspectorControls,
			{ key: 'ur-gutenberg-form-selector-inspector-controls' },
			wp.element.createElement(
				PanelBody,
				{ title: ur_form_block_data.i18n.form_settings },
				wp.element.createElement(SelectControl, {
					label: ur_form_block_data.i18n.form_selected,
					value: formId,
					options: formOptions,
					onChange: selectForm
				})
			)
		)];
		if (formId) {
			jsx.push(wp.element.createElement(ServerSideRender, {
				key: 'ur-gutenberg-form-selector-server-side-renderer',
				block: 'user-registration/form-selector',
				attributes: props.attributes
			}));
		} else {
			jsx.push(wp.element.createElement(
				Placeholder,
				{
					key: 'ur-gutenberg-form-selector-wrap',
					className: 'ur-gutenberg-form-selector-wrap' },
				wp.element.createElement('img', { src: ur_form_block_data.logo_url }),
				wp.element.createElement(
					'h2',
					null,
					ur_form_block_data.i18n.title
				),
				wp.element.createElement(SelectControl, {
					key: 'ur-gutenberg-form-selector-select-control',
					value: formId,
					options: formOptions,
					onChange: selectForm
				})
			));
		}
		return jsx;
	},
	save: function save() {
		return null;
	}
});

/***/ })
/******/ ]);