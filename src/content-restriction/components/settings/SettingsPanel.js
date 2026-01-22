/**
 * External Dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useState } from "react";
import { RawHTML } from "@wordpress/element";
import { hasAdvancedLogic } from "../../utils/advanced-logic-helper";
import { getURCRData, isProAccess } from "../../utils/localized-data";
import { saveRuleWithCollectiveData } from "../../utils/rule-save-helper";
import AdvancedLogicWarningModal from "../modals/AdvancedLogicWarningModal";
import LocalPageAction from "./LocalPageAction";
import MessageAction from "./MessageAction";
import RedirectAction from "./RedirectAction";
import ShortcodeAction from "./ShortcodeAction";
import URFormAction from "./URFormAction";

/* global wp */

const SettingsPanel = ({ rule, onRuleUpdate, onGoBack, isToggling = false, onSavingChange }) => {
	const isMembershipRule = rule.rule_type === "membership";
	const isMigratedRule = Boolean(rule.is_migrated);
	const isMigratedCustomRule = isMigratedRule && !isMembershipRule;
	const [actionType, setActionType] = useState("message");
	const [message, setMessage] = useState("");
	const [redirectUrl, setRedirectUrl] = useState("");
	const [localPage, setLocalPage] = useState("");
	const [urForm, setUrForm] = useState("");
	const [shortcodeTag, setShortcodeTag] = useState("");
	const [shortcodeArgs, setShortcodeArgs] = useState("");
	const [isSaving, setIsSaving] = useState(false);
	const [useGlobalMessage, setUseGlobalMessage] = useState(true);
	const [isAdvancedLogicEnabled, setIsAdvancedLogicEnabled] = useState(
		Boolean(rule.is_advanced_logic_enabled || false)
	);
	const [showWarningModal, setShowWarningModal] = useState(false);
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
					const decodedMessage = decodeURIComponent(
						ruleActions[0].message
					);
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

		if (isMigratedCustomRule) {
			setActionType("message");
			if (
				ruleActions &&
				ruleActions.length > 0 &&
				ruleActions[0].message &&
				ruleActions[0].message.trim() !== ""
			) {
				try {
					const decodedMessage = decodeURIComponent(
						ruleActions[0].message
					);
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
			// For migrated custom rules, always use message type (handled above)
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
				const defaultMessage = getURCRData(
					"membership_default_message",
					""
				);
				return defaultMessage;
			};

			// For regular custom rules with message action, handle global/custom message option
			if (normalizedType === "message") {
				if (action.message) {
					try {
						const decodedMessage = decodeURIComponent(action.message);
						if (decodedMessage.trim() !== "") {
							setMessage(decodedMessage);
							setUseGlobalMessage(false);
						} else {
							setMessage("");
							setUseGlobalMessage(true);
						}
					} catch (e) {
						if (action.message.trim() !== "") {
							setMessage(action.message);
							setUseGlobalMessage(false);
						} else {
							setMessage("");
							setUseGlobalMessage(true);
						}
					}
				} else {
					setMessage("");
					setUseGlobalMessage(true);
				}
			} else {
				// For non-message actions, reset useGlobalMessage
				setUseGlobalMessage(true);
				if (action.message) {
					try {
						const decodedMessage = decodeURIComponent(action.message);
						if (decodedMessage.trim() !== "") {
							setMessage(decodedMessage);
						} else {
							setMessage("");
						}
					} catch (e) {
						if (action.message.trim() !== "") {
							setMessage(action.message);
						} else {
							setMessage("");
						}
					}
				} else {
					setMessage("");
				}
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
			setMessage("");
			setUseGlobalMessage(true);
			setRedirectUrl("");
			setLocalPage("");
			setUrForm("");
			setShortcodeTag("");
			setShortcodeArgs("");
		}
	}, [
		rule.id,
		rule.content,
		rule.actions,
		isMembershipRule,
		isMigratedCustomRule
	]);

	useEffect(() => {
		setIsAdvancedLogicEnabled(
			Boolean(rule.is_advanced_logic_enabled || false)
		);
	}, [rule.id, rule.is_advanced_logic_enabled]);

	// Notify parent when saving state changes
	useEffect(() => {
		if (onSavingChange) {
			onSavingChange(isSaving);
		}
	}, [isSaving, onSavingChange]);

	useEffect(() => {
		if (
			(isMembershipRule || isMigratedCustomRule) &&
			actionType !== "message"
		) {
			setActionType("message");
		}
		// For regular custom rules, when action type changes to message, initialize useGlobalMessage
		if (!isMembershipRule && !isMigratedCustomRule && actionType === "message") {
			const ruleData = rule.content || rule;
			const ruleActions = ruleData.actions || rule.actions || [];
			if (ruleActions && ruleActions.length > 0 && ruleActions[0].message && ruleActions[0].message.trim() !== "") {
				setUseGlobalMessage(false);
			} else {
				setUseGlobalMessage(true);
			}
		}
	}, [isMembershipRule, isMigratedCustomRule, actionType, rule.content, rule.actions]);

	const handleActionTypeChange = (e) => {
		if (isMembershipRule || isMigratedCustomRule) {
			return;
		}
		const newType = e.target.value;
		setActionType(newType);
	};

	const handleAdvancedLogicToggle = (e) => {
		const newValue = e.target.checked;

		if (!newValue) {
			const logicMap =
				rule.logic_map ||
				(rule.content && rule.content.logic_map) ||
				null;
			if (hasAdvancedLogic(logicMap)) {
				setShowWarningModal(true);
				return;
			}
		}

		setIsAdvancedLogicEnabled(newValue);

		if (onRuleUpdate) {
			const updatedRule = {
				...rule,
				is_advanced_logic_enabled: newValue,
				content: {
					...(rule.content || {}),
					is_advanced_logic_enabled: newValue
				}
			};
			onRuleUpdate(updatedRule);
		}
	};

	// Build actionData from current state
	const buildActionDataFromState = () => {
		const ruleData = rule.content || rule;
		const ruleActions = ruleData.actions || rule.actions || [];

		let effectiveActionType = actionType;
		if (isMembershipRule || isMigratedCustomRule) {
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
				if ((isMembershipRule || isMigratedCustomRule || (!isMembershipRule && !isMigratedCustomRule)) && useGlobalMessage) {
					actionData.message = "";
				} else {
					const defaultMessage = getURCRData(
						"membership_default_message",
						""
					);
					actionData.message = currentMessage || defaultMessage;
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
				actionData.label = __("Show UR Form (Legacy)", "user-registration");
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = "";
				actionData.ur_form = urForm ? String(urForm) : "";
				actionData.shortcode = { tag: "", args: "" };
				break;
			case "shortcode":
				actionData.label = __("Render Shortcode (Legacy)", "user-registration");
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
			if (isMembershipRule || isMigratedCustomRule) {
				effectiveActionType = "message";
			}

			const { actionData, accessControl: syncAccessControl } =
				buildActionDataFromState();

			if (isMembershipRule || isMigratedCustomRule) {
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
			if (isMembershipRule || isMigratedCustomRule) {
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
					if ((isMembershipRule || isMigratedCustomRule || (!isMembershipRule && !isMigratedCustomRule)) && useGlobalMessage) {
						actionData.message = "";
					} else {
						const defaultMessage = getURCRData(
							"membership_default_message",
							""
						);
						actionData.message = currentMessage || defaultMessage;
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
					actionData.label = __("Show UR Form (Legacy)", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = urForm ? String(urForm) : "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "shortcode":
					actionData.label = __(
						"Render Shortcode (Legacy)",
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
				rule: {
					...rule,
					is_advanced_logic_enabled: isAdvancedLogicEnabled,
					content: {
						...(rule.content || {}),
						is_advanced_logic_enabled: isAdvancedLogicEnabled
					}
				},
				onRuleUpdate,
				settingsData: {
					actionData,
					accessControl,
					isAdvancedLogicEnabled
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
			{!isMembershipRule && !isMigratedCustomRule && (
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
							{(() => {
								const actionTypeOptions = getURCRData("action_type_options", []);
								return actionTypeOptions.map((option) => {
									const isLegacy = option.value === "ur-form" || option.value === "shortcode";
									const label = isLegacy
										? `${option.label} (Legacy)`
										: option.label;
									return (
										<option key={option.value} value={option.value}>
											{label}
										</option>
									);
								});
							})()}
						</select>
						{(actionType === "ur-form" || actionType === "ur_form" || actionType === "shortcode") && (() => {
							const actionTypeOptions = getURCRData("action_type_options", []);
							const currentAction = actionTypeOptions.find(
								(option) => option.value === actionType || 
								(actionType === "ur_form" && option.value === "ur-form")
							);
							const dynamicLabel = currentAction ? currentAction.label : 
								(actionType === "shortcode" ? "Render Shortcode" : "Show UR Form");
							
							return (
								<div className="urcr-global-migration-notice">
									<p className="urcr-notice-wrap">
										<RawHTML>
											{__(
												`This legacy <code>${dynamicLabel}</code> setting will be removed in a future version. Please use 'Show Message' and add any shortcodes directly in the editor.`,
												"user-registration"
											)}
										</RawHTML>
									</p>
								</div>
							);
						})()}
					</div>
				</div>
			)}

			{actionType === "message" && (
				<MessageAction
					rule={rule}
					message={message}
					onMessageChange={setMessage}
					isMembershipRule={isMembershipRule}
					isMigratedCustomRule={isMigratedCustomRule}
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
				<URFormAction urForm={urForm} onUrFormChange={setUrForm} />
			)}

			{actionType === "shortcode" && (
				<ShortcodeAction
					shortcodeTag={shortcodeTag}
					shortcodeArgs={shortcodeArgs}
					onShortcodeTagChange={setShortcodeTag}
					onShortcodeArgsChange={setShortcodeArgs}
				/>
			)}

			{!isMembershipRule && !isMigratedCustomRule && isProAccess() && (
				<div className="urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span className="urcr-target-content-label">
							{__("Advanced Logic", "user-registration")}
						</span>
						<span className="urcr-puncher"></span>
						<span
							className="user-registration-help-tip"
							data-tip={__(
								"Enable advanced logic gates (OR, NOT) and nested groups for complex condition rules",
								"user-registration"
							)}
						></span>
					</label>
					<div className="urcr-input-container">
						<div className="ur-toggle-section">
							<span className="user-registration-toggle-form">
								<input
									type="checkbox"
									checked={isAdvancedLogicEnabled}
									onChange={handleAdvancedLogicToggle}
								/>
								<span className="slider round"></span>
							</span>
						</div>
					</div>
				</div>
			)}

			<div
				className="urcr-settings-actions"
				style={{ marginTop: "20px" }}
			>
				<button
					type="button"
					className="button button-primary"
					onClick={handleSave}
					disabled={isSaving || isToggling}
				>
					{isSaving
						? __("Saving...", "user-registration")
						: __("Save", "user-registration")}
				</button>
			</div>
			<AdvancedLogicWarningModal
				isOpen={showWarningModal}
				onClose={() => setShowWarningModal(false)}
			/>
		</div>
	);
};

export default SettingsPanel;
