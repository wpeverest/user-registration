/**
 * External Dependencies
 */
import React from "react";
import { Stack } from "@chakra-ui/react";

import InputHandler from "../common/InputHandler";

const MyAccountSettings = ({ sectionSettings, siteURL, onBoardIconsURL }) => {
	return (
		<Stack direction="column" spacing="8" mt={5}>
			{sectionSettings &&
				sectionSettings.settings.map((setting, key) => (
					<InputHandler
						key={key}
						setting={setting}
						siteURL={siteURL}
						onBoardIconsURL={onBoardIconsURL}
					/>
				))}
		</Stack>
	);
};

export default MyAccountSettings;
