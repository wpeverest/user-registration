/**
 * External Dependencies
 */
import React, { useState, useEffect, useCallback } from "react";
import { __ } from "@wordpress/i18n";
import { getAllRules } from "./api/content-access-rules-api";
import RuleCard from "./components/rules/RuleCard";
import AddNewRuleModal from "./components/modals/AddNewRuleModal";
import { showError } from "./utils/notifications";
import { getURCRLocalizedData, getURCRData, isProAccess } from "./utils/localized-data";

/* global _UR_DASHBOARD_ */
const { adminURL, assetsURL } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_ ? _UR_DASHBOARD_ : {};

const ContentAccessRules = () => {
	const [rules, setRules] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState(null);
	const [expandedRules, setExpandedRules] = useState(new Set());
	const [openSettingsPanels, setOpenSettingsPanels] = useState(new Set());
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [activeTab, setActiveTab] = useState("custom"); // 'membership' or 'custom'

	// Access urcr_localized_data
	const urcrData = getURCRLocalizedData();
	const hasMultipleMemberships = getURCRData("has_multiple_memberships", false);

	const fetchRules = useCallback(() => {
		setIsLoading(true);
		setError(null);
		getAllRules()
			.then((data) => {
				if (data.success) {
					setRules(data.rules || []);
				} else {
					const errorMsg = data.message || __("Failed to load rules", "user-registration");
					setError(errorMsg);
					showError(errorMsg);
				}
			})
			.catch((err) => {
				const errorMessage = err.message || __("An error occurred while loading rules", "user-registration");
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

	// Filter rules by type
	const membershipRules = rules.filter((rule) => rule.rule_type === "membership");
	const customRules = rules.filter((rule) => rule.rule_type !== "membership" || !rule.rule_type);

	// Show membership rules tab only if there are multiple memberships AND more than 1 membership rule
	const shouldShowMembershipTab = hasMultipleMemberships && membershipRules.length > 1;

	// Get rules for current tab
	const currentRules = activeTab === "membership" ? membershipRules : customRules;

	// Set default tab based on membership count (only once when rules are loaded)
	const [hasSetDefaultTab, setHasSetDefaultTab] = useState(false);
	useEffect(() => {
		if (!hasSetDefaultTab && !isLoading && shouldShowMembershipTab && membershipRules.length > 0) {
			// If we have multiple memberships and membership rules, default to membership tab
			setActiveTab("membership");
			setHasSetDefaultTab(true);
		} else if (!hasSetDefaultTab && !isLoading && !shouldShowMembershipTab) {
			// If we shouldn't show membership tab, ensure we're on custom tab
			setActiveTab("custom");
			setHasSetDefaultTab(true);
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [rules, isLoading, hasMultipleMemberships]);



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
		// If updatedRule is provided, update local state without refetching
		if (updatedRule) {
			setRules((prevRules) =>
				prevRules.map((rule) =>
					rule.id === updatedRule.id ? { ...rule, ...updatedRule } : rule
				)
			);
		}
		// If called without parameter (delete/duplicate), refetch is needed
		// But for updates, we don't refetch - just update local state
	};

	const handleRuleDelete = (ruleId) => {
		// Remove deleted rule from local state without refetching
		setRules((prevRules) => prevRules.filter((rule) => rule.id !== ruleId));
		// Also remove from expanded and settings panels if present
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
		// Refetch rules after duplicate operation to get the new rule
		fetchRules();
	};

	const handleRuleStatusUpdate = (ruleId, enabled) => {
		setRules((prevRules) =>
			prevRules.map((rule) =>
				rule.id === ruleId ? { ...rule, enabled } : rule
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
		// Add the new rule to the top of the list
		setRules((prevRules) => [newRule, ...prevRules]);
		// Auto-expand the new rule
		setExpandedRules((prev) => new Set([...prev, newRule.id]));
		// Switch to custom tab if not already there
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
					<button className="button button-primary" onClick={fetchRules}>
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
					{isProAccess() && activeTab === "custom" && (
						<button type="button" className="urcr-add-new-button" onClick={handleOpenModal}>
							<span className="dashicons dashicons-plus-alt2"></span>
							{__("Add New", "user-registration")}
						</button>
					)}
				</div>

				{/* Tabs */}
				{shouldShowMembershipTab && (
					<div className="urcr-tabs">
						<button
							type="button"
							className={`urcr-tab ${activeTab === "membership" ? "urcr-tab-active" : ""}`}
							onClick={() => setActiveTab("membership")}
						>
							{__("Membership Rules", "user-registration")}
						</button>
						<button
							type="button"
							className={`urcr-tab ${activeTab === "custom" ? "urcr-tab-active" : ""}`}
							onClick={() => setActiveTab("custom")}
						>
							{__("Custom Rules", "user-registration")}
						</button>
					</div>
				)}

				<AddNewRuleModal isOpen={isModalOpen} onClose={handleCloseModal} onCreateSuccess={handleRuleCreated} />

				{currentRules.length === 0 ? (
					<div className="user-registration-card ur-text-center urcr-no-rules">
						<img
							src={`${assetsURL || ""}images/empty-table.png`}
							alt={__("No rules found", "user-registration")}
							style={{maxWidth: "100%", height: "auto", margin: "20px 0"}}
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
								onToggleExpand={() => handleToggleExpand(rule.id)}
								onToggleSettings={() => handleToggleSettings(rule.id)}
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
