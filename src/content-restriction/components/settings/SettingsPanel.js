/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRLocalizedData, getURCRData } from "../../utils/localized-data";
import { showError } from "../../utils/notifications";
import { saveRuleWithCollectiveData } from "../../utils/rule-save-helper";
import MessageAction from "./MessageAction";
import RedirectAction from "./RedirectAction";
import LocalPageAction from "./LocalPageAction";
import URFormAction from "./URFormAction";
import ShortcodeAction from "./ShortcodeAction";

/* global wp */

const SettingsPanel = ({ rule, onRuleUpdate, onGoBack }) => {
	const isMembershipRule = rule.rule_type === "membership";
	const [actionType, setActionType] = useState("message");
	const [message, setMessage] = useState("");
	const [redirectUrl, setRedirectUrl] = useState("");
	const [localPage, setLocalPage] = useState("");
	const [urForm, setUrForm] = useState("");
	const [shortcodeTag, setShortcodeTag] = useState("");
	const [shortcodeArgs, setShortcodeArgs] = useState("");
	const [isSaving, setIsSaving] = useState(false);
	const [useGlobalMessage, setUseGlobalMessage] = useState(true);
	const editorId = `urcr-action-message-editor-${rule.id}`;

	// Initialize from rule actions
	useEffect(() => {
		const ruleData = rule.content || rule;
		const ruleActions = ruleData.actions || rule.actions || [];

		if (isMembershipRule) {
			setActionType("message");
			if (
				ruleActions &&
				ruleActions.length > 0 &&
				ruleActions[0].message &&
				ruleActions[0].message.trim() !== ""
			) {
				try {
					const decodedMessage = decodeURIComponent(ruleActions[0].message);
					setMessage(decodedMessage);
					setUseGlobalMessage(false);
				} catch (e) {
					setMessage(ruleActions[0].message);
					setUseGlobalMessage(false);
				}
			} else {
				setMessage("");
				setUseGlobalMessage(true);
			}
			setRedirectUrl("");
			setLocalPage("");
			setUrForm("");
			setShortcodeTag("");
			setShortcodeArgs("");
			return;
		}

		if (ruleActions && ruleActions.length > 0) {
			const action = ruleActions[0];
			let normalizedType = "message"; // Default to message
			if (action.type) {
				normalizedType =
					action.type === "ur_form" ? "ur-form" : action.type;
				if (normalizedType === "redirect_to_local_page") {
					normalizedType = "local_page";
				}
				setActionType(normalizedType);
			}
			
			// Helper function to get default message
			const getDefaultMessage = () => {
				return (
					"<p>" +
					__(
						"You do not have sufficient permission to access this content.",
						"user-registration"
					) +
					"</p>"
				);
			};
			
			if (action.message) {
				try {
					const decodedMessage = decodeURIComponent(action.message);
					if (decodedMessage.trim() !== "") {
						setMessage(decodedMessage);
					} else {
						// If message is empty and action type is message, show default message
						setMessage(
							normalizedType === "message" ? getDefaultMessage() : ""
						);
					}
				} catch (e) {
					if (action.message.trim() !== "") {
						setMessage(action.message);
					} else {
						// If message is empty and action type is message, show default message
						setMessage(
							normalizedType === "message" ? getDefaultMessage() : ""
						);
					}
				}
			} else {
				// If message is not set and action type is message, show default message
				setMessage(
					normalizedType === "message" ? getDefaultMessage() : ""
				);
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
			if (
				action.ur_form !== undefined &&
				action.ur_form !== null &&
				action.ur_form !== ""
			) {
				setUrForm(String(action.ur_form));
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
			setActionType("message");
			setMessage(
				"<p>" +
					__(
						"You do not have sufficient permission to access this content.",
						"user-registration"
					) +
					"</p>"
			);
			setRedirectUrl("");
			setLocalPage("");
			setUrForm("");
			setShortcodeTag("");
			setShortcodeArgs("");
		}
	}, [rule.id, rule.content, rule.actions, isMembershipRule]);

	useEffect(() => {
		if (isMembershipRule && actionType !== "message") {
			setActionType("message");
		}
	}, [isMembershipRule, actionType]);


	const handleActionTypeChange = (e) => {
		if (isMembershipRule) {
			return;
		}
		const newType = e.target.value;
		setActionType(newType);
	};

	// Build actionData from current state
	const buildActionDataFromState = () => {
		const ruleData = rule.content || rule;
		const ruleActions = ruleData.actions || rule.actions || [];

		let effectiveActionType = actionType;
		if (isMembershipRule) {
			effectiveActionType = "message";
		}

		let currentMessage = message;
		if (
			effectiveActionType === "message" &&
			typeof wp !== "undefined" &&
			wp.editor
		) {
			if (
				document.getElementById(editorId) &&
				window.tinymce &&
				window.tinymce.get(editorId)
			) {
				currentMessage = wp.editor.getContent(editorId);
			}
		}

		const accessControl =
			rule.access_control ||
			ruleData.access_control ||
			(ruleActions &&
				ruleActions.length > 0 &&
				ruleActions[0].access_control) ||
			"restrict";

		let actionData = {
			id:
				ruleActions && ruleActions.length > 0
					? ruleActions[0].id
					: `x${Date.now()}`,
			type: effectiveActionType,
			access_control: accessControl
		};

		switch (effectiveActionType) {
			case "message":
				actionData.label = __("Show Message", "user-registration");
				if (isMembershipRule && useGlobalMessage) {
					actionData.message = "";
				} else {
					actionData.message =
						currentMessage ||
						"<p>" +
							__(
								"You do not have sufficient permission to access this content.",
								"user-registration"
							) +
							"</p>";
				}
				actionData.redirect_url = "";
				actionData.local_page = "";
				actionData.ur_form = "";
				actionData.shortcode = { tag: "", args: "" };
				break;
			case "redirect":
				actionData.label = __("Redirect", "user-registration");
				actionData.message = "";
				actionData.redirect_url = redirectUrl;
				actionData.local_page = "";
				actionData.ur_form = "";
				actionData.shortcode = { tag: "", args: "" };
				break;
			case "local_page":
				actionData.type = "redirect_to_local_page";
				actionData.label = __(
					"Redirect to a Local Page",
					"user-registration"
				);
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = localPage;
				actionData.ur_form = "";
				actionData.shortcode = { tag: "", args: "" };
				break;
			case "ur-form":
			case "ur_form":
				actionData.type = "ur-form";
				actionData.label = __("Show UR Form", "user-registration");
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = "";
				actionData.ur_form = urForm ? String(urForm) : "";
				actionData.shortcode = { tag: "", args: "" };
				break;
			case "shortcode":
				actionData.label = __("Render Shortcode", "user-registration");
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = "";
				actionData.ur_form = "";
				actionData.shortcode = {
					tag: shortcodeTag,
					args: shortcodeArgs
				};
				break;
		}

		return { actionData, accessControl };
	};

	// Sync state to rule prop when action state changes
	const hasInitialized = useRef(false);
	const prevActionState = useRef(null);

	useEffect(() => {
		if (!hasInitialized.current) {
			hasInitialized.current = true;
			prevActionState.current = {
				actionType,
				message,
				redirectUrl,
				localPage,
				urForm,
				shortcodeTag,
				shortcodeArgs,
				useGlobalMessage
			};
			return;
		}

		const currentState = {
			actionType,
			message,
			redirectUrl,
			localPage,
			urForm,
			shortcodeTag,
			shortcodeArgs,
			useGlobalMessage
		};

		const stateChanged =
			!prevActionState.current ||
			prevActionState.current.actionType !== currentState.actionType ||
			prevActionState.current.message !== currentState.message ||
			prevActionState.current.redirectUrl !== currentState.redirectUrl ||
			prevActionState.current.localPage !== currentState.localPage ||
			prevActionState.current.urForm !== currentState.urForm ||
			prevActionState.current.shortcodeTag !==
				currentState.shortcodeTag ||
			prevActionState.current.shortcodeArgs !==
				currentState.shortcodeArgs ||
			prevActionState.current.useGlobalMessage !==
				currentState.useGlobalMessage;

		if (stateChanged) {
			let effectiveActionType = actionType;
			if (isMembershipRule) {
				effectiveActionType = "message";
			}

			const { actionData, accessControl: syncAccessControl } =
				buildActionDataFromState();

			if (isMembershipRule) {
				actionData.type = "message";
			}

			const currentLogicMap = rule.logic_map ||
				(rule.content && rule.content.logic_map) || {
					type: "group",
					id: `x${Date.now()}`,
					conditions: []
				};
			const currentTargetContents =
				rule.target_contents ||
				(rule.content && rule.content.target_contents) ||
				[];

			if (onRuleUpdate) {
				const updatedRule = {
					...rule,
					access_control: syncAccessControl,
					actions: [actionData],
					content: {
						...(rule.content || {}),
						access_control: syncAccessControl,
						logic_map: currentLogicMap,
						target_contents: currentTargetContents,
						actions: [actionData]
					}
				};
				onRuleUpdate(updatedRule);
			}

			prevActionState.current = currentState;
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [
		actionType,
		message,
		redirectUrl,
		localPage,
		urForm,
		shortcodeTag,
		shortcodeArgs,
		useGlobalMessage
	]);

	const handleSave = async () => {
		setIsSaving(true);
		try {
			const ruleData = rule.content || rule;
			const ruleActions = ruleData.actions || rule.actions || [];

			let effectiveActionType = actionType;
			if (isMembershipRule) {
				effectiveActionType = "message";
			}

			let currentMessage = message;
			if (
				effectiveActionType === "message" &&
				typeof wp !== "undefined" &&
				wp.editor
			) {
				if (
					document.getElementById(editorId) &&
					window.tinymce &&
					window.tinymce.get(editorId)
				) {
					currentMessage = wp.editor.getContent(editorId);
				}
			}

			const accessControl =
				rule.access_control ||
				ruleData.access_control ||
				(ruleActions &&
					ruleActions.length > 0 &&
					ruleActions[0].access_control) ||
				"restrict";

			let actionData = {
				id:
					ruleActions && ruleActions.length > 0
						? ruleActions[0].id
						: `x${Date.now()}`,
				type: effectiveActionType,
				access_control: accessControl
			};

			switch (effectiveActionType) {
				case "message":
					actionData.label = __("Show Message", "user-registration");
					if (isMembershipRule && useGlobalMessage) {
						actionData.message = "";
					} else {
						actionData.message =
							currentMessage ||
							"<p>" +
								__(
									"You do not have sufficient permission to access this content.",
									"user-registration"
								) +
								"</p>";
					}
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "redirect":
					actionData.label = __("Redirect", "user-registration");
					actionData.message = "";
					actionData.redirect_url = redirectUrl;
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "local_page":
					actionData.type = "redirect_to_local_page";
					actionData.label = __(
						"Redirect to a Local Page",
						"user-registration"
					);
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = localPage;
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "ur-form":
				case "ur_form":
					actionData.type = "ur-form";
					actionData.label = __("Show UR Form", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = urForm ? String(urForm) : "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "shortcode":
					actionData.label = __(
						"Render Shortcode",
						"user-registration"
					);
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = {
						tag: shortcodeTag,
						args: shortcodeArgs
					};
					break;
			}

			await saveRuleWithCollectiveData({
				rule,
				onRuleUpdate,
				settingsData: {
					actionData,
					accessControl
				}
			});
		} catch (error) {
			// Error handled in saveRuleWithCollectiveData
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<div className="urcr-rule-settings-panel">
			{!isMembershipRule && (
				<div className="urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span className="urcr-target-content-label">
							{__("Action", "user-registration")}
						</span>
						<span className="urcr-puncher"></span>
						<span
							className="user-registration-help-tip"
							data-tip={__(
								"Action to perform for restricting the specified contents",
								"user-registration"
							)}
						></span>
					</label>
					<div className="urcr-input-container">
						<select
							className="urcr-action-type-select urcr-condition-value-input"
							value={actionType}
							onChange={handleActionTypeChange}
							style={{ width: "100%" }}
						>
							<option value="message">
								{__("Show Message", "user-registration")}
							</option>
							<option value="redirect">
								{__("Redirect", "user-registration")}
							</option>
							<option value="local_page">
								{__(
									"Redirect to a Local Page",
									"user-registration"
								)}
							</option>
							<option value="ur-form">
								{__("Show UR Form", "user-registration")}
							</option>
							<option value="shortcode">
								{__("Render Shortcode", "user-registration")}
							</option>
						</select>
					</div>
				</div>
			)}

			{actionType === "message" && (
				<MessageAction
					rule={rule}
					message={message}
					onMessageChange={setMessage}
					isMembershipRule={isMembershipRule}
					useGlobalMessage={useGlobalMessage}
					onUseGlobalMessageChange={setUseGlobalMessage}
				/>
			)}

			{actionType === "redirect" && (
				<RedirectAction
					redirectUrl={redirectUrl}
					onRedirectUrlChange={setRedirectUrl}
				/>
			)}

			{actionType === "local_page" && (
				<LocalPageAction
					localPage={localPage}
					onLocalPageChange={setLocalPage}
				/>
			)}

			{(actionType === "ur-form" || actionType === "ur_form") && (
				<URFormAction
					urForm={urForm}
					onUrFormChange={setUrForm}
				/>
			)}

			{actionType === "shortcode" && (
				<ShortcodeAction
					shortcodeTag={shortcodeTag}
					shortcodeArgs={shortcodeArgs}
					onShortcodeTagChange={setShortcodeTag}
					onShortcodeArgsChange={setShortcodeArgs}
				/>
			)}

			<div
				className="urcr-settings-actions"
				style={{ marginTop: "20px" }}
			>
				<button
					type="button"
					className="button button-primary"
					onClick={handleSave}
					disabled={isSaving}
				>
					{isSaving
						? __("Saving...", "user-registration")
						: __("Save", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default SettingsPanel;
