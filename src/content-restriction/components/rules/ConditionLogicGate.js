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
	rule = null,
}) => {
	const isAdvancedLogicEnabled = Boolean(rule?.is_advanced_logic_enabled || false);

	if (!isAdvancedLogicEnabled) {
		return null;
	}

	const showButton = isFirstCondition;

	const getGateColor = (gate) => {
		switch (gate) {
			case "AND":
				return {
					background: "#FED9A8",
					color: "#383838",
					lineColor: "#F9E2CA",
				};
			case "OR":
				return {
					background: "#E9F0FE",
					color: "#383838",
					lineColor: "#89A7E5",
				};
			case "NOT":
				return {
					background: "#F9CACA",
					color: "#383838",
					lineColor: "#F9CACA",
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

