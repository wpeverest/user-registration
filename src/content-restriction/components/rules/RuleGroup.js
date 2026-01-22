/**
 * External Dependencies
 */
import React, { useState, useRef, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import ConditionFieldDropdown from "../dropdowns/ConditionFieldDropdown";
import ConditionRow from "./ConditionRow";
import AdvancedLogicGates from "./AdvancedLogicGates";
import AccessControlSection from "./AccessControlSection";
import DropdownButton from "../dropdowns/DropdownButton";
import { getURCRData, isProAccess, isURDev } from "../../utils/localized-data";

const getConditionType = (conditionType) => {
	const typeMap = {
		roles: "multiselect",
		membership: "multiselect",
		capabilities: "multiselect",
		registration_source: "multiselect",
		ur_form_field: "multiselect",
		user_state: "checkbox",
		user_registered_date: "date",
		access_period: "period",
		email_domain: "text",
		post_count: "number"
	};
	return typeMap[conditionType] || "text";
};

const RuleGroup = ({
	group,
	onGroupUpdate,
	onGroupRemove,
	isNested = false,
	accessControl,
	onAccessControlChange,
	contentTargets,
	onContentTargetsChange,
	isMigrated = false,
	ruleType = null,
	rule = null
}) => {
	const [conditions, setConditions] = useState([]);
	const [logicGate, setLogicGate] = useState(group.logic_gate || "AND");
	const isAdvancedLogicEnabled = Boolean(
		rule?.is_advanced_logic_enabled || false
	);
	const isMembershipRule = ruleType === "membership";

	useEffect(() => {
		if (group.conditions && group.conditions.length > 0) {
			const initialConditions = group.conditions.map((cond) => {
				if (cond.type === "group") {
					return {
						type: "group",
						id: cond.id,
						group: cond
					};
				} else {
					let conditionValue = cond.value;
					const conditionType = getConditionType(cond.type);

					if (cond.type === "registration_source") {
						// Keep registration_source as is, normalize value to array
						if (Array.isArray(conditionValue)) {
							conditionValue = conditionValue;
						} else if (conditionValue) {
							conditionValue = [conditionValue];
						} else {
							conditionValue = [];
						}
						return {
							type: "condition",
							id: cond.id || `x${Date.now()}`,
							value: "registration_source",
							label: "registration_source",
							inputType: "multiselect",
							operator: "is",
							conditionValue: conditionValue
						};
					}

					if (cond.type === "ur_form_field") {
						// Keep ur_form_field as is
						if (
							conditionValue &&
							typeof conditionValue === "object" &&
							conditionValue.form_id
						) {
							conditionValue = {
								form_id: conditionValue.form_id || "",
								form_fields: Array.isArray(
									conditionValue.form_fields
								)
									? conditionValue.form_fields
									: []
							};
						} else {
							conditionValue = { form_id: "", form_fields: [] };
						}
						return {
							type: "condition",
							id: cond.id || `x${Date.now()}`,
							value: "ur_form_field",
							label: "ur_form_field",
							inputType: "multiselect",
							operator: "is",
							conditionValue: conditionValue
						};
					}

					if (cond.type === "urm") {
						if (typeof conditionValue === "string" && conditionValue) {
							conditionValue = conditionValue;
						} else if (conditionValue && typeof conditionValue === "object") {
							if (conditionValue.form_id) {
								conditionValue = {
									form_id: conditionValue.form_id || "",
									form_fields: Array.isArray(conditionValue.form_fields) 
										? conditionValue.form_fields 
										: []
								};
							} else {
								conditionValue = "";
							}
						} else {
							conditionValue = "";
						}
					} else if (cond.type === "user_state") {
						if (Array.isArray(conditionValue)) {
							conditionValue = conditionValue[0] || "";
						}
						if (conditionValue === "logged_in") {
							conditionValue = "logged-in";
						} else if (conditionValue === "logged_out") {
							conditionValue = "logged-out";
						}
					} else if (!conditionValue) {
						conditionValue =
							conditionType === "multiselect" ? [] : "";
					}

					return {
						type: "condition",
						id: cond.id || `x${Date.now()}`,
						value: cond.type,
						label: cond.type,
						inputType: conditionType,
						operator: "is",
						conditionValue: conditionValue
					};
				}
			});
			
			setConditions(initialConditions);
		} else {
			setConditions([]);
		}

		if (!isAdvancedLogicEnabled || isMembershipRule) {
			setLogicGate("AND");
		} else if (group.logic_gate) {
			setLogicGate(group.logic_gate);
		}
	}, [group.id, isAdvancedLogicEnabled, isMembershipRule]);

	// Force logic gate to AND when advanced logic is disabled (in case it changes dynamically)
	useEffect(() => {
		if (!isAdvancedLogicEnabled && logicGate !== "AND") {
			setLogicGate("AND");
		}
	}, [isAdvancedLogicEnabled, logicGate]);

	const handleAfterConditionSelection = (option) => {
		let initialValue = "";
		if (option.value === "ur_form_field") {
			initialValue = { form_id: "", form_fields: [] };
		} else if (option.type === "multiselect") {
			initialValue = [];
		} else if (
			option.type === "checkbox" &&
			option.value !== "user_state"
		) {
			initialValue = [];
		}

		const newCondition = {
			type: "condition",
			id: `x${Date.now()}`,
			value: option.value,
			label: option.label,
			inputType: option.type,
			operator: "is",
			conditionValue: initialValue
		};
		setConditions([...conditions, newCondition]);
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
				conditions: []
			}
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
		// Note: Flattening is now handled on the backend API side when advanced logic is disabled
		const conditionsToSerialize = conditions.map((cond) => {
			if (cond.type === "group") {
				return cond.group;
			} else {
				let conditionValue = cond.conditionValue;
				
				// Handle urm type - can be string (form_id only) or object (form_id + form_fields)
				if (cond.value === "urm") {
					if (typeof conditionValue === "object" && conditionValue !== null) {
						if (conditionValue.form_id && Array.isArray(conditionValue.form_fields)) {
							conditionValue = {
								form_id: conditionValue.form_id,
								form_fields: conditionValue.form_fields.filter(
									(field) => field.field_name && field.operator
								)
							};
						} else if (conditionValue.form_id) {
							conditionValue = conditionValue.form_id;
						} else {
							conditionValue = "";
						}
					} else if (typeof conditionValue === "string") {
						conditionValue = conditionValue;
					} else {
						conditionValue = "";
					}
				} else if (cond.value === "registration_source") {
					// Serialize registration_source as array
					conditionValue = Array.isArray(conditionValue)
						? conditionValue
						: conditionValue
							? [conditionValue]
							: [];
				} else if (
					cond.value === "ur_form_field" &&
					typeof conditionValue === "object"
				) {
					if (
						conditionValue.form_id &&
						Array.isArray(conditionValue.form_fields)
					) {
						conditionValue = {
							form_id: conditionValue.form_id,
							form_fields: conditionValue.form_fields.filter(
								(field) => field.field_name && field.operator
							)
						};
					} else {
						conditionValue = { form_id: "", form_fields: [] };
					}
				} else if (cond.value === "user_state") {
					conditionValue = Array.isArray(conditionValue)
						? conditionValue[0] || ""
						: conditionValue || "";
				} else if (
					cond.inputType === "multiselect" ||
					cond.inputType === "checkbox"
				) {
					conditionValue = Array.isArray(conditionValue)
						? conditionValue
						: conditionValue
							? [conditionValue]
							: [];
				} else if (
					cond.operator === "empty" ||
					cond.operator === "not empty"
				) {
					conditionValue = null;
				}

				return {
					type: cond.value,
					id: cond.id,
					value: conditionValue
				};
			}
		});

		const updatedGroup = {
			id: group.id,
			type: "group",
			logic_gate:
				isAdvancedLogicEnabled && !isMembershipRule ? logicGate : "AND", // Force AND when advanced logic is disabled or for membership rules
			conditions: conditionsToSerialize
		};
		onGroupUpdate(updatedGroup);
	}, [
		conditions,
		logicGate,
		group.id,
		isAdvancedLogicEnabled,
		isMembershipRule
	]);
	return (
		<div
			className={`urcr-content-group ${
				isNested ? "urcr-nested-group" : ""
			}`}
		>
			{isAdvancedLogicEnabled && !isMembershipRule && (
				<AdvancedLogicGates
					logicGate={logicGate}
					onLogicGateChange={setLogicGate}
				/>
			)}
			<div className="urcr-rule-body ur-p-2">
				<div className="urcr-condition-row-parent">
					{conditions.length > 0 && (
						<div
							className={`urcr-conditions-list ${
								isAdvancedLogicEnabled && !isMembershipRule
									? "urcr-conditional-logic-definitions"
									: ""
							}`}
						>
							{isAdvancedLogicEnabled && !isMembershipRule && (
								<div
									className={`urcr-condition-logic-gate-wrapper urcr-logic-group-rule-${logicGate} ${
										conditions.length === 1
											? "urcr-single-condition"
											: ""
									}`}
								>
									<div
										className={`urcr-condition-logic-gate-button urcr-sub-logic-group-rule-${logicGate}`}
									>
										{logicGate}
									</div>
								</div>
							)}
							{conditions.map((condition, index) => {
								if (condition.type === "group") {
									// Only show nested groups when advanced logic is enabled
									if (!isAdvancedLogicEnabled) {
										return null;
									}
									return (
										<div
											key={condition.id}
											className="urcr-condition-wrapper"
										>
											<RuleGroup
												group={condition.group}
												onGroupUpdate={(updatedGroup) =>
													handleGroupUpdate(
														condition.id,
														updatedGroup
													)
												}
												onGroupRemove={() =>
													handleGroupRemove(
														condition.id
													)
												}
												isNested={true}
												isMigrated={isMigrated}
												ruleType={ruleType}
												rule={rule}
											/>
											{!isMembershipRule && (
												<button
													type="button"
													className="button button-link-delete"
													onClick={() =>
														handleGroupRemove(
															condition.id
														)
													}
													aria-label={__(
														"Remove group",
														"user-registration"
													)}
												>
													<span className="dashicons dashicons-no-alt"></span>
												</button>
											)}
										</div>
									);
								} else {
									// Check if this is the first condition in a membership rule or migrated rule
									const isFirstCondition =
										(isMembershipRule || isMigrated) &&
										index === 0;
									// For membership rules and migrated rules, only hide remove button for first condition (unless UR_DEV is enabled)
									const shouldShowRemoveButton =
										(!isMembershipRule && !isMigrated) ||
										!isFirstCondition ||
										isURDev();
									return (
										<div
											key={condition.id}
											className="urcr-condition-wrapper"
										>
											<ConditionRow
												condition={condition}
												onUpdate={handleConditionUpdate}
												isMigrated={isMigrated}
												isLocked={isFirstCondition}
												ruleType={ruleType}
												isFirstCondition={
													isFirstCondition
												}
											/>
											{shouldShowRemoveButton && (
												<button
													type="button"
													className="button button-link-delete"
													onClick={() =>
														handleConditionRemove(
															condition.id
														)
													}
													aria-label={__(
														"Remove condition",
														"user-registration"
													)}
												>
													<span className="dashicons dashicons-no-alt"></span>
												</button>
											)}
										</div>
									);
								}
							})}

							{rule && rule?.is_global && (
								<div className="urcr-condition-wrapper urcr-global-migration-notice">
									<p className="urcr-notice-wrap">
										<span style={{ fontWeight: "bold" }}>
											Note:
										</span>
										{__(
											" For partial content restriction with the above condition, use the ",
											"user-registration"
										)}
										<span style={{ fontWeight: "bold" }}>
											[urcr_restrict]...[/urcr_restrict]
										</span>
										{__(
											" shortcode. It may be deprecated",
											"user-registration"
										)}
										{true &&
											__(
												"—use the Content Restriction block instead",
												"user-registration"
											)}
										.
									</p>
								</div>
							)}
						</div>
					)}

					{/* Access Control Section - Only show for root level groups */}
					{!isNested && conditions.length > 0 && accessControl && (
						<AccessControlSection
							accessControl={accessControl}
							onAccessControlChange={onAccessControlChange}
							contentTargets={contentTargets}
							onContentTargetsChange={onContentTargetsChange}
							ruleType={ruleType}
							rule={rule}
							conditions={conditions}
						/>
					)}
				</div>

				<div
					className="urcr-buttons-wrapper"
					style={{ display: "flex", gap: "10px", marginTop: "10px" }}
				>
					{isProAccess() && !isMembershipRule && !isMigrated && (
						<DropdownButton
							buttonContent={
								<>
									<span className="dashicons dashicons-plus-alt2"></span>
									{__("Condition", "user-registration")}
								</>
							}
							options={[]}
							onSelect={handleAfterConditionSelection}
							buttonClassName="button urcr-add-condition-button"
							wrapperClassName="urcr-condition-dropdown-wrapper"
							renderDropdown={() => (
								<ConditionFieldDropdown
									onSelect={handleAfterConditionSelection}
									isMigrated={isMigrated}
									ruleType={ruleType}
									isFirstCondition={
										isMembershipRule &&
										conditions.length === 0
									}
								/>
							)}
						/>
					)}

					{isProAccess() &&
						isAdvancedLogicEnabled &&
						!isMembershipRule &&
						!isMigrated && (
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
