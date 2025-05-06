import React, {useEffect, useState, useRef} from 'react';
import {Box} from '@chakra-ui/react';
import metadata from './block.json';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {Disabled, Notice, PanelBody, PanelRow, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const {urRestApiNonce, restURL} = typeof _UR_BLOCKS_ !== 'undefined' && _UR_BLOCKS_;

// Helper function to map options for SelectControl
const mapOptions = (list) =>
	Object.keys(list).map((index) => ({
		value: Number(index),
		label: list[index],
	}));

/**
 * Edit component for the membership listing block.
 *
 * @param {Object} props The props received from the parent component.
 * @return {JSX.Element} The Edit component.
 */
const Edit = (props) => {
	const {
		attributes: {redirection_page_id, group_id, thank_you_page_id, type, button_text},
		setAttributes,
	} = props;

	// State variables
	const [redirectionPageList, setRedirectionPageList] = useState('');
	const [thankYouPageList, setThankYouPageList] = useState('');
	const [groupList, setGroupList] = useState('');
	const [error, setError] = useState('');
	const [isLoading, setIsLoading] = useState(false);

	// Refs for locking post saving
	const redirectionLock = useRef(false);
	const thankYouLock = useRef(false);
	// Block props
	const useProps = useBlockProps();
	const blockName = metadata.name;
	const [success, setSuccess] = useState(false);
	const [isBlockList, setIsBlockList] = useState(type === 'block');


	// Fetch data for pages and groups
	const fetchData = async () => {
		try {
			if (!thankYouPageList || !redirectionPageList) {
				const res = await apiFetch({
					path: `${restURL}user-registration/v1/gutenberg-blocks/pages`,
					method: 'GET',
					headers: {'X-WP-Nonce': urRestApiNonce},
				});
				if (res.success) {
					setThankYouPageList(res.page_lists);
					setRedirectionPageList(res.page_lists);
				}
			}

			if (!groupList) {
				const res = await apiFetch({
					path: `${restURL}user-registration/v1/gutenberg-blocks/groups`,
					method: 'GET',
					headers: {'X-WP-Nonce': urRestApiNonce},
				});
				if (res.success) {
					setGroupList(res.group_lists);
				}
			}
		} catch (error) {
			console.error('Error fetching data:', error);
		}
	};

	// Verify a page
	const verifyPage = async ({id, type, lockKey, isLockedRef}) => {
		setIsLoading(true);
		setError('');
		setSuccess(false);
		try {
			await apiFetch({
				path: `${restURL}user-registration/v1/gutenberg-blocks/verify-pages`,
				method: 'POST',
				headers: {
					'X-WP-Nonce': urRestApiNonce,
					'Content-Type': 'application/json',
				},
				data: JSON.stringify({page_id: id, type}),
			});

			if (isLockedRef.current) {
				wp.data.dispatch('core/editor').unlockPostSaving(lockKey);
				isLockedRef.current = false;
			}
			setSuccess(true);
		} catch (err) {
			setError(err?.message || 'Something went wrong.');
			if (!isLockedRef.current) {
				wp.data.dispatch('core/editor').lockPostSaving(lockKey);
				isLockedRef.current = true;
			}
		} finally {
			setIsLoading(false);
		}
	};

	// Verify pages on load
	const verifyPagesOnLoad = async () => {
		if (redirection_page_id) {
			await verifyPage({
				id: redirection_page_id,
				type: 'user_registration_member_registration_page_id',
				lockKey: 'ur-redirection-page-lock',
				isLockedRef: redirectionLock,
			});
		}

		if (thank_you_page_id) {
			await verifyPage({
				id: thank_you_page_id,
				type: 'user_registration_thank_you_page_id',
				lockKey: 'ur-thank-you-page-lock',
				isLockedRef: thankYouLock,
			});
		}
	};

	const onGroupTypeChange = (id) => {
		setAttributes({type: id});
		setIsBlockList('block' === id);
	};
	const onButtonTextChange = (val) => {
		setAttributes({button_text: val});
	};
	// Handle block removal (cleanup)
	useEffect(() => {
		return () => {
			if (redirectionLock.current) {
				wp.data.dispatch('core/editor').unlockPostSaving('ur-redirection-page-lock');
				redirectionLock.current = false;
			}

			if (thankYouLock.current) {
				wp.data.dispatch('core/editor').unlockPostSaving('ur-thank-you-page-lock');
				thankYouLock.current = false;
			}
		};
	}, []);

	// Fetch data on component mount
	useEffect(() => {
		fetchData();
	}, []);

	// Verify pages on component mount
	useEffect(() => {
		verifyPagesOnLoad();
	}, []);

	// Render the component
	return (
		<>
			<Box {...useProps}>
				<InspectorControls key="ur-gutenberg-membership-listing-inspector-controls">
					<PanelBody initialOpen={false} title={__('Redirection Page settings', 'user-registration')}>
						<SelectControl
							key="ur-gutenberg-registration-form-id"
							value={redirection_page_id}
							label={__('Set Redirection Page', 'user-registration')}
							options={[
								{label: __('Select a page', 'user-registration'), value: ''},
								...mapOptions(redirectionPageList),
							]}
							onChange={(id) =>
								setAttributes({redirection_page_id: id}) ||
								verifyPage({
									id,
									type: 'user_registration_member_registration_page_id',
									lockKey: 'ur-redirection-page-lock',
									isLockedRef: redirectionLock,
								})
							}
							__nextHasNoMarginBottom={true}
							__next40pxDefaultSize
						/>
						<SelectControl
							key="ur-gutenberg-thank-you-page-id"
							value={thank_you_page_id}
							label={__('Set Thank You Page', 'user-registration')}
							options={[
								{label: __('Select thank you page', 'user-registration'), value: ''},
								...mapOptions(thankYouPageList),
							]}
							onChange={(id) =>
								setAttributes({thank_you_page_id: id}) ||
								verifyPage({
									id,
									type: 'user_registration_thank_you_page_id',
									lockKey: 'ur-thank-you-page-lock',
									isLockedRef: thankYouLock,
								})
							}
							__nextHasNoMarginBottom={true}
							__next40pxDefaultSize
						/>
						{isLoading && (
							<div style={{textAlign: 'center', margin: '10px 0'}}>
								<Spinner/>
							</div>
						)}
						{error && (
							<Notice status="error" isDismissible={false}>
								{error}
							</Notice>
						)}
						{!error && success && (
							<Notice status="success" isDismissible={false}>
								{__('Page\'s verified successfully!', 'user-registration')}
							</Notice>
						)}
					</PanelBody>
					<PanelBody title={__('Group Settings', 'user-registration')}>

						<SelectControl
							key="ur-gutenberg-group-id"
							value={group_id}
							label={__('Set Group', 'user-registration')}
							options={[
								{label: __('Select a Group', 'user-registration'), value: ''},
								...mapOptions(groupList),
							]}
							onChange={(id) => setAttributes({group_id: id})}
							__nextHasNoMarginBottom={true}
							__next40pxDefaultSize
						/>
						<SelectControl
							key="ur-gutenberg-group-type-id"
							value={type}
							label={__('Set Group Type', 'user-registration')}
							options={[
								{label: __('Select a Group Type', 'user-registration'), value: ''},
								{label: __('List', 'user-registration'), value: 'list'},
								{label: __('Block', 'user-registration'), value: 'block'},
							]}
							onChange={onGroupTypeChange}
							__nextHasNoMarginBottom={true}
							__next40pxDefaultSize
						/>
					</PanelBody>

					<PanelBody title={__('Content Settings', 'user-registration')}>
						<TextControl
							key="ur-gutenberg-button-text"
							label={__("Button Text", "user-registration")}
							value={button_text}
							onChange={onButtonTextChange}
						/>
					</PanelBody>

				</InspectorControls>
				<Disabled>
					<wp.serverSideRender
						key="ur-gutenberg-membership-listing-server-side-renderer"
						block={blockName}
						attributes={props.attributes}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
