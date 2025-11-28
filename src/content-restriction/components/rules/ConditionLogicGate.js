/**
 * External Dependencies
 */
import React from "react";
import {__} from "@wordpress/i18n";
import {getURCRData} from "../../utils/localized-data";

const ConditionLogicGate = ({
	logicGate = "AND",
	conditionIndex = 0,
	isFirstCondition = false,
	totalConditions = 0,
}) => {
	// Check if advanced logic is enabled
	const isAdvancedLogicEnabled = getURCRData("is_advanced_logic_enabled", false);

	// Don't render if advanced logic is not enabled
	if (!isAdvancedLogicEnabled) {
		return null;
	}

	// Show the logic gate button:
	// - For single condition: show on first condition
	// - For multiple conditions: show on first condition (it will be positioned at 50% which is between conditions)
	const showButton = isFirstCondition;

	// Get color based on logic gate (matching urcr-content-access-rule-creator.scss)
	const getGateColor = (gate) => {
		switch (gate) {
			case "AND":
				return {
					background: "#FED9A8", // $and-tag
					color: "#383838",
					lineColor: "#F9E2CA", // $and-line
				};
			case "OR":
				return {
					background: "#E9F0FE", // $or-tag
					color: "#383838",
					lineColor: "#89A7E5", // $or-line
				};
			case "NOT":
				return {
					background: "#F9CACA", // $not-tag
					color: "#383838",
					lineColor: "#F9CACA", // $not-line
				};
			default:
				return {
					background: "#FED9A8",
					color: "#383838",
					lineColor: "#F9E2CA",
				};
		}
	};

	const gateColor = getGateColor(logicGate);

	return (
		<div className={`urcr-condition-logic-gate-wrapper urcr-logic-group-rule-${logicGate}`}>
			{showButton && (
				<div 
					className={`urcr-condition-logic-gate-button urcr-sub-logic-group-rule-${logicGate}`}
					style={{
						backgroundColor: gateColor.background,
						color: gateColor.color,
					}}
				>
					{logicGate}
				</div>
			)}
		</div>
	);
};

export default ConditionLogicGate;

