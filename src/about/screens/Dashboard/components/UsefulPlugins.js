import {
	AlertDialog,
	AlertDialogBody,
	AlertDialogContent,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogOverlay,
	Button,
	Grid,
	Heading,
	HStack,
	Stack,
	Text,
	useDisclosure,
} from "@chakra-ui/react";
import { sprintf, __ } from "@wordpress/i18n";
import React, { useRef } from "react";
import { PLUGINS } from "../../../constants/products";
// import usePluginInstallActivate from "../../../hooks/usePluginInstallActivate";

const Plugin = ({ plugin, index, pluginsStatus }) => {
	const { isOpen, onOpen, onClose } = useDisclosure();
	// const { installPlugin, activatePlugin, performPluginAction } =
	// 	usePluginInstallActivate({
	// 		successCallback: onClose,
	// 		errorCallback: onClose,
	// 		pluginsStatus,
	// 	});
	const { installPlugin, activatePlugin, performPluginAction } = {};
	const cancelRef = useRef;

	return (
		<HStack
			key={plugin.slug}
			gap="4px"
			justify="space-between"
			px="16px"
			py="18px"
			pl={index % 2 === 0 ? "0" : undefined}
		>
			<HStack>
				<plugin.logo w="40px" h="40px" />
				<Stack gap="6px">
					<Heading as="h4" fontSize="14px" fontWeight="semibold">
						{plugin.label}
					</Heading>
					<Text as="span" color="gray.500">
						{plugin.shortDescription}
					</Text>
				</Stack>
			</HStack>
			<Button
				variant="link"
				colorScheme="primary"
				color="var(--chakra-colors-primary-500) !important"
				fontSize="14px"
				fontWeight="normal"
				textDecor="underline"
				// isLoading={activatePlugin.isLoading || installPlugin.isLoading}
				isDisabled={"active" === pluginsStatus[plugin.slug]}
				onClick={onOpen}
			>
				{pluginsStatus[plugin.slug] === "active"
					? __("Active", "blockart")
					: pluginsStatus[plugin.slug] === "inactive"
					? __("Activate", "blockart")
					: __("Install", "blockart")}
			</Button>
			<AlertDialog
				isOpen={isOpen}
				leastDestructiveRef={cancelRef}
				onClose={onClose}
				isCentered
			>
				<AlertDialogOverlay>
					<AlertDialogContent>
						<AlertDialogHeader fontSize="lg" fontWeight="semibold">
							{"inactive" === pluginsStatus[plugin.slug]
								? __("Activate Plugin", "blockart")
								: __("Install Plugin", "blockart")}
						</AlertDialogHeader>
						<AlertDialogBody>
							{"inactive" === pluginsStatus[plugin.slug]
								? sprintf(
										__(
											"Are you sure? You want to activate %s plugin.",
											"blockart"
										),
										plugin.label
								  )
								: sprintf(
										__(
											"Are you sure? You want to install and activate %s plugin.",
											"blockart"
										),
										plugin.label
								  )}
						</AlertDialogBody>
						<AlertDialogFooter>
							<Button
								size="sm"
								fontSize="xs"
								fontWeight="normal"
								variant="outline"
								colorScheme="primary"
								// isDisabled={
								// 	activatePlugin.isLoading ||
								// 	installPlugin.isLoading
								// }
								ref={cancelRef}
								onClick={onClose}
							>
								{__("Cancel", "blockart")}
							</Button>
							<Button
								size="sm"
								fontSize="xs"
								fontWeight="normal"
								colorScheme="primary"
								onClick={() => performPluginAction(plugin.slug)}
								ml={3}
								// isLoading={
								// 	activatePlugin.isLoading ||
								// 	installPlugin.isLoading
								// }
							>
								{"inactive" === pluginsStatus[plugin.slug]
									? __("Activate", "blockart")
									: __("Install", "blockart")}
							</Button>
						</AlertDialogFooter>
					</AlertDialogContent>
				</AlertDialogOverlay>
			</AlertDialog>
		</HStack>
	);
};

export const UsefulPlugins = () => {
	const pluginsStatus = {};
	return (
		<Grid
			gridTemplateColumns="1fr 1fr"
			sx={{
				"> div:nth-child(2n+1)": {
					borderRight: "1px",
					borderColor: "gray.100",
				},
				"> div:first-child, > div:nth-child(2)": {
					borderBottom: "1px",
					borderColor: "gray.100",
				},
			}}
		>
			{PLUGINS.map((plugin, i) => (
				<Plugin
					key={plugin.slug}
					pluginsStatus={pluginsStatus}
					plugin={plugin}
					index={i}
				/>
			))}
		</Grid>
	);
};

export default UsefulPlugins;
