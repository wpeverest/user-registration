/**
 * External Dependencies
 */
import React, { useState, useRef, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import { showError } from "../../utils/notifications";
import AccessControlSection from "./AccessControlSection";
import RuleGroup from "./RuleGroup";
import { getURCRLocalizedData, getURCRData } from "../../utils/localized-data";
import { saveRuleWithCollectiveData } from "../../utils/rule-save-helper";

/* global _URCR_DASHBOARD_ */
const { adminURL } =
	typeof _URCR_DASHBOARD_ !== "undefined" && _URCR_DASHBOARD_;

const RuleContentDisplay = ({ rule, onRuleUpdate, isToggling = false }) => {
	const [isSaving, setIsSaving] = useState(false);
	const [rootGroup, setRootGroup] = useState(null);
	const [accessControl, setAccessControl] = useState(
		rule.access_control ||
			(rule.content && rule.content.access_control) ||
			"access"
	);
	const [contentTargets, setContentTargets] = useState([]);

	const prevRuleIdRef = useRef(rule.id);
	const isUpdatingFromPropsRef = useRef(false);

	useEffect(() => {
		if (prevRuleIdRef.current === rule.id && rootGroup !== null) {
			return;
		}

		prevRuleIdRef.current = rule.id;
		isUpdatingFromPropsRef.current = true;

		if (rule.logic_map) {
			const initialGroup = {
				id: rule.logic_map.id || `x${Date.now()}`,
				type: "group",
				logic_gate: rule.logic_map.logic_gate || "AND",
				conditions: rule.logic_map.conditions || []
			};
			setRootGroup(initialGroup);

			const initialAccessControl =
				rule.access_control ||
				(rule.content && rule.content.access_control);
			if (initialAccessControl) {
				setAccessControl(initialAccessControl);
			}

			if (
				rule.target_contents &&
				Array.isArray(rule.target_contents) &&
				rule.target_contents.length > 0
			) {
				const convertedTargets = rule.target_contents.map((target) => {
					let type = target.type;
					if (type === "wp_pages") type = "pages";
					if (type === "wp_posts") type = "posts";

					let value =
						target.value ||
						(type === "whole_site" ? "whole_site" : []);
					if (type === "taxonomy" && target.taxonomy) {
						value = {
							taxonomy: target.taxonomy,
							value: Array.isArray(target.value)
								? target.value
								: []
						};
					}

					return {
						id: target.id || `x${Date.now()}`,
						type: type,
						label: getTypeLabel(type),
						value: value,
						drip: target.drip
					};
				});
				setContentTargets(convertedTargets);
			}
		} else {
			setRootGroup({
				id: `x${Date.now()}`,
				type: "group",
				logic_gate: "AND",
				conditions: []
			});
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [rule.id]);

	useEffect(() => {
		const newAccessControl =
			rule.access_control ||
			(rule.content && rule.content.access_control);
		if (newAccessControl && newAccessControl !== accessControl) {
			isUpdatingFromPropsRef.current = true;
			setAccessControl(newAccessControl);
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [rule.access_control, rule.content]);

	const urcrData = getURCRLocalizedData();

	const handleSave = async () => {
		setIsSaving(true);
		try {
			if (!rootGroup) {
				showError(__("No group data to save", "user-registration"));
				setIsSaving(false);
				return;
			}

			await saveRuleWithCollectiveData({
				rule,
				onRuleUpdate,
				contentData: {
					rootGroup,
					contentTargets,
					accessControl
				}
			});
		} catch (error) {
			// Error handled in saveRuleWithCollectiveData
		} finally {
			setIsSaving(false);
		}
	};

	const getTypeLabel = (type) => {
		const labels = {
			wp_pages: __("Pages", "user-registration"),
			wp_posts: __("Posts", "user-registration"),
			post_types: __("Post Types", "user-registration"),
			taxonomy: __("Taxonomy", "user-registration"),
			whole_site: __("Whole Site", "user-registration")
		};
		return labels[type] || type;
	};

	const handleRootGroupUpdate = (updatedGroup) => {
		setRootGroup(updatedGroup);
	};

	const isAdvancedLogicEnabled = Boolean(
		rule.is_advanced_logic_enabled || false
	);

	// Build content data from current state
	const buildContentDataFromState = () => {
		if (!rootGroup) {
			return null;
		}

		const targetContents = contentTargets.map((target) => {
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

		const logicConditions = rootGroup.conditions || [];
		const logicMap = {
			type: "group",
			id: rootGroup.id || rule.logic_map?.id || `x${Date.now()}`,
			conditions: logicConditions
		};

		if (isAdvancedLogicEnabled) {
			logicMap.logic_gate =
				rootGroup.logic_gate || rule.logic_map?.logic_gate || "AND";
		}

		return { targetContents, logicMap };
	};

	const hasInitialized = useRef(false);
	const prevContentState = useRef(null);

	useEffect(() => {
		hasInitialized.current = false;
		prevContentState.current = null;
	}, [rule.id]);

	useEffect(() => {
		if (!rootGroup) {
			return;
		}

		if (isUpdatingFromPropsRef.current) {
			prevContentState.current = {
				rootGroup: JSON.stringify(rootGroup),
				contentTargets: JSON.stringify(contentTargets),
				accessControl
			};
			hasInitialized.current = true;
			isUpdatingFromPropsRef.current = false;
			return;
		}

		if (!hasInitialized.current) {
			hasInitialized.current = true;
			prevContentState.current = {
				rootGroup: JSON.stringify(rootGroup),
				contentTargets: JSON.stringify(contentTargets),
				accessControl
			};
			return;
		}

		const currentState = {
			rootGroup: JSON.stringify(rootGroup),
			contentTargets: JSON.stringify(contentTargets),
			accessControl
		};

		const stateChanged =
			!prevContentState.current ||
			prevContentState.current.rootGroup !== currentState.rootGroup ||
			prevContentState.current.contentTargets !==
				currentState.contentTargets ||
			prevContentState.current.accessControl !==
				currentState.accessControl;

		if (!stateChanged) {
			return;
		}

		const contentData = buildContentDataFromState();

		if (contentData) {
			const ruleData = rule.content || rule;
			const currentActions =
				rule.actions ||
				(rule.content && rule.content.actions) ||
				ruleData.actions ||
				[];

			if (onRuleUpdate) {
				const updatedRule = {
					...rule,
					access_control: accessControl,
					logic_map: contentData.logicMap,
					target_contents: contentData.targetContents,
					actions: currentActions,
					content: {
						...(rule.content || {}),
						access_control: accessControl,
						logic_map: contentData.logicMap,
						target_contents: contentData.targetContents,
						actions: currentActions
					}
				};
				onRuleUpdate(updatedRule);
			}
		}

		prevContentState.current = currentState;
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [rootGroup, contentTargets, accessControl]);

	if (!rootGroup) {
		return <div>Loading...</div>;
	}

	return (
		<div className="urcr-rule-content-panel">
			<RuleGroup
				group={rootGroup}
				onGroupUpdate={handleRootGroupUpdate}
				onGroupRemove={() => {}}
				isNested={false}
				accessControl={accessControl}
				onAccessControlChange={setAccessControl}
				contentTargets={contentTargets}
				onContentTargetsChange={setContentTargets}
				isMigrated={Boolean(rule.is_migrated)}
				ruleType={rule.rule_type}
				rule={rule}
			/>

			<div className="urcr-rule-actions">
				<button
					className="urcr-save-rule-btn button button-primary"
					type="button"
					onClick={handleSave}
					disabled={isSaving || isToggling}
					data-rule-id={rule.id}
				>
					{isSaving
						? __("Saving...", "user-registration")
						: __("Save", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default RuleContentDisplay;
