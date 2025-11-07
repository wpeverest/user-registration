import {
	Button,
	Collapse,
	HStack,
	Heading,
	Icon,
	IconButton,
	Stack,
	Text,
	Flex,
	Link,
	Box,
	useToast
} from '@chakra-ui/react';
import { __ } from '@wordpress/i18n';
import React, { useState } from 'react';
import { BiChevronDown, BiChevronUp } from 'react-icons/bi';

const SpamProtection = ({ isOpen, onToggle, onSkipped }) => {
	const [isSkipping, setIsSkipping] = useState(false);
	const toast = useToast();

	const handleConfigureRecaptcha = () => {
		const settingsURL = window._UR_DASHBOARD_?.settingsURL || `${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(`${settingsURL}&tab=captcha&method=v2`, '_blank');
	};

	const handleOtherSpamFeatures = () => {
		const settingsURL = window._UR_DASHBOARD_?.settingsURL || `${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(`${settingsURL}&tab=captcha`, '_blank');
	};

	const handleSkip = async () => {
		setIsSkipping(true);

		try {
			const adminURL = window._UR_DASHBOARD_?.adminURL || window.location.origin + '/wp-admin';
			const response = await fetch(`${adminURL}admin-ajax.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'user_registration_skip_site_assistant_section',
					section: 'spam_protection',
					security: window._UR_DASHBOARD_?.urRestApiNonce || '',
				}),
			});

			const result = await response.json();

			if (result.success) {
				toast({
					title: __('Skipped', 'user-registration'),
					description: result.data?.message || __('Spam protection setting has been acknowledged and skipped.', 'user-registration'),
					status: 'success',
					duration: 3000,
					isClosable: true,
				});
				// Notify parent component to hide this section
				if (onSkipped) {
					onSkipped();
				}
			} else {
				throw new Error(result.data?.message || 'Failed to skip settings');
			}
		} catch (error) {
			toast({
				title: __('Error', 'user-registration'),
				description: error.message || __('Failed to skip settings. Please try again.', 'user-registration'),
				status: 'error',
				duration: 3000,
				isClosable: true,
			});
		} finally {
			setIsSkipping(false);
		}
	};

	return (
		<Stack
			p="6"
			gap="5"
			bgColor="white"
			borderRadius="base"
			border="1px"
			borderColor="gray.100"
		>
			<HStack justify={'space-between'}>
				<Heading as="h3" size="md" fontWeight="semibold">
					{__('Spam Protection', 'user-registration')}
				</Heading>
				<IconButton
					aria-label={'spamProtection'}
					icon={
						<Icon
							as={isOpen ? BiChevronUp : BiChevronDown}
							fontSize="2xl"
							fill={isOpen ? 'primary.500' : 'black'}
						/>
					}
					cursor={'pointer'}
					fontSize={'xl'}
					onClick={onToggle}
					size="sm"
					boxShadow="none"
					borderRadius="base"
					variant={isOpen ? 'solid' : 'link'}
					border="none"
				/>
			</HStack>
			<Collapse in={isOpen}>
				<Stack gap={5}>
					<Text fontWeight={'light'} fontSize={'md'}>
						{__(
							'Set up protection against spam registrations. We recommend enabling reCaptcha v2.',
							'user-registration',
						)}
					</Text>

					<Flex
						bg="#f9fafc"
						p="4"
						borderRadius="md"
						justify="space-between"
						align="center">
						<Box>
							<Text
								fontSize="sm"
								fontWeight="bold"
								mb={1}
								className="ur-recaptcha-bold"
								sx={{
									fontWeight: 'bold',
									'&.ur-recaptcha-bold': {
										fontWeight: 'bold'
									}
								}}
							>
								{__('reCaptcha v2', 'user-registration')}
							</Text>
							<Text fontSize="xs" color="gray.600">
								{__('Enable Google reCaptcha protection', 'user-registration')}
							</Text>
						</Box>
						<Link
							color="primary.500"
							fontSize="sm"
							textDecoration="underline"
							onClick={handleConfigureRecaptcha}
							cursor="pointer"
						>
							{__('Configure Settings', 'user-registration')}
						</Link>
					</Flex>

					<Text fontSize="sm" color="gray.600">
						{__('You can also set up other spam protection features from ', 'user-registration')}
						<Link
							color="primary.500"
							textDecoration="underline"
							onClick={handleOtherSpamFeatures}
							cursor="pointer"
						>
							{__('here', 'user-registration')}
						</Link>
						.
					</Text>

					<Link
						color="primary.500"
						fontSize="sm"
						textDecoration="underline"
						onClick={handleSkip}
						cursor="pointer"
						width="fit-content"
						opacity={isSkipping ? 0.6 : 1}
						pointerEvents={isSkipping ? 'none' : 'auto'}
					>
						{isSkipping ? __('Skipping...', 'user-registration') : __('I acknowledge and skip', 'user-registration')}
					</Link>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default SpamProtection;
