import React from "react";
import { Stack } from "@chakra-ui/react";

import InputHandler from "../common/InputHandler";

const MyAccountSettings = ({ sectionSettings }) => {
    return (
        <Stack direction="column" spacing="8">
            {sectionSettings.settings.map((setting, key) => (
                <InputHandler key={key} setting={setting} />
            ))}
        </Stack>
    );
};

export default MyAccountSettings;
