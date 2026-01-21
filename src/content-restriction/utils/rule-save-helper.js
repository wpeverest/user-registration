import { __ } from "@wordpress/i18n";
import { updateRule } from "../api/content-access-rules-api";
import { showSuccess, showError } from "./notifications";
import { getURCRData } from "./localized-data";

// Save rule with collective data from both settings and content sections
export const saveRuleWithCollectiveData = async ({
	rule,
	onRuleUpdate,
	settingsData = null,
	contentData = null
}) => {
	try {
		const ruleData = rule.content || rule;

		const isAdvancedLogicEnabled =
			settingsData?.isAdvancedLogicEnabled !== undefined
				? settingsData.isAdvancedLogicEnabled
				: rule.is_advanced_logic_enabled !== undefined
					? Boolean(rule.is_advanced_logic_enabled)
					: false;

		let actions = [];

		if (settingsData && settingsData.actionData) {
			actions = [settingsData.actionData];
		} else {
			if (
				rule.content &&
				rule.content.actions &&
				Array.isArray(rule.content.actions) &&
				rule.content.actions.length > 0
			) {
				actions = rule.content.actions;
			} else if (
				rule.actions &&
				Array.isArray(rule.actions) &&
				rule.actions.length > 0
			) {
				actions = rule.actions;
			} else if (
				ruleData.actions &&
				Array.isArray(ruleData.actions) &&
				ruleData.actions.length > 0
			) {
				actions = ruleData.actions;
			}

			const hasValidActions = actions.length > 0 && actions.some(
				(action) => action.type && action.type.trim() !== ""
			);

			if (!hasValidActions) {
				const currentAccessControl =
					contentData?.accessControl ||
					rule.access_control ||
					"access";
				actions = [
					{
						id: `x${Date.now()}`,
						type: "message",
						label: __("Show Message", "user-registration"),
						message: "",
						redirect_url: "",
						access_control: currentAccessControl,
						local_page: "",
						ur_form: "",
						shortcode: { tag: "", args: "" }
					}
				];
			} else {
				const currentAccessControl =
					contentData?.accessControl ||
					rule.access_control ||
					"access";
				actions = actions.map((action) => ({
					...action,
					access_control: currentAccessControl
				}));
			}
		}

		let targetContents = [];

		if (
			contentData &&
			contentData.contentTargets &&
			Array.isArray(contentData.contentTargets)
		) {
			targetContents = contentData.contentTargets.map((target) => {
				let type = target.type;
				if (type === "pages") type = "wp_pages";
				if (type === "posts") type = "wp_posts";

				const targetData = {
					id: target.id || `x${Date.now()}`,
					type: type,
					drip: target.drip
				};

				if (type === "taxonomy") {
					if (
						target.value &&
						typeof target.value === "object" &&
						target.value.taxonomy
					) {
						targetData.taxonomy = target.value.taxonomy;
						targetData.value = Array.isArray(target.value.value)
							? target.value.value
							: [];
					} else {
						targetData.taxonomy = target.taxonomy || "";
						targetData.value = Array.isArray(target.value)
							? target.value
							: [];
					}
				} else if (type !== "whole_site") {
					targetData.value = Array.isArray(target.value)
						? target.value
						: [];
				}

				return targetData;
			});
		} else {
			targetContents =
				rule.target_contents ||
				(rule.content && rule.content.target_contents) ||
				[];
		}

		let logicMap = {};

		if (contentData && contentData.rootGroup) {
			const logicConditions = contentData.rootGroup.conditions || [];
			logicMap = {
				type: "group",
				id:
					contentData.rootGroup.id ||
					rule.logic_map?.id ||
					`x${Date.now()}`,
				conditions: logicConditions
			};

			if (isAdvancedLogicEnabled) {
				logicMap.logic_gate =
					contentData.rootGroup.logic_gate ||
					rule.logic_map?.logic_gate ||
					"AND";
			}
		} else {
			logicMap = rule.logic_map ||
				(rule.content && rule.content.logic_map) || {
					type: "group",
					id: `x${Date.now()}`,
					conditions: []
				};
		}

		const accessControl =
			contentData?.accessControl ||
			(settingsData && settingsData.accessControl) ||
			rule.access_control ||
			"access";

		const accessRuleData = {
			enabled:
				rule.enabled !== undefined
					? rule.enabled
					: ruleData.enabled !== undefined
						? ruleData.enabled
						: true,
			access_control: accessControl,
			logic_map: logicMap,
			target_contents: targetContents,
			actions: actions,
			is_advanced_logic_enabled: isAdvancedLogicEnabled
		};

		const data = {
			title: rule.title || __("Untitled Rule", "user-registration"),
			access_rule_data: accessRuleData
		};

		const response = await updateRule(rule.id, data);

		if (response.success) {
			showSuccess(
				response.message ||
					__("Rule saved successfully", "user-registration")
			);

			const updatedRule = {
				...rule,
				title: data.title,
				enabled: accessRuleData.enabled,
				access_control: accessControl,
				logic_map: logicMap,
				target_contents: targetContents,
				actions: actions,
				is_advanced_logic_enabled: isAdvancedLogicEnabled,
				content: {
					...(rule.content || {}),
					enabled: accessRuleData.enabled,
					access_control: accessControl,
					logic_map: logicMap,
					target_contents: targetContents,
					actions: actions,
					is_advanced_logic_enabled: isAdvancedLogicEnabled
				}
			};

			if (onRuleUpdate) {
				onRuleUpdate(updatedRule);
			}

			return response;
		} else {
			showError(
				response.message ||
					__("Failed to save rule", "user-registration")
			);
			throw new Error(
				response.message ||
					__("Failed to save rule", "user-registration")
			);
		}
	} catch (error) {
		showError(
			error.message ||
				__("An error occurred while saving", "user-registration")
		);
		throw error;
	}
};
