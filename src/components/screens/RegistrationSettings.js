/**
 * External Dependencies
 */
import React from "react";
import { Stack } from "@chakra-ui/react";

/**
 * Internal Dependencies
 */
import InputHandler from "../common/InputHandler";
import { useStateValue } from "../../context/StateProvider";

const RegistrationSettings = ({ sectionSettings, siteURL }) => {
    const [{ settings }] = useStateValue();
    return (
        <Stack direction="column" spacing="8" mt={5}>
            {sectionSettings &&
				sectionSettings.settings.map((setting, key) =>
				    setting.id ===
						"user_registration_form_setting_minimum_password_strength" &&
					settings.user_registration_form_setting_enable_strong_password ===
						"no" ? (
				            ""
				        ) : (
				            <InputHandler
				                key={key}
				                setting={setting}
				                siteURL={siteURL}
				            />
				        )
				)}
        </Stack>
    );
};

export default RegistrationSettings;
