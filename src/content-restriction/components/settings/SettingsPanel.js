/**
 * External Dependencies
 */
import React, {useState, useEffect, useRef} from "react";
import {__} from "@wordpress/i18n";
import {getURCRLocalizedData, getURCRData} from "../../utils/localized-data";
import {showError} from "../../utils/notifications";
import {saveRuleWithCollectiveData} from "../../utils/rule-save-helper";

/* global wp */

const SettingsPanel = ({rule, onRuleUpdate, onGoBack}) => {
	const isMembershipRule = rule.rule_type === "membership";
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
		const ruleData = rule.content || rule;
		const ruleActions = ruleData.actions || rule.actions || [];

		if (isMembershipRule) {
			setActionType("message");
			if (ruleActions && ruleActions.length > 0 && ruleActions[0].message) {
				try {
					setMessage(decodeURIComponent(ruleActions[0].message));
				} catch (e) {
					setMessage(ruleActions[0].message);
				}
			} else {
				setMessage("<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>");
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
			if (action.type) {
				let normalizedType = action.type === "ur_form" ? "ur-form" : action.type;
				if (normalizedType === "redirect_to_local_page") {
					normalizedType = "local_page";
				}
				setActionType(normalizedType);
			}
			if (action.message) {
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
			if (action.ur_form !== undefined && action.ur_form !== null && action.ur_form !== "") {
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
			setMessage("<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>");
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

	// Initialize WordPress editor for message
	useEffect(() => {
		if (actionType === "message") {
			const initEditor = () => {
				if (typeof wp !== "undefined" && wp.editor && document.getElementById(editorId)) {
					if (window.tinymce && window.tinymce.get(editorId)) {
						wp.editor.remove(editorId);
					}

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

			const timer1 = setTimeout(initEditor, 100);
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

	// Initialize tooltips
	useEffect(() => {
		if (typeof window.jQuery !== "undefined" && typeof window.jQuery.fn.tooltipster !== "undefined") {
			const $helpTips = window.jQuery(".user-registration-help-tip");
			if ($helpTips.length > 0) {
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});

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
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});
			};
		}
	}, [actionType]);

	const pages = getURCRData("pages", {});
	const pageOptions = Object.entries(pages).map(([id, title]) => ({
		value: id,
		label: title,
	}));

	const urForms = getURCRData("ur_forms", {});
	const formOptions = Object.entries(urForms).map(([id, title]) => ({
		value: id,
		label: title,
	}));

	const shortcodes = getURCRData("shortcodes", {});
	const shortcodeOptions = Object.keys(shortcodes).map((tag) => ({
		value: tag,
		label: tag,
	}));

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
		if (effectiveActionType === "message" && typeof wp !== "undefined" && wp.editor) {
			if (document.getElementById(editorId) && window.tinymce && window.tinymce.get(editorId)) {
				currentMessage = wp.editor.getContent(editorId);
			}
		}

		const accessControl = rule.access_control || ruleData.access_control || 
			(ruleActions && ruleActions.length > 0 && ruleActions[0].access_control) || "restrict";

		let actionData = {
			id: ruleActions && ruleActions.length > 0 ? ruleActions[0].id : `x${Date.now()}`,
			type: effectiveActionType,
			access_control: accessControl,
		};

		switch (effectiveActionType) {
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
				actionData.type = "redirect_to_local_page";
				actionData.label = __("Redirect to a Local Page", "user-registration");
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = localPage;
				actionData.ur_form = "";
				actionData.shortcode = {tag: "", args: ""};
				break;
			case "ur-form":
			case "ur_form":
				actionData.type = "ur-form";
				actionData.label = __("Show UR Form", "user-registration");
				actionData.message = "";
				actionData.redirect_url = "";
				actionData.local_page = "";
				actionData.ur_form = urForm ? String(urForm) : "";
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
		};

		const stateChanged = !prevActionState.current ||
			prevActionState.current.actionType !== currentState.actionType ||
			prevActionState.current.message !== currentState.message ||
			prevActionState.current.redirectUrl !== currentState.redirectUrl ||
			prevActionState.current.localPage !== currentState.localPage ||
			prevActionState.current.urForm !== currentState.urForm ||
			prevActionState.current.shortcodeTag !== currentState.shortcodeTag ||
			prevActionState.current.shortcodeArgs !== currentState.shortcodeArgs;

		if (stateChanged) {
			let effectiveActionType = actionType;
			if (isMembershipRule) {
				effectiveActionType = "message";
			}

			const { actionData, accessControl: syncAccessControl } = buildActionDataFromState();
			
			if (isMembershipRule) {
				actionData.type = "message";
			}
			
			const currentLogicMap = rule.logic_map || (rule.content && rule.content.logic_map) || {
				type: "group",
				id: `x${Date.now()}`,
				conditions: []
			};
			const currentTargetContents = rule.target_contents || (rule.content && rule.content.target_contents) || [];

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
						actions: [actionData],
					}
				};
				onRuleUpdate(updatedRule);
			}

			prevActionState.current = currentState;
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [actionType, message, redirectUrl, localPage, urForm, shortcodeTag, shortcodeArgs]);

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
			if (effectiveActionType === "message" && typeof wp !== "undefined" && wp.editor) {
				if (document.getElementById(editorId) && window.tinymce && window.tinymce.get(editorId)) {
					currentMessage = wp.editor.getContent(editorId);
				}
			}

			const accessControl = rule.access_control || ruleData.access_control || 
				(ruleActions && ruleActions.length > 0 && ruleActions[0].access_control) || "restrict";

			let actionData = {
				id: ruleActions && ruleActions.length > 0 ? ruleActions[0].id : `x${Date.now()}`,
				type: effectiveActionType,
				access_control: accessControl,
			};

			switch (effectiveActionType) {
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
					actionData.type = "redirect_to_local_page";
					actionData.label = __("Redirect to a Local Page", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = localPage;
					actionData.ur_form = "";
					actionData.shortcode = {tag: "", args: ""};
					break;
				case "ur-form":
				case "ur_form":
					actionData.type = "ur-form";
					actionData.label = __("Show UR Form", "user-registration");
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = urForm ? String(urForm) : "";
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

			await saveRuleWithCollectiveData({
				rule,
				onRuleUpdate,
				settingsData: {
					actionData,
					accessControl,
				},
			});
		} catch (error) {
			// Error handled in saveRuleWithCollectiveData
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<div className="urcr-rule-settings-panel">

			<div className={`urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group ${isMembershipRule ? "urcr-hidden" : ""}`}>
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
					{isSaving ? __("Saving...", "user-registration") : __("Save", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default SettingsPanel;
