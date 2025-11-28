/**
 * External Dependencies
 */
import React, { useState, useEffect, useCallback } from "react";
import { __ } from "@wordpress/i18n";
import { getAllRules } from "./api/content-access-rules-api";
import RuleCard from "./components/rules/RuleCard";
import AddNewRuleModal from "./components/modals/AddNewRuleModal";
import { showError } from "./utils/notifications";
import { getURCRLocalizedData, getURCRData } from "./utils/localized-data";

/* global _UR_DASHBOARD_ */
const { adminURL } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const ContentAccessRules = () => {
	const [rules, setRules] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState(null);
	const [expandedRules, setExpandedRules] = useState(new Set());
	const [openSettingsPanels, setOpenSettingsPanels] = useState(new Set());
	const [isModalOpen, setIsModalOpen] = useState(false);

	// Access urcr_localized_data
	const urcrData = getURCRLocalizedData();

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

	// For testing: Auto-expand all rules when they're loaded
	useEffect(() => {
		if (rules.length > 0) {
			const allRuleIds = new Set(rules.map((rule) => rule.id));
			setExpandedRules(allRuleIds);
		}
	}, [rules]);

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
	
	const handleRuleDeleteOrDuplicate = () => {
		// Refetch rules after delete or duplicate operations
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
		// Add the new rule to the list
		setRules((prevRules) => [...prevRules, newRule]);
		// Auto-expand the new rule
		setExpandedRules((prev) => new Set([...prev, newRule.id]));
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
					<h1>{__("All Rules", "user-registration")}</h1>
					<button type="button" className="urcr-add-new-button" onClick={handleOpenModal}>
						<span className="dashicons dashicons-plus-alt2"></span>
						{__("Add New", "user-registration")}
					</button>
				</div>

				<AddNewRuleModal isOpen={isModalOpen} onClose={handleCloseModal} onCreateSuccess={handleRuleCreated} />

				{rules.length === 0 ? (
					<div className="user-registration-card ur-text-center urcr-no-rules">
						<p>
							{__("No content access rules found.", "user-registration")}
						</p>
						<button type="button" className="button button-primary" onClick={handleOpenModal}>
							{__("Create Your First Rule", "user-registration")}
						</button>
					</div>
				) : (
					<div className="urcr-rules-list">
						{rules.map((rule) => (
							<RuleCard
								key={rule.id}
								rule={rule}
								isExpanded={expandedRules.has(rule.id)}
								isSettingsOpen={openSettingsPanels.has(rule.id)}
								onToggleExpand={() => handleToggleExpand(rule.id)}
								onToggleSettings={() => handleToggleSettings(rule.id)}
								onRuleUpdate={handleRuleUpdate}
								onRuleStatusUpdate={handleRuleStatusUpdate}
								onRuleDeleteOrDuplicate={handleRuleDeleteOrDuplicate}
							/>
						))}
					</div>
				)}
			</div>
		</div>
	);
};

export default ContentAccessRules;
