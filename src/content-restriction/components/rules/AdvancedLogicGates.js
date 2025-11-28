/**
 * External Dependencies
 */
import React from "react";
import {__} from "@wordpress/i18n";
import {getURCRData} from "../../utils/localized-data";

const AdvancedLogicGates = ({
	logicGate = "AND",
	onLogicGateChange,
}) => {

	const handleLogicGateClick = (gate) => {
		if (onLogicGateChange) {
			onLogicGateChange(gate);
		}
	};

	return (
		<div className="urcr-advanced-logic-gates">
			<label className="urcr-advanced-logic-label">
				{__("Add Logic:", "user-registration")}
			</label>
			<div className="urcr-logic-gate-buttons">
				<button
					type="button"
					className={`urcr-logic-gate-button ${logicGate === "AND" ? "active" : ""}`}
					onClick={() => handleLogicGateClick("AND")}
					title={__("AND", "user-registration")}
				>
					{__("AND", "user-registration")}
				</button>
				<button
					type="button"
					className={`urcr-logic-gate-button ${logicGate === "OR" ? "active" : ""}`}
					onClick={() => handleLogicGateClick("OR")}
					title={__("OR", "user-registration")}
				>
					{__("OR", "user-registration")}
				</button>
				<button
					type="button"
					className={`urcr-logic-gate-button ${logicGate === "NOT" ? "active" : ""}`}
					onClick={() => handleLogicGateClick("NOT")}
					title={__("NOT", "user-registration")}
				>
					{__("NOT", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default AdvancedLogicGates;

