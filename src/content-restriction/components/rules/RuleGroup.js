/**
 * External Dependencies
 */
import React, {useState, useRef, useEffect} from "react";
import {__} from "@wordpress/i18n";
import ConditionFieldDropdown from "../dropdowns/ConditionFieldDropdown";
import ConditionRow from "./ConditionRow";
import AdvancedLogicGates from "./AdvancedLogicGates";
import AccessControlSection from "./AccessControlSection";
import {getURCRData, isProAccess} from "../../utils/localized-data";

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

// RuleGroup Component - Reusable nested group component
const RuleGroup = ({
	group,
	onGroupUpdate,
	onGroupRemove,
	isNested = false,
	accessControl,
	onAccessControlChange,
	contentTargets,
	onContentTargetsChange,
}) => {
	const [conditions, setConditions] = useState([]);
	const [logicGate, setLogicGate] = useState(group.logic_gate || "AND");
	const [dropdownOpen, setDropdownOpen] = useState(false);
	const dropdownWrapperRef = useRef(null);
	const isAdvancedLogicEnabled = getURCRData("is_advanced_logic_enabled", false);

	// Initialize conditions from group data
	useEffect(() => {
		if (group.conditions && group.conditions.length > 0) {
			const initialConditions = group.conditions.map((cond) => {
				if (cond.type === "group") {
					// This is a nested group
					return {
						type: "group",
						id: cond.id,
						group: cond,
					};
				} else {
					// Regular condition
					let conditionValue = cond.value;
					const conditionType = getConditionType(cond.type);

					if (cond.type === "user_state") {
						if (Array.isArray(conditionValue)) {
							conditionValue = conditionValue[0] || "";
						}
						if (conditionValue === "logged_in") {
							conditionValue = "logged-in";
						} else if (conditionValue === "logged_out") {
							conditionValue = "logged-out";
						}
					} else if (!conditionValue) {
						conditionValue = conditionType === "multiselect" ? [] : "";
					}

					return {
						type: "condition",
						id: cond.id || `x${Date.now()}`,
						value: cond.type,
						label: cond.type,
						inputType: conditionType,
						operator: "is",
						conditionValue: conditionValue,
					};
				}
			});
			setConditions(initialConditions);
		} else {
			setConditions([]);
		}
		if (group.logic_gate) {
			setLogicGate(group.logic_gate);
		}
	}, [group.id]);

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
		setDropdownOpen(!dropdownOpen);
	};

	const handleAfterConditionSelection = (option) => {
		let initialValue = "";
		if (option.type === "multiselect") {
			initialValue = [];
		} else if (option.type === "checkbox" && option.value !== "user_state") {
			initialValue = [];
		}

		const newCondition = {
			type: "condition",
			id: `x${Date.now()}`,
			value: option.value,
			label: option.label,
			inputType: option.type,
			operator: "is",
			conditionValue: initialValue,
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

	const handleAddGroup = () => {
		const groupId = `x${Date.now()}`;
		const newGroup = {
			type: "group",
			id: groupId,
			group: {
				id: groupId,
				type: "group",
				logic_gate: "AND",
				conditions: [],
			},
		};
		setConditions([...conditions, newGroup]);
	};

	const handleGroupUpdate = (groupId, updatedGroup) => {
		setConditions(
			conditions.map((cond) =>
				cond.id === groupId ? { ...cond, group: updatedGroup } : cond
			)
		);
	};

	const handleGroupRemove = (groupId) => {
		setConditions(conditions.filter((cond) => cond.id !== groupId));
	};

	// Notify parent of changes whenever conditions or logicGate changes
	useEffect(() => {
		// Build the updated group structure
		const updatedGroup = {
			id: group.id,
			type: "group",
			logic_gate: logicGate,
			conditions: conditions.map((cond) => {
				if (cond.type === "group") {
					return cond.group;
				} else {
					let conditionValue = cond.conditionValue;
					if (cond.inputType === "ur_form_field" && typeof conditionValue === "object") {
						if (conditionValue.form_id && conditionValue.field_name) {
							conditionValue = {
								form_id: conditionValue.form_id,
								form_fields: [conditionValue.field_name],
							};
						} else {
							conditionValue = {form_id: "", form_fields: []};
						}
					} else if (cond.value === "user_state") {
						conditionValue = Array.isArray(conditionValue) ? (conditionValue[0] || "") : (conditionValue || "");
					} else if (cond.inputType === "multiselect" || cond.inputType === "checkbox") {
						conditionValue = Array.isArray(conditionValue) ? conditionValue : (conditionValue ? [conditionValue] : []);
					} else if (cond.operator === "empty" || cond.operator === "not empty") {
						conditionValue = null;
					}

					return {
						type: cond.value,
						id: cond.id,
						value: conditionValue,
					};
				}
			}),
		};
		onGroupUpdate(updatedGroup);
	}, [conditions, logicGate, group.id]);
	return (
		<div className={`urcr-content-group ${isNested ? "urcr-nested-group" : ""}`}>
			{isAdvancedLogicEnabled && (
				<AdvancedLogicGates
					logicGate={logicGate}
					onLogicGateChange={setLogicGate}
				/>
			)}
			<div className="urcr-rule-body ur-p-2">
				<div className="urcr-condition-row-parent">
					{conditions.length > 0 && (
						<div
							className={`urcr-conditions-list ${isAdvancedLogicEnabled ? "urcr-conditional-logic-definitions" : ""}`}
						>
							{isAdvancedLogicEnabled && (
								<div className={`urcr-condition-logic-gate-wrapper urcr-logic-group-rule-${logicGate}`}>
									<div
										className={`urcr-condition-logic-gate-button urcr-sub-logic-group-rule-${logicGate}`}
									>
										{logicGate}
									</div>
								</div>
							)}
							{conditions.map((condition) => {
								if (condition.type === "group") {
									return (
										<div key={condition.id} className="urcr-condition-wrapper">
											<RuleGroup
												group={condition.group}
												onGroupUpdate={(updatedGroup) => handleGroupUpdate(condition.id, updatedGroup)}
												onGroupRemove={() => handleGroupRemove(condition.id)}
												isNested={true}
											/>
											<button
												type="button"
												className="button button-link-delete"
												onClick={() => handleGroupRemove(condition.id)}
												aria-label={__("Remove group", "user-registration")}
											>
												<span className="dashicons dashicons-no-alt"></span>
											</button>
										</div>
									);
								} else {
									return (
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
									);
								}
							})}
						</div>
					)}

					{/* Access Control Section - Only show for root level groups */}
					{!isNested && conditions.length > 0 && accessControl && (
						<AccessControlSection
							accessControl={accessControl}
							onAccessControlChange={onAccessControlChange}
							contentTargets={contentTargets}
							onContentTargetsChange={onContentTargetsChange}
						/>
					)}
				</div>

				<div className="urcr-buttons-wrapper" style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
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

					{isProAccess() && (
						<button
							type="button"
							className="button urcr-add-group-button"
							onClick={handleAddGroup}
						>
							<span className="dashicons dashicons-plus-alt2"></span>
							{__("Group", "user-registration")}
						</button>
					)}
				</div>
			</div>
		</div>
	);
};

export default RuleGroup;

