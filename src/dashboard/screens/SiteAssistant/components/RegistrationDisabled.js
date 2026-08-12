import {
	Box,
	Collapse,
	Flex,
	HStack,
	Heading,
	Icon,
	IconButton,
	Link,
	Stack,
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const RegistrationDisabled = ({ isOpen, onToggle, numbering }) => {
	const handleConfigureGeneralSettings = () => {
		const adminURL =
			window._UR_DASHBOARD_?.adminURL ||
			`${window.location.origin}/wp-admin/`;
		window.open(
			`${adminURL}options-general.php#users_can_register`,
			"_blank"
		);
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
			<HStack
				justify={"space-between"}
				onClick={onToggle}
				borderBottom={isOpen && "1px solid #dcdcde"}
				paddingBottom={isOpen && 5}
				_hover={{
					cursor: "pointer"
				}}
			>
				<Heading
					as="h3"
					fontSize="18px"
					fontWeight="semibold"
					lineHeight={"1.2"}
				>
					{numbering +
						") " +
						__("Registration Is Disabled", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"registrationDisabled"}
					icon={
						<Icon
							as={isOpen ? BiChevronUp : BiChevronDown}
							fontSize="2xl"
							fill={isOpen ? "primary.500" : "black"}
						/>
					}
					cursor={"pointer"}
					fontSize={"xl"}
					size="sm"
					boxShadow="none"
					borderRadius="base"
					variant={isOpen ? "solid" : "link"}
					border="none"
				/>
			</HStack>
			<Collapse in={isOpen}>
				<Stack gap={5}>
					<Text fontWeight={"light"} fontSize={"15px !important"}>
						{__(
							'Your registration forms show "Registration is currently disabled." because the WordPress "Anyone can register" option is turned off.',
							"user-registration"
						)}
					</Text>

					<Flex
						bg="#f9fafc"
						p="4"
						borderRadius="md"
						justify="space-between"
						align="center"
					>
						<Box>
							<Text
								fontSize={"15px !important"}
								fontWeight="bold"
								mb={1}
							>
								{__("Anyone can register", "user-registration")}
							</Text>
							<Text fontSize="14px" color="gray.600">
								{__(
									"Enable membership registration in WordPress General Settings",
									"user-registration"
								)}
							</Text>
						</Box>
						<Link
							color="primary.500"
							textDecoration="underline"
							onClick={handleConfigureGeneralSettings}
							cursor="pointer"
						>
							{__("Configure Settings", "user-registration")}
						</Link>
					</Flex>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default RegistrationDisabled;
