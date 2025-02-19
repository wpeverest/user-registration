/**
 * External Dependencies
 */
import React from "react";
import { Stack, Flex, Heading } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import InputHandler from "../common/InputHandler";
import RegistrationSettings from "./RegistrationSettings";

const GeneralSettings = ({ sectionSettings, siteURL, onBoardIconsURL }) => {
	return (
		<Flex direction="column" justifyContent="space-between" gap={"40px"}>
			<Flex
				direction="column"
				justifyContent="space-between"
				alignItems="left"
			>
				<Heading
					as="h2"
					size="lg"
					fontSize="22px"
					mb={4}
					color="#383838"
					fontWeight="600"
				>
					{__("General Settings", "user-registration")}
				</Heading>
				<Stack direction="column" spacing="8" mt={5}>
					{sectionSettings.settings.general.map((setting, key) => (
						<InputHandler key={key} setting={setting} />
					))}
				</Stack>
			</Flex>
			<hr />
			<Flex
				direction="column"
				justifyContent="space-between"
				alignItems="left"
			>
				<Heading
					as="h2"
					size="lg"
					fontSize="22px"
					mb={4}
					color="#383838"
					fontWeight="600"
				>
					{__("Registration Settings", "user-registration")}
				</Heading>
				<RegistrationSettings
					sectionSettings={sectionSettings.settings.registration}
					siteURL={siteURL}
					onBoardIconsURL={onBoardIconsURL}
				/>
			</Flex>
		</Flex>
	);
};

export default GeneralSettings;
