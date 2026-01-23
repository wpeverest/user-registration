/**
 *  External Dependencies
 */
import React from "react";
import { useToast, Button } from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { sprintf, __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/dashboardContext";

const UsePluginInstallActivate = ({
	cancelRef,
	onClose,
	slug,
	isPluginStatusLoading,
	setIsPluginStatusLoading
}) => {
	const toast = useToast();
	const [{ pluginsStatus }, dispatch] = useStateValue();

	/* global _UR_DASHBOARD_ */
	const { urRestApiNonce } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	const successCallback = (closeFunction) => {
		if (typeof closeFunction === "function") {
			closeFunction();
		}
	};

	const errorCallback = (closeFunction) => {
		if (typeof closeFunction === "function") {
			closeFunction();
		}
	};

	const activatePlugin = async ({ slug, file }) => {
		setIsPluginStatusLoading(true);
		try {
			const data = await apiFetch({
				path: `wp/v2/plugins/${slug}`,
				method: "POST",
				headers: {
					"X-WP-Nonce": urRestApiNonce
				},
				data: {
					plugin: file.replace(".php", ""),
					status: "active"
				}
			});

			pluginsStatus[`${data.plugin}.php`] = data.status;
			dispatch({
				type: actionTypes.GET_PLUGINS_STATUS,
				pluginsStatus: pluginsStatus
			});

			toast({
				title: "Success",
				description: sprintf(
					__("%s plugin activated successfully", "user-registration"),
					data.name
				),
				status: "success",
				duration: 5000,
				isClosable: true
			});

			successCallback(onClose);
		} catch (e) {
			toast({
				title: "Error",
				description:
					e.message || __("An error occurred", "user-registration"),
				status: "error",
				duration: 5000,
				isClosable: true
			});

			errorCallback(onClose);
		} finally {
			setIsPluginStatusLoading(false);
			onClose();
		}
	};

	const installPlugin = async (slug) => {
		setIsPluginStatusLoading(true);

		try {
			const data = await apiFetch({
				path: "wp/v2/plugins",
				method: "POST",
				headers: {
					"X-WP-Nonce": urRestApiNonce
				},
				data: {
					slug: slug,
					status: "active"
				}
			});

			pluginsStatus[`${data.plugin}.php`] = data.status;
			dispatch({
				type: actionTypes.GET_PLUGINS_STATUS,
				pluginsStatus: pluginsStatus
			});
			toast({
				title: "Success",
				description: sprintf(
					__(
						"%s plugin installed and activated successfully",
						"user-registration"
					),
					data.name
				),
				status: "success",
				duration: 9000,
				isClosable: true
			});
			successCallback(onClose);
		} catch (e) {
			toast({
				title: "Error",
				description: e.message || "An error occurred",
				status: "error",
				duration: 9000,
				isClosable: true
			});
			errorCallback(onClose);
		} finally {
			setIsPluginStatusLoading(false);
		}
		onClose();
	};

	const performPluginAction = (slug) => {
		const pluginSlug = slug.split("/")[0];

		if (pluginsStatus[slug] === "not-installed") {
			installPlugin(pluginSlug);
		} else if (pluginsStatus[slug] === "inactive") {
			activatePlugin({
				slug: pluginSlug,
				file: slug
			});
		}
	};

	return (
		<>
			<Button
				size="sm"
				fontSize="xs"
				fontWeight="normal"
				variant="outline"
				colorScheme="primary"
				isDisabled={isPluginStatusLoading}
				ref={cancelRef}
				onClick={onClose}
			>
				{__("Cancel", "user-registration")}
			</Button>
			<Button
				size="sm"
				fontSize="xs"
				fontWeight="normal"
				colorScheme="primary"
				onClick={() => {
					performPluginAction(slug);
				}}
				ml={3}
				isLoading={isPluginStatusLoading}
			>
				{"inactive" === pluginsStatus[slug]
					? __("Activate", "user-registration")
					: __("Install", "user-registration")}
			</Button>
		</>
	);
};

export default UsePluginInstallActivate;
