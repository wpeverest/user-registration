import {
	Box,
	Container,
	Grid,
	HStack,
	Heading,
	Link,
	Stack,
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useCallback, useEffect } from "react";
import { BiBulb } from "react-icons/bi";
import * as URIcon from "../../components/Icon/Icon";

// Import new components
import {
	DefaultFormMissing,
	RequiredPagesMissing,
	PaymentSetup,
	SendTestEmail,
	DefaultWordPressLogin,
	SpamProtection
} from "./components";

const PRIMARY_COLOR = "#475bb2";
const docURL = "https://docs.wpuserregistration.com/";
const facebookGroup = "https://www.facebook.com/groups/userregistration";
const featureRequestURL = "https://wpuserregistration.com/feature-requests/";
const submitReviewUrl =
	"https://wordpress.org/support/plugin/user-registration/reviews/?rate=5#new-post";
const ticketUrl =
	"https://wordpress.org/support/plugin/user-registration/#new-topic-0";

const SiteAssistant = () => {
	const [open, setOpen] = useState({
		defaultForm: true,
		requiredPages: true,
		paymentSetup: true,
		sendTestEmail: true,
		defaultWordPressLogin: true,
		spamProtection: true
	});

	const toggleOpen = useCallback((id) => {
		setOpen((prev) => ({ ...prev, [id]: !prev[id] }));
	}, []);

	// Check if default form exists
	const hasDefaultForm =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.has_default_form;

	// Get missing pages data from site_assistant_data
	const missingPagesData =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.missing_pages
			? _UR_DASHBOARD_.site_assistant_data.missing_pages
			: [];

	// Check if test email was already sent successfully
	const initialTestEmailSent =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.test_email_sent;

	// State to track if test email was sent during this session
	const [testEmailSent, setTestEmailSent] = useState(initialTestEmailSent);

	// Check if default WordPress login has been handled (disabled or skipped)
	const initialWordPressLoginHandled =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.wordpress_login_handled;

	// State to track if WordPress login was handled during this session
	const [wordPressLoginHandled, setWordPressLoginHandled] = useState(
		initialWordPressLoginHandled
	);

	// Check if spam protection has been handled (configured or skipped)
	const initialSpamProtectionHandled =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.spam_protection_handled;

	// State to track if spam protection was handled during this session
	const [spamProtectionHandled, setSpamProtectionHandled] = useState(
		initialSpamProtectionHandled
	);

	// Check if payment setup is handled (all payments connected or explicitly skipped)
	const paymentConnections =
		(typeof _UR_DASHBOARD_ !== "undefined" &&
			_UR_DASHBOARD_.site_assistant_data &&
			_UR_DASHBOARD_.site_assistant_data.payment_connections) ||
		{};

	const allPaymentsConnected = Object.values(paymentConnections).every(
		(connection) => connection.is_connected
	);
	const isPaymentSetupSkipped =
		typeof _UR_DASHBOARD_ !== "undefined" &&
		_UR_DASHBOARD_.site_assistant_data &&
		_UR_DASHBOARD_.site_assistant_data.payment_setup_handled === true &&
		!allPaymentsConnected;

	const initialPaymentSetupHandled =
		allPaymentsConnected || isPaymentSetupSkipped;

	// State to track if payment setup was handled during this session
	const [paymentSetupHandled, setPaymentSetupHandled] = useState(
		initialPaymentSetupHandled
	);

	// State to track if all components are completed
	const [allCompleted, setAllCompleted] = useState(false);

	// Callback to handle when test email is sent successfully
	const handleTestEmailSent = useCallback(() => {
		setTestEmailSent(true);
	}, []);

	// Callback to handle when WordPress login is handled (disabled or skipped)
	const handleWordPressLoginHandled = useCallback(() => {
		setWordPressLoginHandled(true);
	}, []);

	// Callback to handle when spam protection is handled (skipped)
	const handleSpamProtectionHandled = useCallback(() => {
		setSpamProtectionHandled(true);
	}, []);

	// Callback to handle when payment setup is handled (any payment connected or skipped)
	const handlePaymentSetupHandled = useCallback(() => {
		setPaymentSetupHandled(true);
	}, []);

	// Check if all components are completed and redirect if so
	useEffect(() => {
		// Check if all components are handled
		const allComponentsHandled =
			hasDefaultForm &&
			missingPagesData.length === 0 &&
			testEmailSent &&
			wordPressLoginHandled &&
			spamProtectionHandled &&
			paymentSetupHandled;

		// If all components are handled, show completion message and redirect
		if (allComponentsHandled && !allCompleted) {
			setAllCompleted(true);
			// Small delay to allow user to see the completion message
			setTimeout(() => {
				window.location.href =
					window._UR_DASHBOARD_?.adminURL +
					"admin.php?page=user-registration";
			}, 2000);
		}
	}, [
		hasDefaultForm,
		missingPagesData.length,
		testEmailSent,
		wordPressLoginHandled,
		spamProtectionHandled,
		paymentSetupHandled,
		allCompleted
	]);

	return (
		<Container maxW="100%" py={5}>
			<Stack align={"flex-start"} gap={4} mb={8}>
				<Heading
					as="h3"
					fontSize="2xl"
					fontWeight="bold"
					color="gray.800"
					mt={0}
				>
					{__("Welcome to User Registration & Membership", "user-registration")}
				</Heading>
				<Text fontSize={"md"} fontWeight={"light"}>
					{__(
						"Let's get your user registration system set up and ready to go!",
						"user-registration"
					)}
				</Text>
			</Stack>
			<Grid
				gridGap="5"
				gridTemplateColumns={{
					sm: "1fr",
					md: "2fr 2fr",
					lg: "3fr 2fr",
					xl: "3fr 1fr"
				}}
			>
				{allCompleted && (
					<Box
						p="4"
						bgColor="transparent"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
						textAlign="center"
					>
						<Heading
							as="h3"
							fontSize="md"
							fontWeight="semibold"
							color="green.600"
							mb="1"
						>
							{__("Setup Complete!", "user-registration")}
						</Heading>

						<Text
							fontSize="xs"
							color="gray.600"
						>
							{__(
								"Redirecting to All forms...",
								"user-registration"
							)}
						</Text>
					</Box>
				)}
				{!allCompleted && (
					<Stack gap="5">
						{/* Default Form Missing - only show if no default form exists */}
						{!hasDefaultForm && (
							<DefaultFormMissing
								isOpen={open.defaultForm}
								onToggle={() => toggleOpen("defaultForm")}
							/>
						)}

						{/* Required Pages Missing - only show if there are missing pages */}
						{missingPagesData.length > 0 && (
							<RequiredPagesMissing
								isOpen={open.requiredPages}
								onToggle={() => toggleOpen("requiredPages")}
								missingPagesData={missingPagesData}
							/>
						)}

						{/* Payment Setup - only show if not handled */}
						{!paymentSetupHandled && (
							<PaymentSetup
								isOpen={open.paymentSetup}
								onToggle={() => toggleOpen("paymentSetup")}
								onSkipped={handlePaymentSetupHandled}
							/>
						)}

						{/* Send Test Email - only show if test email hasn't been sent successfully */}
						{!testEmailSent && (
							<SendTestEmail
								isOpen={open.sendTestEmail}
								onToggle={() => toggleOpen("sendTestEmail")}
								onEmailSent={handleTestEmailSent}
							/>
						)}

						{/* Default WordPress Login - only show if not handled */}
						{!wordPressLoginHandled && (
							<DefaultWordPressLogin
								isOpen={open.defaultWordPressLogin}
								onToggle={() =>
									toggleOpen("defaultWordPressLogin")
								}
								onHandled={handleWordPressLoginHandled}
							/>
						)}

						{/* Spam Protection - only show if not handled */}
						{!spamProtectionHandled && (
							<SpamProtection
								isOpen={open.spamProtection}
								onToggle={() => toggleOpen("spamProtection")}
								onSkipped={handleSpamProtectionHandled}
							/>
						)}
					</Stack>
				)}
				<Stack gap="5">
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<URIcon.Team w="5" h="5" fill={PRIMARY_COLOR} />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__(
									"User Registration Community",
									"user-registration"
								)}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Join our exclusive group and connect with fellow User Registration members. Ask questions, contribute to discussions, and share feedback!",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							href={facebookGroup}
							isExternal
						>
							{__("Join our Facebook Group", "user-registration")}
						</Link>
					</Stack>
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<URIcon.DocsLines
								w="5"
								h="5"
								fill={PRIMARY_COLOR}
							/>
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Getting Started", "user-registration")}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Check our documentation for detailed information on User Registration features and how to use them.",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							href={docURL}
							isExternal
						>
							{__("View Documentation", "user-registration")}
						</Link>
					</Stack>
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<URIcon.Headphones
								w="5"
								h="5"
								fill={PRIMARY_COLOR}
							/>
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Support", "user-registration")}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Submit a ticket for encountered issues and get help from our support team instantly.",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							href={ticketUrl}
							isExternal
						>
							{__("Create a Ticket", "user-registration")}
						</Link>
					</Stack>
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<BiBulb size={20} color={PRIMARY_COLOR} />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Feature Request", "user-registration")}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Don't find a feature you're looking for? Suggest any features you think would enhance our product.",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							href={featureRequestURL}
							isExternal
						>
							{__("Request a Feature", "user-registration")}
						</Link>
					</Stack>
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<URIcon.Star w="5" h="5" fill={PRIMARY_COLOR} />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Submit a Review", "user-registration")}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Please take a moment to give us a review. We appreciate honest feedback that'll help us improve our plugin.",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							href={submitReviewUrl}
							isExternal
						>
							{__("Submit a Review", "user-registration")}
						</Link>
					</Stack>
					<Stack
						p="6"
						gap="3"
						bgColor="white"
						borderRadius="base"
						border="1px"
						borderColor="gray.100"
					>
						<HStack gap="2">
							<URIcon.Video w="5" h="5" fill={PRIMARY_COLOR} />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Video Tutorials", "user-registration")}
							</Heading>
						</HStack>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Watch our step-by-step video tutorials that'll help you get the best out of User Registration's features.",
								"user-registration"
							)}
						</Text>
						<Link
							color="var(--chakra-colors-primary-500) !important"
							textDecor="underline"
							isExternal
							href="https://www.youtube.com/@UserRegistration"
						>
							{__("Watch Videos", "user-registration")}
						</Link>
					</Stack>
				</Stack>
			</Grid>
		</Container>
	);
};

export default SiteAssistant;
