import {
	Button,
	Collapse,
	HStack,
	Heading,
	Icon,
	IconButton,
	Stack,
	Text,
	Box,
	useToast
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const RequiredPagesMissing = ({
	isOpen,
	onToggle,
	missingPagesData = [],
	numbering
}) => {
	const [isLoading, setIsLoading] = useState(false);
	const toast = useToast();

	// Extract display names and option names from the consolidated data
	const missingPages = missingPagesData.map((page) => page.name);
	const missingPageOptions = missingPagesData.map((page) => page.option);

	const handleGeneratePages = async () => {
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
					action: "user_registration_generate_required_pages",
					security: window._UR_DASHBOARD_?.urRestApiNonce || "",
					missing_pages: JSON.stringify(missingPageOptions)
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
					result.data?.message || "Failed to generate pages"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to generate pages. Please try again.",
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
						__("Required Pages Missing", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"requiredPages"}
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
							"Some essential pages are missing. These pages are needed to make your website work correctly. The following pages need to be created:",
							"user-registration"
						)}
					</Text>
					<Box
						bg="blue.50"
						p="4"
						borderRadius="md"
						border="1px"
						borderColor="blue.200"
					>
						<Text fontSize={"15px !important"} color="blue.800">
							{missingPages.join(", ")}
						</Text>
					</Box>
					<Button
						colorScheme={"primary"}
						rounded="base"
						width={"fit-content"}
						fontSize="14px"
						onClick={handleGeneratePages}
						isLoading={isLoading}
						loadingText={__("Generating...", "user-registration")}
					>
						{__("Generate Pages", "user-registration")}
					</Button>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default RequiredPagesMissing;
