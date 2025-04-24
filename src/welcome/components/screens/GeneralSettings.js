/**
 * External Dependencies
 */
import React, { useEffect, useState } from "react";
import { Stack, Flex, Heading } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import InputHandler from "../common/InputHandler";

const GeneralSettings = ({ sectionSettings, siteURL, onBoardIconsURL }) => {
	const [hideElement, setHideElement] = useState({
		user_registration_form_setting_minimum_password_strength: true
	});

	const handleModification = (data) => {
		setHideElement((prev) => ({
			...prev,
			user_registration_form_setting_minimum_password_strength:
				data.value ?? false
		}));
	};

	useEffect(() => {}, [hideElement]);
	return (
		<Flex direction="column" justifyContent="space-between" gap={"40px"}>
			<Stack direction="column" spacing="8" mt={5}>
				{sectionSettings.settings.map((setting, key) => (
					<InputHandler
						key={key}
						setting={setting}
						onModify={(value) => handleModification(value)}
						hideElement={hideElement}
					/>
				))}
			</Stack>
		</Flex>
	);
};

export default GeneralSettings;
