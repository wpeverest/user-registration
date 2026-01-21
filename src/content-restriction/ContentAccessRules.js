/**
 * External Dependencies
 */
import React, { useState, useEffect, useCallback } from "react";
import { __ } from "@wordpress/i18n";
import { getAllRules } from "./api/content-access-rules-api";
import RuleCard from "./components/rules/RuleCard";
import AddNewRuleModal from "./components/modals/AddNewRuleModal";
import { showError } from "./utils/notifications";
import {
	getURCRLocalizedData,
	getURCRData,
	isProAccess
} from "./utils/localized-data";

/* global _URCR_DASHBOARD_ */
const { adminURL, assetsURL } =
	typeof _URCR_DASHBOARD_ !== "undefined" && _URCR_DASHBOARD_
		? _URCR_DASHBOARD_
		: {};

const ContentAccessRules = () => {
	const [rules, setRules] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState(null);
	const [expandedRules, setExpandedRules] = useState(new Set());
	const [openSettingsPanels, setOpenSettingsPanels] = useState(new Set());
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [activeTab, setActiveTab] = useState("custom");
	const [highlightedRuleId, setHighlightedRuleId] = useState(null);
	const [hasAppliedHighlight, setHasAppliedHighlight] = useState(false);

	const urcrData = getURCRLocalizedData();
	const hasMultipleMemberships = getURCRData(
		"has_multiple_memberships",
		false
	);
	const isContentRestrictionEnabled = getURCRData(
		"is_content_restriction_enabled",
		false
	);

	const fetchRules = useCallback(() => {
		setIsLoading(true);
		setError(null);
		getAllRules()
			.then((data) => {
				if (data.success) {
					setRules(data.rules || []);
				} else {
					const errorMsg =
						data.message ||
						__("Failed to load rules", "user-registration");
					setError(errorMsg);
					showError(errorMsg);
				}
			})
			.catch((err) => {
				const errorMessage =
					err.message ||
					__(
						"An error occurred while loading rules",
						"user-registration"
					);
				setError(errorMessage);
				showError(errorMessage);
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, []);

	useEffect(() => {
		fetchRules();
	}, [fetchRules]);

	// Get rule ID from URL params
	useEffect(() => {
		const urlParams = new URLSearchParams(window.location.search);
		const ruleId = urlParams.get("id");
		if (ruleId) {
			const ruleIdNum = parseInt(ruleId, 10);
			if (!isNaN(ruleIdNum)) {
				setHighlightedRuleId(ruleIdNum);
			}
		}
	}, []);

	const membershipRules = rules.filter(
		(rule) => rule.rule_type === "membership"
	);
	const customRules = rules.filter(
		(rule) => rule.rule_type !== "membership" || !rule.rule_type
	);
	const shouldShowMembershipTab =
		hasMultipleMemberships && membershipRules.length > 1;
	const shouldShowCustomTab = isContentRestrictionEnabled;
	const shouldShowTabSwitcher =
		isContentRestrictionEnabled && shouldShowMembershipTab;

	const currentRules = !isContentRestrictionEnabled
		? shouldShowMembershipTab
			? membershipRules
			: []
		: activeTab === "membership"
		? membershipRules
		: customRules;

	const [hasSetDefaultTab, setHasSetDefaultTab] = useState(false);
	useEffect(() => {
		if (!hasSetDefaultTab && !isLoading) {
			if (!isContentRestrictionEnabled) {
				setActiveTab("membership");
				setHasSetDefaultTab(true);
			} else if (shouldShowMembershipTab && membershipRules.length > 0) {
				setActiveTab("membership");
				setHasSetDefaultTab(true);
			} else if (!shouldShowMembershipTab) {
				setActiveTab("custom");
				setHasSetDefaultTab(true);
			}
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [rules, isLoading, hasMultipleMemberships, isContentRestrictionEnabled]);

	useEffect(() => {
		if (!isLoading && rules.length > 0) {
			const allRuleIds = new Set(rules.map((rule) => rule.id));
			setExpandedRules(allRuleIds);
		}
	}, [rules, isLoading]);

	// Handle rule highlighting: switch tab, expand rule, and scroll to it (only once on initial load)
	useEffect(() => {
		if (
			highlightedRuleId &&
			!isLoading &&
			rules.length > 0 &&
			!hasAppliedHighlight
		) {
			const targetRule = rules.find(
				(rule) => rule.id === highlightedRuleId
			);
			if (targetRule) {
				// Determine which tab the rule belongs to
				const isMembershipRule = targetRule.rule_type === "membership";

				// Switch to the correct tab only on initial load
				if (isMembershipRule && shouldShowMembershipTab) {
					setActiveTab("membership");
				} else if (!isMembershipRule && shouldShowCustomTab) {
					setActiveTab("custom");
				}

				// Expand the rule
				setExpandedRules(
					(prev) => new Set([...prev, highlightedRuleId])
				);

				// Scroll to the rule after a short delay to allow DOM update
				setTimeout(() => {
					const ruleElement = document.querySelector(
						`.urcr-rule-card[data-rule-id="${highlightedRuleId}"]`
					);
					if (ruleElement) {
						ruleElement.scrollIntoView({
							behavior: "smooth",
							block: "center"
						});
					}
				}, 300);

				// Mark highlight as applied so it doesn't run again
				setHasAppliedHighlight(true);

				// Clear highlight after 3 seconds
				setTimeout(() => {
					setHighlightedRuleId(null);
				}, 3000);
			}
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [highlightedRuleId, isLoading, rules, hasAppliedHighlight]);

	const handleToggleExpand = (ruleId) => {
		setExpandedRules((prev) => {
			const newSet = new Set(prev);
			if (newSet.has(ruleId)) {
				newSet.delete(ruleId);
			} else {
				newSet.add(ruleId);
			}
			return newSet;
		});
	};

	const handleToggleSettings = (ruleId) => {
		setOpenSettingsPanels((prev) => {
			const newSet = new Set(prev);
			if (newSet.has(ruleId)) {
				newSet.delete(ruleId);
			} else {
				newSet.add(ruleId);
			}
			return newSet;
		});
	};

	const handleRuleUpdate = (updatedRule) => {
		if (updatedRule) {
			setRules((prevRules) =>
				prevRules.map((rule) =>
					rule.id === updatedRule.id
						? { ...rule, ...updatedRule }
						: rule
				)
			);
		}
	};

	const handleRuleDelete = (ruleId) => {
		setRules((prevRules) => prevRules.filter((rule) => rule.id !== ruleId));
		setExpandedRules((prev) => {
			const newSet = new Set(prev);
			newSet.delete(ruleId);
			return newSet;
		});
		setOpenSettingsPanels((prev) => {
			const newSet = new Set(prev);
			newSet.delete(ruleId);
			return newSet;
		});
	};

	const handleRuleDuplicate = () => {
		fetchRules();
	};

	const handleRuleStatusUpdate = (ruleId, enabled) => {
		setRules((prevRules) =>
			prevRules.map((rule) =>
				rule.id === ruleId
					? {
							...rule,
							enabled,
							content: {
								...(rule.content || {}),
								enabled
							}
						}
					: rule
			)
		);
	};

	const handleOpenModal = () => {
		setIsModalOpen(true);
	};

	const handleCloseModal = () => {
		setIsModalOpen(false);
	};

	const handleRuleCreated = (newRule) => {
		setRules((prevRules) => [newRule, ...prevRules]);
		setExpandedRules((prev) => new Set([...prev, newRule.id]));
		setActiveTab("custom");
	};

	if (isLoading) {
		return (
			<div className="user-registration-content-restriction-viewer">
				<div className="urcr-loading-container">
					<span className="spinner is-active"></span>
				</div>
			</div>
		);
	}

	if (error && rules.length === 0) {
		return (
			<div className="user-registration-content-restriction-viewer">
				<div className="urcr-error-container">
					<p>{error}</p>
					<button
						className="button button-primary"
						onClick={fetchRules}
					>
						{__("Retry", "user-registration")}
					</button>
				</div>
			</div>
		);
	}

	return (
		<div className="user-registration-content-restriction-viewer">
			<div className="urcr-viewer-container">
				<div className="urcr-header">
					<h1>{__("Content Rules", "user-registration")}</h1>
					{isProAccess() &&
						isContentRestrictionEnabled &&
						activeTab === "custom" && (
							<button
								type="button"
								className="urcr-add-new-button"
								onClick={handleOpenModal}
							>
								<span className="dashicons dashicons-plus-alt2"></span>
								{__("Add New", "user-registration")}
							</button>
						)}
				</div>

				{shouldShowTabSwitcher && (
					<div className="urcr-tabs">
						<button
							type="button"
							className={`urcr-tab ${
								activeTab === "membership"
									? "urcr-tab-active"
									: ""
							}`}
							onClick={() => setActiveTab("membership")}
						>
							{__("Membership Rules", "user-registration")}
						</button>
						{shouldShowCustomTab && (
							<button
								type="button"
								className={`urcr-tab ${
									activeTab === "custom"
										? "urcr-tab-active"
										: ""
								}`}
								onClick={() => setActiveTab("custom")}
							>
								{__("Custom Rules", "user-registration")}
							</button>
						)}
					</div>
				)}

				<AddNewRuleModal
					isOpen={isModalOpen}
					onClose={handleCloseModal}
					onCreateSuccess={handleRuleCreated}
				/>

				{currentRules.length === 0 ? (
					<div className="user-registration-card ur-text-center urcr-no-rules">
						<img
							src={`${assetsURL || ""}images/empty-table.png`}
							alt={__("No rules found", "user-registration")}
						/>
					</div>
				) : (
					<div className="urcr-rules-list">
						{currentRules.map((rule) => (
							<RuleCard
								key={rule.id}
								rule={rule}
								isExpanded={expandedRules.has(rule.id)}
								isSettingsOpen={openSettingsPanels.has(rule.id)}
								isHighlighted={highlightedRuleId === rule.id}
								onToggleExpand={() =>
									handleToggleExpand(rule.id)
								}
								onToggleSettings={() =>
									handleToggleSettings(rule.id)
								}
								onRuleUpdate={handleRuleUpdate}
								onRuleStatusUpdate={handleRuleStatusUpdate}
								onRuleDelete={handleRuleDelete}
								onRuleDuplicate={handleRuleDuplicate}
							/>
						))}
					</div>
				)}
			</div>
		</div>
	);
};

export default ContentAccessRules;
