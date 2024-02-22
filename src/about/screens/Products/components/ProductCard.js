import React, { useRef } from "react";
import {
	AlertDialog,
	AlertDialogBody,
	AlertDialogContent,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogOverlay,
	Box,
	Button,
	Divider,
	Heading,
	HStack,
	Image,
	Link,
	Stack,
	Text,
	useDisclosure,
} from "@chakra-ui/react";
import { sprintf, __ } from "@wordpress/i18n";
import { PLUGINS } from "../../../constants/products";
// import usePluginInstallActivate from "../../../hooks/usePluginInstallActivate";

// const [{ pluginsStatus, themesStatus }, dispatch] = useStateValue();
// type Props = Prettify<
//   Omit<typeof PLUGINS[number], 'demo' | 'logo' | 'shortDescription'> & {
//     demo?: string;
//     logo?: React.ReactNode | React.ElementType;
//     shortDescription?: string;
//     pluginsStatus: pluginsStatus ;
//     themesStatus: themesStatus;
//   }
// >;

const ProductCard = (props) => {
	const {
		label,
		description,
		image,
		website,
		pluginsStatus,
		slug,
		type,
		themesStatus,
	} = props;
	const { isOpen, onOpen, onClose } = useDisclosure();
	const { installPlugin, activatePlugin, performPluginAction } = {};

	const cancelRef = useRef;

	const status = type === "theme" ? themesStatus[slug] : pluginsStatus[slug];

	return (
		<>
			<Box
				overflow="hidden"
				boxShadow="none"
				border="1px"
				borderRadius="base"
				borderColor="gray.100"
				display="flex"
				flexDir="column"
			>
				<Box p="0" flex="1 1 0%">
					<Image w="full" src={image} />
					<Stack gap="2" px="4" py="5">
						<Heading
							as="h3"
							size="md"
							m="0"
							fontSize="md"
							fontWeight="semibold"
						>
							{label}
						</Heading>
						<Text m="0" color="gray.600" fontSize="13px">
							{description}
						</Text>
					</Stack>
				</Box>
				<Divider color="gray.300" />
				<Box
					px="4"
					py="5"
					justifyContent="space-between"
					alignItems="center"
					display="flex"
				>
					<HStack gap="1" align="center">
						<Link
							href={website}
							fontSize="xs"
							color="gray.500"
							textDecoration="underline"
							isExternal
						>
							{__("Learn More", "blockart")}
						</Link>
						<Text as="span" lineHeight="1" color="gray.500">
							|
						</Text>
						<Link
							href={website}
							fontSize="xs"
							color="gray.500"
							textDecoration="underline"
							isExternal
						>
							{__("Live Demo", "blockart")}
						</Link>
					</HStack>
					<Button
						colorScheme="primary"
						size="sm"
						fontSize="xs"
						borderRadius="base"
						fontWeight="semibold"
						_hover={{
							color: "white",
							textDecoration: "none",
						}}
						_focus={{
							color: "white",
							textDecoration: "none",
						}}
						isDisabled={"active" === status}
						as={"theme" === type ? Link : undefined}
						// href={
						// 	"theme" === type
						// 		? "inactive" === status
						// 			? `${localized.adminUrl}themes.php?search=${slug}`
						// 			: `${localized.adminUrl}/theme-install.php?search=${slug}`
						// 		: undefined
						// }
						onClick={"plugin" === type ? onOpen : undefined}
						// isLoading={
						// 	"plugin" === type
						// 		? activatePlugin.isLoading ||
						// 		  installPlugin.isLoading
						// 		: undefined
						// }
					>
						{"active" === status
							? __("Active", "blockart")
							: "inactive" === status
							? __("Activate", "blockart")
							: __("Install", "blockart")}
					</Button>
				</Box>
			</Box>
			{type === "plugin" && (
				<AlertDialog
					isOpen={isOpen}
					leastDestructiveRef={cancelRef}
					onClose={onClose}
					isCentered
				>
					{/* ... rest of the code for AlertDialog */}
				</AlertDialog>
			)}
		</>
	);
};

export default ProductCard;
