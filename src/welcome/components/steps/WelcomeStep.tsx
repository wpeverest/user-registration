import {
	Box,
	Checkbox,
	Heading,
	HStack,
	Input,
	Link,
	Radio,
	RadioGroup,
	Text,
	useColorModeValue,
	VStack
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";
import { MembershipSetupType } from "../../context/Gettingstartedcontext";
import { useStateValue } from "../../context/StateProvider";

interface MembershipOptionProps {
	value: MembershipSetupType;
	title: string;
	description: string;
	isSelected: boolean;
}

const MembershipOption: React.FC<MembershipOptionProps> = ({
	value,
	title,
	description,
	isSelected
}) => {
	const selectedBorder = "#475BB2";
	const defaultBorder = useColorModeValue("#F4F4F4", "gray.600");
	const titleColor = useColorModeValue("gray.800", "white");
	const descColor = useColorModeValue("gray.600", "gray.400");

	return (
		<Box
			as="label"
			cursor="pointer"
			w="100%"
			p={4}
			borderRadius="4px"
			borderWidth="2px"
			borderColor={isSelected ? selectedBorder : defaultBorder}
			bg={"transparent"}
			transition="all 0.2s ease"
		>
			<HStack align="flex-start" spacing={3}>
				<Radio
					value={value}
					colorScheme="blue"
					mt="3px"
					sx={{
						"&[data-checked]": {
							bg: "#475BB2",
							borderColor: "#475BB2"
						}
					}}
				/>
				<Box>
					<Text
						fontWeight="600"
						color={titleColor}
						fontSize="14px"
						lineHeight="20px"
					>
						{title}
					</Text>
					<Text
						fontSize="sm"
						color={descColor}
						mt={1}
						lineHeight="22px"
					>
						{description}
					</Text>
				</Box>
			</HStack>
		</Box>
	);
};

const mapApiValueToSetupType = (value: string): MembershipSetupType => {
	if (value === "paid_membership") return "paid";
	if (value === "free_membership") return "free";
	return "other";
};

const WelcomeStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const {
		membershipSetupType,
		allowTracking,
		adminEmail,
		membershipOptions
	} = state;

	const [isEditingEmail, setIsEditingEmail] = useState(false);
	const [tempEmail, setTempEmail] = useState(adminEmail);

	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const linkColor = "#475BB2";
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	const handleMembershipChange = (value: MembershipSetupType) => {
		dispatch({ type: "SET_MEMBERSHIP_SETUP_TYPE", payload: value });
	};

	const handleTrackingChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		dispatch({ type: "SET_ALLOW_TRACKING", payload: e.target.checked });
	};

	const handleChangeEmailClick = (e: React.MouseEvent) => {
		e.preventDefault();
		e.stopPropagation();
		setTempEmail(adminEmail);
		setIsEditingEmail(true);
	};

	const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		setTempEmail(e.target.value);
	};

	const handleEmailBlur = () => {
		if (tempEmail && tempEmail.includes("@")) {
			dispatch({ type: "SET_ADMIN_EMAIL", payload: tempEmail });
		}
		setIsEditingEmail(false);
	};

	const handleEmailKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
		if (e.key === "Enter") {
			handleEmailBlur();
		} else if (e.key === "Escape") {
			setTempEmail(adminEmail);
			setIsEditingEmail(false);
		}
	};

	const emailForDisplay = adminEmail || "admin@example.com";

	const optionsToRender =
		membershipOptions && membershipOptions.length > 0
			? membershipOptions.map((opt) => ({
					value: mapApiValueToSetupType(opt.value),
					title: opt.label,
					description: opt.description
			  }))
			: [
					{
						value: "paid" as MembershipSetupType,
						title: __("Paid Membership", "user-registration"),
						description: __(
							"Charge users to access premium content (you can offer free plans too).",
							"user-registration"
						)
					},
					{
						value: "free" as MembershipSetupType,
						title: __("Free Membership", "user-registration"),
						description: __(
							"Let users register for free and access members-only content.",
							"user-registration"
						)
					},
					{
						value: "other" as MembershipSetupType,
						title: __("Advanced Registration", "user-registration"),
						description: __(
							"Complete registration system to replace WordPress's basic signup. Custom signup fields, login & account pages, and user approval.",
							"user-registration"
						)
					}
			  ];

	return (
		<>
			<VStack align="flex-start" spacing={1} mb={8}>
				<Heading
					fontFamily="Inter"
					fontWeight={600}
					fontSize="21px"
					lineHeight="34px"
					letterSpacing="-0.01em"
					color={textColor}
				>
					{__("Welcome 🙂", "user-registration")}
				</Heading>
				<Text color={textColor} fontWeight="500" fontSize="14px">
					{__("Thanks for choosing URM!", "user-registration")}
				</Text>
				<Text color={mutedColor} fontSize="14px">
					{__(
						"Tell us what you want to do. We'll set up the right pages and features for your site. Don't worry—you can change this anytime.",
						"user-registration"
					)}
				</Text>
			</VStack>

			<Box mb={10}>
				<Text fontWeight="600" color={textColor} mb={4} fontSize="16px">
					{__("Choose your setup:", "user-registration")}
				</Text>
				<RadioGroup
					value={membershipSetupType}
					onChange={handleMembershipChange as any}
				>
					<VStack spacing={4} align="stretch">
						{optionsToRender.map((option) => (
							<MembershipOption
								key={option.value}
								value={option.value}
								title={option.title}
								description={option.description}
								isSelected={
									membershipSetupType === option.value
								}
							/>
						))}
					</VStack>
				</RadioGroup>
			</Box>

			<Box>
				<HStack align="flex-start" spacing="10px">
					<Box flexShrink={0} pt="2px">
						<Checkbox
							isChecked={allowTracking}
							onChange={handleTrackingChange}
							colorScheme="blue"
							sx={{
								".chakra-checkbox__control[data-checked]": {
									bg: "#475BB2",
									borderColor: "#475BB2"
								}
							}}
						/>
					</Box>
					<Box>
						<Text
							fontSize="sm"
							color={mutedColor}
							lineHeight="20px"
						>
							{__(
								"Share anonymous usage data to improve URM, plus receive updates and offers.",
								"user-registration"
							)}
						</Text>
						<Box mt={1}>
							{isEditingEmail ? (
								<Input
									value={tempEmail}
									onChange={handleEmailChange}
									onBlur={handleEmailBlur}
									onKeyDown={handleEmailKeyDown}
									size="sm"
									width="220px"
									bg={inputBg}
									borderColor={inputBorder}
									borderRadius="4px"
									autoFocus
									placeholder="Enter email address"
									_focus={{
										borderColor: "#475BB2",
										boxShadow: "0 0 0 1px #475BB2"
									}}
								/>
							) : (
								<Text
									fontSize="sm"
									color={mutedColor}
									lineHeight="20px"
								>
									{__("Email:", "user-registration")}{" "}
									<Link
										color={linkColor}
										href={`mailto:${emailForDisplay}`}
									>
										{emailForDisplay}
									</Link>
									{" · "}
									<Link
										onClick={handleChangeEmailClick}
										cursor="pointer"
										_hover={{
											color: linkColor,
											textDecoration: "underline"
										}}
									>
										{__("Change", "user-registration")}
									</Link>
								</Text>
							)}
						</Box>
					</Box>
				</HStack>
			</Box>
		</>
	);
};

export default WelcomeStep;
