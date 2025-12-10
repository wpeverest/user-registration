/**
 * External Dependencies
 */
import React, {useState, useRef, useEffect} from "react";
import {__} from "@wordpress/i18n";
import {updateRule} from "../../api/content-access-rules-api";
import {showSuccess, showError} from "../../utils/notifications";
import AccessControlSection from "./AccessControlSection";
import RuleGroup from "./RuleGroup";
import {getURCRLocalizedData, getURCRData} from "../../utils/localized-data";

/* global _UR_DASHBOARD_ */
const {adminURL} = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const RuleContentDisplay = ({rule, onRuleUpdate}) => {
	const [isSaving, setIsSaving] = useState(false);
	const [rootGroup, setRootGroup] = useState(null);
	const [accessControl, setAccessControl] = useState(rule.access_control || "access");
	const [contentTargets, setContentTargets] = useState([]); // Rule-level content targets
	

	// Initialize root group from rule data
	useEffect(() => {
		if (rule.logic_map) {
			// Initialize root group
			const initialGroup = {
				id: rule.logic_map.id || `x${Date.now()}`,
				type: "group",
				logic_gate: rule.logic_map.logic_gate || "AND",
				conditions: rule.logic_map.conditions || [],
			};
			setRootGroup(initialGroup);

			// Initialize access control from rule
			if (rule.access_control) {
				setAccessControl(rule.access_control);
			}

			// Initialize content targets from rule.target_contents (rule-level)
			if (rule.target_contents && Array.isArray(rule.target_contents) && rule.target_contents.length > 0) {
				// Convert old format to new format
				const convertedTargets = rule.target_contents.map((target) => {
					// Map old type names to new ones
					let type = target.type;
					if (type === "wp_pages") type = "pages";
					if (type === "wp_posts") type = "posts";

					// Handle taxonomy type - convert old format to new format
					let value = target.value || (type === "whole_site" ? "whole_site" : []);
					if (type === "taxonomy" && target.taxonomy) {
						// Convert old format { taxonomy: "category", value: [] } to new format
						value = {
							taxonomy: target.taxonomy,
							value: Array.isArray(target.value) ? target.value : [],
						};
					}

					return {
						id: target.id || `x${Date.now()}`,
						type: type,
						label: getTypeLabel(type),
						value: value,
					};
				});
				setContentTargets(convertedTargets);
			}
		} else {
			// Initialize empty root group
			setRootGroup({
				id: `x${Date.now()}`,
				type: "group",
				logic_gate: "AND",
				conditions: [],
			});
		}
	}, [rule.id]); // Only run on initial load

	// Access urcr_localized_data
	const urcrData = getURCRLocalizedData();

	const handleSave = async () => {
		setIsSaving(true);
		try {
			// Check if advanced logic is enabled
			const isAdvancedLogicEnabled = Boolean(getURCRData("is_advanced_logic_enabled", false));
			
			if (!rootGroup) {
				showError(__("No group data to save", "user-registration"));
				setIsSaving(false);
				return;
			}

			// Root group conditions are already serialized by notifyUpdate
			const logicConditions = rootGroup.conditions || [];

			// Build target_contents array from rule-level contentTargets
			const targetContents = contentTargets.map((target) => {
				// Map new type names back to old format
				let type = target.type;
				if (type === "pages") type = "wp_pages";
				if (type === "posts") type = "wp_posts";

				const targetData = {
					id: target.id || `x${Date.now()}`,
					type: type,
				};

				// Handle taxonomy type specially
				if (type === "taxonomy") {
					// For taxonomy, value is an object with taxonomy and value properties
					if (target.value && typeof target.value === "object" && target.value.taxonomy) {
						targetData.taxonomy = target.value.taxonomy;
						targetData.value = Array.isArray(target.value.value) ? target.value.value : [];
					} else {
						targetData.taxonomy = target.taxonomy || "";
						targetData.value = Array.isArray(target.value) ? target.value : [];
					}
				} else if (type !== "whole_site") {
					// Add value if not whole_site
					targetData.value = Array.isArray(target.value) ? target.value : [];
				}

				return targetData;
			});

			// Use actions from rule, or create default if none exist
			// Always use the current accessControl state value, not the existing action's value
			const currentAccessControl = accessControl || "access";
			let actions = rule.actions || [];
			
			if (actions.length === 0) {
				// Create default action if none exists
				actions = [
					{
						id: `x${Date.now()}`,
						type: "message",
						label: __("Show Message", "user-registration"),
						message: "<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>",
						redirect_url: "",
						access_control: currentAccessControl,
						local_page: "",
						ur_form: "",
						shortcode: {
							tag: "",
							args: "",
						},
					},
				];
			} else {
				// Always update access_control to match the current state
				actions = actions.map(action => ({
					...action,
					access_control: currentAccessControl,
				}));
			}

			// Build logic_map
			const logicMap = {
				type: "group",
				id: rootGroup.id || rule.logic_map?.id || `x${Date.now()}`,
				conditions: logicConditions,
			};

			// Only include logic_gate if advanced logic is enabled
			if (isAdvancedLogicEnabled) {
				logicMap.logic_gate = rootGroup.logic_gate || rule.logic_map?.logic_gate || "AND";
			}

			// Build the full access_rule_data structure
			const accessRuleData = {
				enabled: rule.enabled !== undefined ? rule.enabled : true,
				access_control: accessControl || "access",
				logic_map: logicMap,
				target_contents: targetContents,
				actions: actions,
			};

			const data = {
				title: rule.title || __("Untitled Rule", "user-registration"),
				access_rule_data: accessRuleData,
			};

			const response = await updateRule(rule.id, data);
			if (response.success) {
				showSuccess(response.message || __("Rule saved successfully", "user-registration"));
				// Update local state with the updated rule data without refetching
				const updatedRule = {
					...rule,
					title: data.title,
					access_control: accessControl,
					logic_map: logicMap,
					target_contents: targetContents,
					actions: actions,
				};
				onRuleUpdate(updatedRule);
			} else {
				showError(response.message || __("Failed to save rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsSaving(false);
		}
	};

	// Get type label
	const getTypeLabel = (type) => {
		const labels = {
			wp_pages: __("Pages", "user-registration"),
			wp_posts: __("Posts", "user-registration"),
			post_types: __("Post Types", "user-registration"),
			taxonomy: __("Taxonomy", "user-registration"),
			whole_site: __("Whole Site", "user-registration"),
		};
		return labels[type] || type;
	};

	const handleRootGroupUpdate = (updatedGroup) => {
		setRootGroup(updatedGroup);
	};

	// Check if advanced logic is enabled
	const isAdvancedLogicEnabled = Boolean(getURCRData("is_advanced_logic_enabled", false));

	if (!rootGroup) {
		return <div>Loading...</div>;
	}


	return (
		<div className="urcr-rule-content-panel">
			<RuleGroup
				group={rootGroup}
				onGroupUpdate={handleRootGroupUpdate}
				onGroupRemove={() => {}} // Root group cannot be removed
				isNested={false}
				accessControl={accessControl}
				onAccessControlChange={setAccessControl}
				contentTargets={contentTargets}
				onContentTargetsChange={setContentTargets}
				isMigrated={Boolean(rule.is_migrated)}
			/>

				{/* Save Button */}


			<div className="urcr-rule-actions">
				<button
					className="urcr-save-rule-btn button button-primary"
					type="button"
					onClick={handleSave}
					disabled={isSaving}
					data-rule-id={rule.id}
				>
					{isSaving ? __("Saving...", "user-registration") : __("Save", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default RuleContentDisplay;
