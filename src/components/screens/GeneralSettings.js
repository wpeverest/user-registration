/**
 * External Dependencies
 */
import React from "react";
import { Stack } from "@chakra-ui/react";

/**
 * Internal Dependencies
 */
import InputHandler from "../common/InputHandler";

const GeneralSettings = ({ sectionSettings }) => {
    return (
        <Stack direction="column" spacing="8" mt={5}>
            {sectionSettings.settings.map((setting, key) => (
                <InputHandler key={key} setting={setting} />
            ))}
        </Stack>
    );
};

export default GeneralSettings;
