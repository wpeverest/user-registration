export const hasAdvancedLogic = (logicMap) => {
	if (!logicMap || typeof logicMap !== 'object') {
		return false;
	}

	if (logicMap.type === 'group') {
		const logicGate = logicMap.logic_gate || 'AND';
		if (logicGate !== 'AND') {
			return true;
		}

		if (Array.isArray(logicMap.conditions)) {
			for (const condition of logicMap.conditions) {
				if (condition && condition.type === 'group') {
					return true;
				}
				if (hasAdvancedLogic(condition)) {
					return true;
				}
			}
		}
	}

	return false;
};

