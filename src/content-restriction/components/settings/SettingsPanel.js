/**
 * External Dependencies
 */
import React, {useState, useEffect, useRef} from "react";
import {__} from "@wordpress/i18n";
import {getURCRLocalizedData, getURCRData} from "../../utils/localized-data";
import {updateRule} from "../../api/content-access-rules-api";
import {showSuccess, showError} from "../../utils/notifications";

/* global wp */

const SettingsPanel = ({rule, onRuleUpdate, onGoBack}) => {
	const [actionType, setActionType] = useState("message");
	const [message, setMessage] = useState("");
	const [redirectUrl, setRedirectUrl] = useState("");
	const [localPage, setLocalPage] = useState("");
	const [urForm, setUrForm] = useState("");
	const [shortcodeTag, setShortcodeTag] = useState("");
	const [shortcodeArgs, setShortcodeArgs] = useState("");
	const [isSaving, setIsSaving] = useState(false);
	const editorRef = useRef(null);
	const editorId = `urcr-action-message-editor-${rule.id}`;

	// Initialize from rule actions
	useEffect(() => {
		// Get rule data - check content property first, then root level
		const ruleData = rule.content || rule;
		const ruleActions = ruleData.actions || rule.actions || [];

		if (ruleActions && ruleActions.length > 0) {
			const action = ruleActions[0];
			if (action.type) {
				// Handle both "ur-form" and "ur_form" for backward compatibility
				const normalizedType = action.type === "ur_form" ? "ur-form" : action.type;
				setActionType(normalizedType);
			}
			if (action.message) {
				// Decode URL encoded message if needed
				try {
					setMessage(decodeURIComponent(action.message));
				} catch (e) {
					setMessage(action.message);
				}
			} else {
				setMessage("");
			}
			if (action.redirect_url) {
				setRedirectUrl(action.redirect_url);
			} else {
				setRedirectUrl("");
			}
			if (action.local_page) {
				setLocalPage(action.local_page);
			} else {
				setLocalPage("");
			}
			// Handle ur_form - check both ur_form and ur-form keys for backward compatibility
			if (action.ur_form !== undefined && action.ur_form !== null && action.ur_form !== "") {
				setUrForm(String(action.ur_form)); // Ensure it's a string
			} else {
				setUrForm("");
			}
			if (action.shortcode) {
				if (action.shortcode.tag) {
					setShortcodeTag(action.shortcode.tag);
				} else {
					setShortcodeTag("");
				}
				if (action.shortcode.args) {
					setShortcodeArgs(action.shortcode.args);
				} else {
					setShortcodeArgs("");
				}
			} else {
				setShortcodeTag("");
				setShortcodeArgs("");
			}
		} else {
			// Default to message with default text
			setActionType("message");
			setMessage("<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>");
			setRedirectUrl("");
			setLocalPage("");
			setUrForm("");
			setShortcodeTag("");
			setShortcodeArgs("");
		}
	}, [rule.id, rule.content, rule.actions]);

	// Initialize WordPress editor for message
	useEffect(() => {
		if (actionType === "message") {
			// Wait for wp.editor to be available
			const initEditor = () => {
				if (typeof wp !== "undefined" && wp.editor && document.getElementById(editorId)) {
					// Remove existing editor if it exists
					if (window.tinymce && window.tinymce.get(editorId)) {
						wp.editor.remove(editorId);
					}

					// Initialize editor with same settings as PHP version
					wp.editor.initialize(editorId, {
						quicktags: false,
						mediaButtons: true,
						tinymce: {
							toolbar1: "undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat",
							statusbar: false,
							plugins: "wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists",
							theme_advanced_buttons1: "bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator",
							theme_advanced_buttons2: "",
						},
					});

					// Listen for editor changes after a short delay to ensure editor is ready
					setTimeout(() => {
						if (window.tinymce && window.tinymce.get(editorId)) {
							const editor = window.tinymce.get(editorId);
							editor.on("change keyup", () => {
								const content = wp.editor.getContent(editorId);
								setMessage(content);
							});
						}
					}, 500);
				}
			};

			// Try to initialize immediately
			const timer1 = setTimeout(initEditor, 100);

			// Also try after a longer delay in case wp.editor loads later
			const timer2 = setTimeout(initEditor, 500);

			return () => {
				clearTimeout(timer1);
				clearTimeout(timer2);
				if (typeof wp !== "undefined" && wp.editor && document.getElementById(editorId)) {
					wp.editor.remove(editorId);
				}
			};
		}
	}, [actionType, editorId]);

	// Initialize tooltips for help tips
	useEffect(() => {
		if (typeof window.jQuery !== "undefined" && typeof window.jQuery.fn.tooltipster !== "undefined") {
			// Initialize tooltips for any help tips in this component
			const $helpTips = window.jQuery(".user-registration-help-tip");
			if ($helpTips.length > 0) {
				// Destroy existing tooltips first to avoid duplicates
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});

				// Initialize tooltips
				$helpTips.tooltipster({
					theme: "tooltipster-borderless",
					maxWidth: 200,
					multiple: true,
					interactive: true,
					position: "bottom",
					contentAsHTML: true,
					functionInit: function (instance, helper) {
						const tip = window.jQuery(helper.origin).attr("data-tip");
						if (tip) {
							instance.content(tip);
						}
					}
				});
			}

			return () => {
				// Cleanup on unmount
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});
			};
		}
	}, [actionType]); // Re-initialize when action type changes (component re-renders)

	// Get pages from localized data
	const pages = getURCRData("pages", {});
	const pageOptions = Object.entries(pages).map(([id, title]) => ({
		value: id,
		label: title,
	}));

	// Get UR forms from localized data
	const urForms = getURCRData("ur_forms", {});
	const formOptions = Object.entries(urForms).map(([id, title]) => ({
		value: id,
		label: title,
	}));

	// Get shortcodes from localized data
	const shortcodes = getURCRData("shortcodes", {});
	const shortcodeOptions = Object.keys(shortcodes).map((tag) => ({
		value: tag,
		label: tag,
	}));

	const handleActionTypeChange = (e) => {
		const newType = e.target.value;
		setActionType(newType);
	};

	const handleSave = async () => {
		setIsSaving(true);
		try {
			// Get rule data - check content property first, then root level
			const ruleData = rule.content || rule;
			const ruleActions = ruleData.actions || rule.actions || [];

			// Get current content from editor if message type
			let currentMessage = message;
			if (actionType === "message" && typeof wp !== "undefined" && wp.editor) {
				if (document.getElementById(editorId) && window.tinymce && window.tinymce.get(editorId)) {
					currentMessage = wp.editor.getContent(editorId);
				}
			}

			// Get access_control from existing action or rule, default to "restrict"
			const accessControl = ruleActions && ruleActions.length > 0 && ruleActions[0].access_control
				? ruleActions[0].access_control
				: (ruleData.access_control || rule.access_control || "restrict");

			// Build action based on type
			let actionData = {
				id: ruleActions && ruleActions.length > 0 ? ruleActions[0].id : `x${Date.now()}`,
				type: actionType,
				access_control: accessControl,
			};

			switch (actionType) {
				case "message":
					actionData.label = __("Show Message", "user-registration");
					actionData.message = currentMessage || "<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = {tag: "", args: ""};
					break;
				case "redirect":
					actionData.label = __("Redirect", "user-registration");
					actionData.message = "";
					actionData.redirect_url = redirectUrl;
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = {tag: "", args: ""};
					break;
				case "local_page":
					actionData.label = __("Redirect to a Local Page", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = localPage;
					actionData.ur_form = "";
					actionData.shortcode = {tag: "", args: ""};
					break;
				case "ur-form":
				case "ur_form":
					actionData.type = "ur-form"; // Always use hyphen format
					actionData.label = __("Show UR Form", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = urForm ? String(urForm) : ""; // Ensure it's a string
					actionData.shortcode = {tag: "", args: ""};
					break;
				case "shortcode":
					actionData.label = __("Render Shortcode", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = {
						tag: shortcodeTag,
						args: shortcodeArgs,
					};
					break;
			}

			// Build the data to save
			// Ensure access_control matches at both rule and action levels
			const accessRuleData = {
				enabled: ruleData.enabled !== undefined ? ruleData.enabled : (rule.enabled !== undefined ? rule.enabled : true),
				access_control: accessControl, // Use same access_control as action
				logic_map: ruleData.logic_map || rule.logic_map || {
					type: "group",
					id: `x${Date.now()}`,
					conditions: []
				},
				target_contents: ruleData.target_contents || rule.target_contents || [],
				actions: [actionData],
			};

			const data = {
				title: rule.title || __("Untitled Rule", "user-registration"),
				access_rule_data: accessRuleData,
			};

			// Debug: Log the payload to verify ur_form is included
			if (actionType === "ur-form" || actionType === "ur_form") {
				console.log("Saving UR Form action with form ID:", urForm);
				console.log("Action data:", actionData);
				console.log("Full payload:", JSON.stringify(data, null, 2));
			}

			const response = await updateRule(rule.id, data);
			if (response.success) {
				showSuccess(response.message || __("Settings saved successfully", "user-registration"));
				// Don't update state - let parent component handle refresh if needed
			} else {
				showError(response.message || __("Failed to save settings", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<div className="urcr-rule-settings-panel">

			<div className="urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group">
				<label className="urcr-label-container ur-col-4">
					<span className="urcr-target-content-label">{__("Action", "user-registration")}</span>
					<span className="urcr-puncher"></span>
					<span className="user-registration-help-tip"
						  data-tip={__("Action to perform for restricting the specified contents", "user-registration")}></span>
				</label>
				<div className="urcr-input-container">
					<select
						className="urcr-action-type-select urcr-condition-value-input"
						value={actionType}
						onChange={handleActionTypeChange}
						style={{width: "100%"}}
					>
						<option value="message">{__("Show Message", "user-registration")}</option>
						<option value="redirect">{__("Redirect", "user-registration")}</option>
						<option value="local_page">{__("Redirect to a Local Page", "user-registration")}</option>
						<option value="ur-form">{__("Show UR Form", "user-registration")}</option>
						<option value="shortcode">{__("Render Shortcode", "user-registration")}</option>
					</select>
				</div>
			</div>

			{/* Show Message - Text Editor */}
			{actionType === "message" && (
				<div
					className="urcr-title-body-pair urcr-rule-action-input-container urcrra-message-input-container  ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span
							className="urcr-target-content-label">{__("Redirection Message", "user-registration")}</span>
					</label>
					<div className="urcr-body">

						<div className="wp-editor-wrap">
							<div className="wp-editor-container">
							<textarea
								id={editorId}
								ref={editorRef}
								value={message}
								onChange={(e) => setMessage(e.target.value)}
								style={{minHeight: "175px", width: "100%"}}
							/>
							</div>
						</div>
					</div>
				</div>
			)}

			{/* Redirect - URL Input */}
			{actionType === "redirect" && (
				<div
					className="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-input-container  ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span className="urcr-target-content-label">{__("Redirection URL", "user-registration")}</span>
					</label>
					<div className="urcr-body ">
						<input
							type="url"
							className="urcr-input"
							value={redirectUrl}
							onChange={(e) => setRedirectUrl(e.target.value)}
							placeholder={__("Enter a URL to redirect to...", "user-registration")}
						/>
					</div>
				</div>
			)}

			{/* Redirect to Local Page - Pages Dropdown */}
			{actionType === "local_page" && (
				<div
					className="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-to-local-page-input-container  ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span
							className="urcr-target-content-label">{__("Redirect to a local page", "user-registration")}</span>
					</label>
					<div className="urcr-body ">
						<select
							className="urcr-input"
							value={localPage}
							onChange={(e) => setLocalPage(e.target.value)}
						>
							<option value="">{__("Select a page", "user-registration")}</option>
							{pageOptions.map((page) => (
								<option key={page.value} value={page.value}>
									{page.label}
								</option>
							))}
						</select>
					</div>
				</div>
			)}

			{/* Show UR Form - Forms Dropdown */}
			{(actionType === "ur-form" || actionType === "ur_form") && (
				<div
					className="urcr-title-body-pair urcr-rule-action-input-container urcrra-ur-form-input-container  ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span
							className="urcr-target-content-label">{__("Display User Registration Form", "user-registration")}</span>
					</label>
					<div className="urcr-body ">
						<select
							className="urcr-input"
							value={urForm}
							onChange={(e) => setUrForm(e.target.value)}
						>
							<option value="">{__("Select a form", "user-registration")}</option>
							{formOptions.map((form) => (
								<option key={form.value} value={form.value}>
									{form.label}
								</option>
							))}
						</select>
					</div>
				</div>
			)}

			{/* Render Shortcode - Two Inputs */}
			{actionType === "shortcode" && (
				<div
					className="urcr-title-body-pair urcr-rule-action-input-container urcrra-shortcode-input-container  ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span
							className="urcr-target-content-label">{__("Render a Shortcode", "user-registration")}</span>
					</label>
					<div className="urcr-body ">
						<div className="urcrra-shortcode-input">
							<select
								className="urcr-input"
								value={shortcodeTag}
								onChange={(e) => setShortcodeTag(e.target.value)}
								style={{marginBottom: "16px"}}
							>
								<option value="">{__("Select shortcode", "user-registration")}</option>
								{shortcodeOptions.map((shortcode) => (
									<option key={shortcode.value} value={shortcode.value}>
										{shortcode.label}
									</option>
								))}
							</select>
							<input
								type="text"
								className="urcr-input"
								value={shortcodeArgs}
								onChange={(e) => setShortcodeArgs(e.target.value)}
								placeholder='Enter shortcode arguments here. Eg: id="345"'
							/>
						</div>
					</div>
				</div>
			)}

			<div className="urcr-settings-actions" style={{marginTop: "20px"}}>
				<button
					type="button"
					className="button button-primary"
					onClick={handleSave}
					disabled={isSaving}
				>
					{isSaving ? __("Saving...", "user-registration") : __("Save Settings", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default SettingsPanel;
