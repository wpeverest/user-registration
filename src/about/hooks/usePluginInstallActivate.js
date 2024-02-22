import React, { useEffect } from "react";
import { useToast } from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { sprintf, __ } from "@wordpress/i18n";
import { useStateValue } from "../../context/StateProvider";

const usePluginInstallActivate = ({
	successCallback,
	errorCallback,
	pluginsStatus,
}) => {
	const toast = useToast();
	const [{ setPluginsStatus }, dispatch] = useStateValue();

	const activatePlugin = useMutation(
		({ slug, file }) =>
			apiFetch({
				path: `wp/v2/plugins/${slug}`,
				method: "POST",
				data: {
					plugin: file.replace(".php", ""),
					status: "active",
				},
			}),
		{
			onSuccess(data) {
				setPluginsStatus({
					[`${data.plugin}.php`]: data.status,
				});
				toast({
					status: "success",
					description: sprintf(
						__("%s plugin activated successfully", "blockart"),
						data.name
					),
					isClosable: true,
				});
				successCallback?.();
				// window.location.reload();
			},
			onError(e) {
				toast({
					status: "error",
					description: e.message,
					isClosable: true,
				});
				errorCallback?.(e);
			},
		}
	);

	const installPlugin = useMutation(
		(plugin) =>
			apiFetch({
				path: "wp/v2/plugins",
				method: "POST",
				data: {
					slug: plugin,
					status: "active",
				},
			}),
		{
			onSuccess(data) {
				setPluginsStatus({
					[`${data.plugin}.php`]: data.status,
				});
				toast({
					status: "success",
					description: sprintf(
						__(
							"%s plugin installed and activated successfully",
							"blockart"
						),
						data.name
					),
					isClosable: true,
				});
				successCallback?.();
				// window.location.reload();
			},
			onError(e) {
				toast({
					status: "error",
					description: e.message,
					isClosable: true,
				});
				errorCallback?.(e);
			},
		}
	);

	const performPluginAction = (pluginFile) => {
		const slug = pluginFile.split("/")[0];

		if (pluginsStatus[pluginFile] === "not-installed") {
			installPlugin.mutate(slug);
		} else if (pluginsStatus[pluginFile] === "inactive") {
			activatePlugin.mutate({
				slug: slug,
				file: pluginFile,
			});
		}
	};

	useEffect(() => {
		// Cleanup function if needed
		return () => {
			// Cleanup code here
		};
	}, []);

	return {
		installPlugin,
		activatePlugin,
		performPluginAction,
	};
};

export default usePluginInstallActivate;
