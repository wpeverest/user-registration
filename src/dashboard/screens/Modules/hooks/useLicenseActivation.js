import { useState } from "react";
import { useToast } from "@chakra-ui/react";
import { activateLicense } from "../components/modules-api";

export const useLicenseActivation = (reloadPage) => {
	const toast = useToast();
	const [isLicenseActivation, setLicenseActivation] = useState(false);
	const [licenseKey, setLicenseKey] = useState("");
	const [validationMessage, setValidationMessage] = useState("");

	const validateLicenseKey = () => {
		if (!licenseKey)
			return __("Please enter a license key.", "user-registration");
		if (licenseKey.length < 32)
			return __("Invalid license key.", "user-registration");
		return "";
	};

	const handleActivation = () => {
		const validationError = validateLicenseKey();
		if (validationError) {
			setValidationMessage(validationError);
			return;
		}

		setLicenseActivation(true);
		activateLicense(licenseKey)
			.then((data) => {
				toast({
					title: data.message,
					status: data.code === 200 ? "success" : "error",
					duration: 3000
				});
				if (data.code === 200) reloadPage();
			})
			.catch((e) => {
				toast({ title: e.message, status: "error", duration: 3000 });
			})
			.finally(() => setLicenseActivation(false));
	};

	return {
		licenseKey,
		setLicenseKey,
		isLicenseActivation,
		validationMessage,
		handleActivation
	};
};
