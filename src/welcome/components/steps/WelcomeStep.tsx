import React from "react";
import {
	Box,
	Text,
	Heading,
	VStack,
	HStack,
	Radio,
	RadioGroup,
	Checkbox,
	Link,
	useColorModeValue,
} from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { useStateValue } from "../../context/StateProvider";
import { MembershipSetupType } from "../../context/Gettingstartedcontext";

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
	isSelected,
}) => {
	const selectedBg = useColorModeValue("blue.50", "blue.900");
	const selectedBorder = "#475BD8";
	const defaultBg = useColorModeValue("white", "gray.800");
	const defaultBorder = useColorModeValue("gray.200", "gray.600");
	const titleColor = useColorModeValue("gray.800", "white");
	const descColor = useColorModeValue("gray.600", "gray.400");
	const hoverBg = useColorModeValue("gray.50", "gray.700");

	return (
		<Box
			as="label"
			cursor="pointer"
			w="100%"
			p={4}
			borderRadius="lg"
			borderWidth="2px"
			borderColor={isSelected ? selectedBorder : defaultBorder}
			bg={isSelected ? selectedBg : defaultBg}
			transition="all 0.2s ease"
			_hover={{
				borderColor: isSelected ? selectedBorder : "gray.300",
				bg: isSelected ? selectedBg : hoverBg,
			}}
		>
			<HStack align="flex-start" spacing={3}>
				<Radio
					value={value}
					colorScheme="blue"
					mt={0.5}
					sx={{
						"&[data-checked]": {
							bg: "#475BD8",
							borderColor: "#475BD8",
						},
					}}
				/>
				<VStack align="flex-start" spacing={0.5}>
					<Text fontWeight="600" color={titleColor} fontSize="md">
						{title}
					</Text>
					<Text fontSize="sm" color={descColor}>
						{description}
					</Text>
				</VStack>
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
	const { membershipSetupType, allowTracking, adminEmail, membershipOptions } =
		state;

	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const linkColor = "#475BD8";

	const handleMembershipChange = (value: MembershipSetupType) => {
		dispatch({ type: "SET_MEMBERSHIP_SETUP_TYPE", payload: value });
	};

	const handleTrackingChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		dispatch({ type: "SET_ALLOW_TRACKING", payload: e.target.checked });
	};

	const emailForDisplay = adminEmail || "admin@example.com";

	const optionsToRender =
		membershipOptions && membershipOptions.length > 0
			? membershipOptions.map((opt) => ({
					value: mapApiValueToSetupType(opt.value),
					title: opt.label,
					description: opt.description,
			  }))
			: [
					{
						value: "paid" as MembershipSetupType,
						title: __("Paid Membership", "urm"),
						description: __(
							"Paid members can access protected content. Choose this even if you have combination of both free and paid.",
							"urm"
						),
					},
					{
						value: "free" as MembershipSetupType,
						title: __("Free Membership", "urm"),
						description: __(
							"Registered users can access protected content.",
							"urm"
						),
					},
					{
						value: "other" as MembershipSetupType,
						title: __("Other URM Features (no membership now)", "urm"),
						description: __(
							"I want registration and other features without membership.",
							"urm"
						),
					},
			  ];

	return (
		<>
			<VStack align="flex-start" spacing={2} mb={8}>
				<Heading
					fontFamily="Inter"
					fontWeight={600}
					fontSize="21px"
					lineHeight="34px"
					letterSpacing="-0.01em"
					color={textColor}
				>
					{__("Welcome 🙂", "urm")}
				</Heading>
				<Text color={textColor} fontWeight="500">
					{__("Thanks for choosing URM!", "urm")}
				</Text>
				<Text color={mutedColor} fontSize="sm">
					{__(
						"Select what applies to your site. We'll create the pages and features you need. You can always change this later.",
						"urm"
					)}
				</Text>
			</VStack>

			<Box mb={10}>
				<Text fontWeight="600" color={textColor} mb={4}>
					{__("I want to setup", "urm")}
				</Text>
				<RadioGroup
					value={membershipSetupType}
					onChange={handleMembershipChange as any}
				>
					<VStack spacing={3} align="stretch">
						{optionsToRender.map((option) => (
							<MembershipOption
								key={option.value}
								value={option.value}
								title={option.title}
								description={option.description}
								isSelected={membershipSetupType === option.value}
							/>
						))}
					</VStack>
				</RadioGroup>
			</Box>

			<Box>
				<Checkbox
					isChecked={allowTracking}
					onChange={handleTrackingChange}
					colorScheme="blue"
					sx={{
						".chakra-checkbox__control[data-checked]": {
							bg: "#475BD8",
							borderColor: "#475BD8",
						},
					}}
				>
					<Text fontSize="sm" color={mutedColor}>
						{__(
							"Allow usage tracking and subscribe to updates (security updates, new features, and occasional offers). We will use your email if provided.",
							"urm"
						)}{" "}
						<Link color={linkColor} href={`mailto:${emailForDisplay}`}>
							{emailForDisplay}
						</Link>
					</Text>
				</Checkbox>
			</Box>
		</>
	);
};

export default WelcomeStep;
