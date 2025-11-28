/**
 * External Dependencies
 */
import React, {useState, useRef, useEffect, useCallback} from "react";
import {__} from "@wordpress/i18n";
import {updateRule} from "../../api/content-access-rules-api";
import {showSuccess, showError} from "../../utils/notifications";
import ConditionFieldDropdown from "../dropdowns/ConditionFieldDropdown";
import ConditionRow from "./ConditionRow";
import AdvancedLogicGates from "./AdvancedLogicGates";
import AccessControlSection from "./AccessControlSection";
import {getURCRLocalizedData, getURCRData} from "../../utils/localized-data";

/* global _UR_DASHBOARD_ */
const {adminURL} = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const RuleContentDisplay = ({rule, onRuleUpdate}) => {
	const [redirectUrl, setRedirectUrl] = useState(rule.redirect_url || "");
	const [isSaving, setIsSaving] = useState(false);
	const [showCondition, setShowCondition] = useState(false);
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const [conditions, setConditions] = useState([]);
	const [accessControl, setAccessControl] = useState(rule.access_control || "access");
	const [contentTargets, setContentTargets] = useState([]); // Rule-level content targets
	const [logicGate, setLogicGate] = useState("AND"); // Default logic gate
	const dropdownWrapperRef = useRef(null);

	// Initialize conditions and content targets from rule data
	useEffect(() => {
		if (rule.logic_map && rule.logic_map.conditions && rule.logic_map.conditions.length > 0) {
			const initialConditions = rule.logic_map.conditions.map((cond) => {
				let conditionValue = cond.value;
				const conditionType = getConditionType(cond.type);
				
				// Normalize user_state: if it's an array, extract the first value
				// Also convert old format (logged_in/logged_out) to new format (logged-in/logged-out)
				if (cond.type === "user_state") {
					if (Array.isArray(conditionValue)) {
						conditionValue = conditionValue[0] || "";
					}
					// Convert underscore format to hyphen format for backward compatibility
					if (conditionValue === "logged_in") {
						conditionValue = "logged-in";
					} else if (conditionValue === "logged_out") {
						conditionValue = "logged-out";
					}
				} else if (!conditionValue) {
					conditionValue = conditionType === "multiselect" ? [] : "";
				}
				
				return {
					id: cond.id || `x${Date.now()}`,
					value: cond.type, // Field identifier
					label: cond.type, // Will be updated based on type
					type: conditionType,
					operator: "is",
					conditionValue: conditionValue,
				};
			});
			setConditions(initialConditions);
			// Initialize logic gate from rule
			if (rule.logic_map.logic_gate) {
				setLogicGate(rule.logic_map.logic_gate);
			}

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
		}
	}, [rule.id]); // Only run on initial load

	// Helper function to determine condition input type
	const getConditionType = (conditionType) => {
		const typeMap = {
			roles: "multiselect",
			membership: "multiselect",
			capabilities: "multiselect",
			registration_source: "multiselect",
			payment_status: "multiselect",
			ur_form_field: "multiselect",
			user_state: "checkbox",
			user_registered_date: "date",
			access_period: "period",
			email_domain: "text",
			post_count: "number",
		};
		return typeMap[conditionType] || "text";
	};

	// Access urcr_localized_data
	const urcrData = getURCRLocalizedData();

	const handleSave = async () => {
		setIsSaving(true);
		try {
			// Check if advanced logic is enabled
			const isAdvancedLogicEnabled = getURCRData("is_advanced_logic_enabled", false);
			// Build conditions array for logic_map
			const logicConditions = conditions.map((cond) => {
				let conditionValue = cond.conditionValue;

				// Handle special cases for condition values
				if (cond.type === "period" && typeof conditionValue === "object") {
					// Keep as object for period type
				} else if (cond.type === "ur_form_field" && typeof conditionValue === "object") {
					// Convert to form_id:field_name format or keep as object
					if (conditionValue.form_id && conditionValue.field_name) {
						conditionValue = {
							form_id: conditionValue.form_id,
							form_fields: [conditionValue.field_name],
						};
					} else {
						conditionValue = {form_id: "", form_fields: []};
					}
				} else if (cond.value === "user_state") {
					// user_state should be a scalar string, not an array
					// Extract from array if it was converted incorrectly
					conditionValue = Array.isArray(conditionValue) ? (conditionValue[0] || "") : (conditionValue || "");
				} else if (cond.type === "multiselect" || cond.type === "checkbox") {
					// Ensure array format for other checkbox/multiselect fields
					conditionValue = Array.isArray(conditionValue) ? conditionValue : (conditionValue ? [conditionValue] : []);
				} else if (cond.operator === "empty" || cond.operator === "not empty") {
					conditionValue = null;
				}

				const conditionData = {
					type: cond.value, // The field identifier
					id: cond.id || `x${Date.now()}`,
					value: conditionValue,
				};

				return conditionData;
			});

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

			// Build actions array
			// Use rule-level accessControl
			const defaultAccessControl = accessControl || "access";

			const actions = [
				{
					id: `x${Date.now()}`,
					type: redirectUrl ? "redirect" : "message",
					label: redirectUrl ? __("Redirect", "user-registration") : __("Show Message", "user-registration"),
					message: redirectUrl ? "" : "<p>" + __("You do not have sufficient permission to access this content.", "user-registration") + "</p>",
					redirect_url: redirectUrl || "",
					access_control: defaultAccessControl,
					local_page: "",
					ur_form: "",
					shortcode: {
						tag: "",
						args: "",
					},
				},
			];

			// Build logic_map
			const logicMap = {
				type: "group",
				id: rule.logic_map?.id || `x${Date.now()}`,
				conditions: logicConditions,
			};

			// Only include logic_gate if advanced logic is enabled
			if (isAdvancedLogicEnabled) {
				logicMap.logic_gate = logicGate || rule.logic_map?.logic_gate || "AND";
			} else {
				// When disabled, either set to AND or remove the key
				// Remove the key to keep it clean
				if (rule.logic_map?.logic_gate) {
					// Key will not be included in the object
				}
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

	// Check if this is a new/empty rule (no conditions and no targets)
	const isEmptyRule = true;

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

	// Handle tag removal (for future implementation)
	const handleRemoveTag = (type, tagId) => {
		// TODO: Implement tag removal via API
		console.log("Remove tag", type, tagId);
	};

	// Handle add content button click
	const handleAddContent = () => {
		const editUrl = adminURL
			? `${adminURL}admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=${rule.id}`
			: "#";
		window.location.href = editUrl;
	};

	// Close dropdown when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (dropdownWrapperRef.current && !dropdownWrapperRef.current.contains(event.target)) {
				setDropdownOpen(false);
			}
		};

		if (dropdownOpen) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [dropdownOpen]);

	const handleConditionButtonClick = (e) => {
		e.stopPropagation();
		setShowCondition(true);
		setDropdownOpen(!dropdownOpen);
	};

	const handleAfterConditionSelection = (option) => {
		// Add new condition
		// user_state should be initialized as empty string, not array
		let initialValue = "";
		if (option.type === "multiselect") {
			initialValue = [];
		} else if (option.type === "checkbox" && option.value !== "user_state") {
			initialValue = [];
		}
		
		const newCondition = {
			id: `x${Date.now()}`, // ID format matching old system
			value: option.value, // Field identifier
			label: option.label,
			type: option.type,
			operator: "is",
			conditionValue: initialValue, // The actual value for the condition
		};
		setConditions([...conditions, newCondition]);
		setDropdownOpen(false);
	};

	const handleConditionUpdate = (updatedCondition) => {
		setConditions(
			conditions.map((cond) =>
				cond.id === updatedCondition.id ? updatedCondition : cond
			)
		);
	};

	const handleConditionRemove = (conditionId) => {
		setConditions(conditions.filter((cond) => cond.id !== conditionId));
	};
	// Check if advanced logic is enabled
	const isAdvancedLogicEnabled = getURCRData("is_advanced_logic_enabled", false);


	return (
		<div className="urcr-rule-content-panel">
			{/* Advanced Logic Gates */}
			{
				isAdvancedLogicEnabled && <AdvancedLogicGates
					logicGate={logicGate}
					onLogicGateChange={setLogicGate}
				/>
			}
			<div className="urcr-rule-body ur-p-2">
				<div className="urcr-condition-row-parent">
					{/* Conditions List */}
					{conditions.length > 0 && (
						<div
							className={`urcr-conditions-list ${getURCRData("is_advanced_logic_enabled", false) ? "urcr-conditional-logic-definitions" : ""}`}>
							{getURCRData("is_advanced_logic_enabled", false) && (
								<div className={`urcr-condition-logic-gate-wrapper urcr-logic-group-rule-${logicGate}`}>
									<div
										className={`urcr-condition-logic-gate-button urcr-sub-logic-group-rule-${logicGate}`}
									>
										{logicGate}
									</div>
								</div>
							)}
							{conditions.map((condition, index) => (
								<div key={condition.id} className="urcr-condition-wrapper">
									<ConditionRow
										condition={condition}
										onUpdate={handleConditionUpdate}
									/>
									<button
										type="button"
										className="button button-link-delete"
										onClick={() => handleConditionRemove(condition.id)}
										aria-label={__("Remove condition", "user-registration")}
									>
										<span className="dashicons dashicons-no-alt"></span>
									</button>
								</div>
							))}
						</div>
					)}

					{/* Access Control Section - Show once for all conditions */}
					{conditions.length > 0 && (
						<AccessControlSection
							accessControl={accessControl}
							onAccessControlChange={setAccessControl}
							contentTargets={contentTargets}
							onContentTargetsChange={setContentTargets}
						/>
					)}
				</div>

				{/* Add Condition Button */}

				<div className="urcr-condition-dropdown-wrapper" ref={dropdownWrapperRef}>
					<button
						type="button"
						className="button urcr-add-condition-button"
						onClick={handleConditionButtonClick}
					>
						<span className="dashicons dashicons-plus-alt2"></span>
						{__("Condition", "user-registration")}
					</button>
					{dropdownOpen && (
						<ConditionFieldDropdown
							onSelect={handleAfterConditionSelection}
						/>
					)}
				</div>

			</div>
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
