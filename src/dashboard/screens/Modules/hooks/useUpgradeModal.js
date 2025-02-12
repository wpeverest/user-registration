import { useEffect, useState } from "react";
import { sprintf, __ } from "@wordpress/i18n";

export const useUpgradeModal = (
	upgradeModal,
	upgradeURL,
	licenseActivationURL
) => {
	const [upgradeContent, setUpgradeContent] = useState({
		title: "",
		body: "",
		buttonText: __("Upgrade to Pro", "user-registration"),
		upgradeURL:
			upgradeURL +
			"&utm_source=dashboard-all-features&utm_medium=upgrade-popup",
		licenseActivationPlaceholder: __("License key", "user-registration")
	});

	useEffect(() => {
		if (upgradeModal.enable) {
			const content = { ...upgradeContent };

			if (upgradeModal.type === "pro") {
				content.title = __(
					"User Registration Pro Required",
					"user-registration"
				);
				content.body = sprintf(
					__(
						"%s requires User Registration Pro to be activated. Please upgrade.",
						"user-registration"
					),
					upgradeModal.moduleName
				);
			} else if (upgradeModal.type === "license") {
				content.title = __(
					"License Activation Required",
					"user-registration"
				);
				content.body = sprintf(
					__(
						"Please activate your license to use %s.",
						"user-registration"
					),
					upgradeModal.moduleName
				);
				content.buttonText = __(
					"Activate License",
					"user-registration"
				);
				content.upgradeURL = licenseActivationURL;
			} else if (upgradeModal.type === "requirement") {
				content.title = __(
					"Activation Requirement not Fulfilled",
					"user-registration"
				);
				content.body = sprintf(
					__(
						"%s requires Membership module to be activated. Please activate Membership module in order to activate %s",
						"user-registration"
					),
					upgradeModal.moduleName,
					upgradeModal.moduleName
				);
				content.buttonText = sprintf(
					__("Ok", "user-registration"),
					upgradeModal.moduleName
				);
				content.upgradeURL = "";
			} else {
				content.title = __(
					"License Upgrade Required",
					"user-registration"
				);
				content.body = sprintf(
					__(
						"%s is only available in the plus plan and above.",
						"user-registration"
					),
					upgradeModal.moduleName
				);
				content.buttonText = __("Upgrade Plan", "user-registration");
			}

			setUpgradeContent(content);
		}
	}, [upgradeModal]);

	return upgradeContent;
};
