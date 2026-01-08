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
import { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const MembershipField = ({ isOpen, onToggle, numbering, onHandled }) => {
	const [isLoading, setIsLoading] = useState(false);
	const [isSkipping, setIsSkipping] = useState(false);
	const toast = useToast();

	const handleAddMembershipField = async () => {
		setIsLoading(true);

		try {
			const adminURL =
				window._UR_DASHBOARD_?.adminURL ||
				window.location.origin + "/wp-admin/";

			const response = await fetch(`${adminURL}admin-ajax.php`, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "user_registration_create_default_form", // Same action!
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

				if (onHandled) {
					onHandled();
				}

				setTimeout(() => {
					window.location.reload();
				}, 1000);
			} else {
				throw new Error(
					result.data?.message || "Failed to add membership field"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to add membership field. Please try again.",
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

	const handleSkip = async () => {
		setIsSkipping(true);

		try {
			const adminURL =
				window._UR_DASHBOARD_?.adminURL ||
				window.location.origin + "/wp-admin/";

			const response = await fetch(`${adminURL}admin-ajax.php`, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "user_registration_skip_membership_field_setup",
					security: window._UR_DASHBOARD_?.urRestApiNonce || ""
				})
			});

			const result = await response.json();

			if (result.success) {
				toast({
					title: __("Skipped", "user-registration"),
					description: __(
						"Membership field setup skipped.",
						"user-registration"
					),
					status: "info",
					duration: 2000,
					isClosable: true
				});

				if (onHandled) {
					onHandled();
				}
			} else {
				throw new Error(result.data?.message || "Failed to skip");
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to skip membership field setup.",
						"user-registration"
					),
				status: "error",
				duration: 3000,
				isClosable: true
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
						__("Add Membership Field", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"membershipField"}
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
							"Your default registration form is missing a membership field. Add a membership field to allow users to select membership plans during registration.",
							"user-registration"
						)}
					</Text>

					<Button
						colorScheme={"primary"}
						rounded="base"
						width={"fit-content"}
						onClick={handleAddMembershipField}
						py={5}
						size={"sm"}
						fontSize="14px"
						isLoading={isLoading}
						loadingText={__("Adding...", "user-registration")}
					>
						{__("Add Membership Field", "user-registration")}
					</Button>

					{/* Skip Setup row - styled like the image */}
					<HStack
						justify="space-between"
						borderTop="1px solid"
						borderColor="gray.100"
						pt={4}
					>
						<Text fontSize="14px" color="gray.600">
							{__(
								"You can also add the membership field manually from the form builder.",
								"user-registration"
							)}
						</Text>
					</HStack>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default MembershipField;
