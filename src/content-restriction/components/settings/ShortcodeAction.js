/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { RawHTML } from "@wordpress/element";
import { getURCRData } from "../../utils/localized-data";

const ShortcodeAction = ({
	shortcodeTag,
	shortcodeArgs,
	onShortcodeTagChange,
	onShortcodeArgsChange
}) => {
	const shortcodes = getURCRData("shortcodes", {});
	const shortcodeOptions = Object.keys(shortcodes).map((tag) => ({
		value: tag,
		label: tag
	}));

	return (
		<>
			<div className="urcr-title-body-pair urcr-rule-action-input-container urcrra-shortcode-input-container ur-form-group">
				<label className="urcr-label-container ur-col-4">
					<span className="urcr-target-content-label">
						{__("Render a Shortcode", "user-registration")}
					</span>
				</label>
				<div className="urcr-body">
					<div className="urcrra-shortcode-input">
						<select
							className="urcr-input"
							value={shortcodeTag || ""}
							onChange={(e) => onShortcodeTagChange(e.target.value)}
							style={{ marginBottom: "16px" }}
						>
							<option value="">
								{__("Select shortcode", "user-registration")}
							</option>
							{shortcodeOptions.map((shortcode) => (
								<option key={shortcode.value} value={shortcode.value}>
									{shortcode.label}
								</option>
							))}
						</select>
						<input
							type="text"
							className="urcr-input"
							value={shortcodeArgs || ""}
							onChange={(e) => onShortcodeArgsChange(e.target.value)}
							placeholder='Enter shortcode arguments here. Eg: id="345"'
						/>
					</div>
				</div>
			</div>
		</>
	);
};

export default ShortcodeAction;

