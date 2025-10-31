import {
	Button,
	Collapse,
	HStack,
	Heading,
	Icon,
	IconButton,
	Stack,
	Text,
	useToast
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const DefaultFormMissing = ({ isOpen, onToggle, numbering }) => {
	const [isLoading, setIsLoading] = useState(false);
	const toast = useToast();

	const handleCreateDefaultForm = async () => {
		setIsLoading(true);

		try {
			const adminURL =
				window._UR_DASHBOARD_?.adminURL ||
				window.location.origin + "/wp-admin";

			const response = await fetch(`${adminURL}admin-ajax.php`, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "user_registration_create_default_form",
					security: window._UR_DASHBOARD_?.urRestApiNonce || ""
				})
			});

			const result = await response.json();

			if (result.success) {
				toast({
					title: __("Success", "user-registration"),
					description: result.data.message,
					status: "success",
					duration: 3000,
					isClosable: true
				});

				window.location.reload();
			} else {
				throw new Error(
					result.data?.message || "Failed to create default form"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to create default form. Please try again.",
						"user-registration"
					),
				status: "error",
				duration: 3000,
				isClosable: true
			});
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<Stack
			p="6"
			gap="5"
			bgColor="white"
			borderRadius="base"
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
						__("Default Form Missing", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"defaultForm"}
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
							"To start using User Registration & Membership, you first need to create a registration form.",
							"user-registration"
						)}
					</Text>

					<Button
						colorScheme={"primary"}
						rounded="base"
						width={"fit-content"}
						onClick={handleCreateDefaultForm}
						py={5}
						size={"sm"}
						fontSize="14px"
						isLoading={isLoading}
						loadingText={__("Creating...", "user-registration")}
					>
						{__("Create Default Form", "user-registration")}
					</Button>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default DefaultFormMissing;
