import {InspectorControls, useBlockProps} from "@wordpress/block-editor";
import {PanelBody, SelectControl, TextControl} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import React, {useState} from "react";
const Edit = (props) => {
    const blockProps = useBlockProps();

    const { attributes, setAttributes } = props;
    const { loginLabel, logoutLabel } = attributes;

    const [userState, setUserState] = useState("logged_in");

    return (
    <>
        <InspectorControls key="ur-gutenberg-login-logout-menu-inspector-controls">
            <PanelBody title="Login/Logout Menu Settings">
                <TextControl
                    label={__("Login Label", "user-registration")}
                    value={loginLabel}
                    onChange={ value => setAttributes({ loginLabel: value }) }
                />
                <TextControl
                    label={__("Logout Label", "user-registration")}
                    value={logoutLabel}
                    onChange={ value => setAttributes({ logoutLabel: value }) }
                />
                <SelectControl
                    label={__("User State", "user-registration")}
                    value={userState}
                    options={[
                        { label: "Logged In", value: "logged_in" },
                        { label: "Logged Out", value: "logged_out" },
                    ]}
                    onChange={setUserState}
                />
            </PanelBody>
        </InspectorControls>
        <div {...blockProps}
    >
        <a href="#">
            { userState === "logged_in" ? logoutLabel : loginLabel}
        </a>
    </div>
    </>
    );
}
export default Edit;
