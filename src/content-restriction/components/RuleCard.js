/**
 * External Dependencies
 */
import React, {useState, useEffect, useRef} from "react";
import {__} from "@wordpress/i18n";
import {toggleRuleStatus, deleteRule, duplicateRule} from "../api/content-access-rules-api";
import SettingsPanel from "./SettingsPanel";
import RuleContentDisplay from "./RuleContentDisplay";
import {showSuccess, showError} from "../utils/notifications";

/* global _UR_DASHBOARD_ */
const {adminURL} = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const RuleCard = ({
					  rule,
					  isExpanded,
					  isSettingsOpen,
					  onToggleExpand,
					  onToggleSettings,
					  onRuleUpdate,
					  onRuleStatusUpdate,
				  }) => {
	const [isToggling, setIsToggling] = useState(false);
	const [isDeleting, setIsDeleting] = useState(false);
	const [isDuplicating, setIsDuplicating] = useState(false);
	const [menuOpen, setMenuOpen] = useState(false);
	const menuWrapperRef = useRef(null);

	const editUrl = adminURL
		? `${adminURL}admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=${rule.id}`
		: "#";

	// Close menu when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (menuWrapperRef.current && !menuWrapperRef.current.contains(event.target)) {
				setMenuOpen(false);
			}
		};

		if (menuOpen) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [menuOpen]);

	const handleToggleStatus = async () => {
		const newStatus = !rule.enabled;
		setIsToggling(true);
		try {
			const response = await toggleRuleStatus(rule.id, newStatus);
			if (response.success) {
				// Update local state instead of reloading all rules
				if (onRuleStatusUpdate) {
					onRuleStatusUpdate(rule.id, newStatus);
				}
				showSuccess(response.message || __("Rule status updated", "user-registration"));
			} else {
				showError(response.message || __("Failed to update rule status", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsToggling(false);
		}
	};

	const handleDelete = async () => {
		if (!window.confirm(__("Are you sure you want to delete this rule?", "user-registration"))) {
			return;
		}

		setIsDeleting(true);
		setMenuOpen(false);
		try {
			const response = await deleteRule(rule.id, false);
			if (response.success) {
				showSuccess(response.message || __("Rule deleted successfully", "user-registration"));
				onRuleUpdate();
			} else {
				showError(response.message || __("Failed to delete rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsDeleting(false);
		}
	};

	const handleDuplicate = async () => {
		setIsDuplicating(true);
		setMenuOpen(false);
		try {
			const response = await duplicateRule(rule.id);
			if (response.success) {
				showSuccess(response.message || __("Rule duplicated successfully", "user-registration"));
				onRuleUpdate();
			} else {
				showError(response.message || __("Failed to duplicate rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsDuplicating(false);
		}
	};

	const formattedId = String(rule.id).padStart(2, "0");
	const headerClass = `user-registration-card__header ur-d-flex ur-align-items-center ur-p-4 integration-header-info accordion${isExpanded ? " active" : ""}`;

	return (
		<div className="user-registration-card ur-mb-2 urcr-rule-card">
			{/* Header */}
			<div
				className={headerClass}
				onClick={(e) => {
					// Only toggle if clicking on the header itself, not on action buttons
					if (!e.target.closest('.integration-action') && !e.target.closest('.urcr-status-toggle')) {
						onToggleExpand();
					}
				}}
			>
				<div className="integration-detail urcr-integration-detail">
					<h3 className="user-registration-card__title">
						{rule.title}
					</h3>
					<span className="urcr-separator"> | </span>
					<span className="urcr-rule-id">
						ID: {formattedId}
					</span>
					<span className="urcr-separator"> | </span>
					<span className="urcr-status-label">
						{__("Status", "user-registration")} :
					</span>
					<div className="urcr-status-toggle-wrapper">
						<label className="urcr-status-toggle" onClick={(e) => e.stopPropagation()}>
							<input
								type="checkbox"
								checked={rule.enabled}
								onChange={handleToggleStatus}
								disabled={isToggling}
							/>
							<span className="urcr-slider"></span>
						</label>
						{isToggling && (
							<span className="urcr-toggle-loader spinner is-active"></span>
						)}
					</div>
				</div>

				<div className="integration-action urcr-integration-action">
					<span className={`urcr-settings-text ${isSettingsOpen ? 'urcr-icon-active' : ''}`}>
						{__("Settings", "user-registration")}
					</span>
					<button
						className={`urcr-settings-icon button-link ${isSettingsOpen ? 'urcr-icon-active' : ''}`}
						type="button"
						onClick={(e) => {
							e.stopPropagation();
							onToggleSettings();
						}}
						aria-label={__("Settings", "user-registration")}
					>
						<span className="dashicons dashicons-admin-generic"></span>
					</button>
					<div className="urcr-menu-wrapper" ref={menuWrapperRef}>
						<button
							className={`urcr-menu-toggle button-link ${menuOpen ? 'urcr-icon-active' : ''}`}
							type="button"
							onClick={(e) => {
								e.stopPropagation();
								setMenuOpen(!menuOpen);
							}}
							aria-label={__("More options", "user-registration")}
						>
							<span className="dashicons dashicons-ellipsis"></span>
						</button>
						{menuOpen && (
							<div className="urcr-menu-dropdown">
								<button
									className="urcr-menu-item urcr-menu-trash"
									type="button"
									onClick={(e) => {
										e.stopPropagation();
										handleDelete();
									}}
									disabled={isDeleting}
								>
									<span className="dashicons dashicons-trash"></span>
									{__("Trash", "user-registration")}
								</button>
								<button
									className="urcr-menu-item urcr-menu-duplicate"
									type="button"
									onClick={(e) => {
										e.stopPropagation();
										handleDuplicate();
									}}
									disabled={isDuplicating}
								>
									<span className="dashicons dashicons-admin-page"></span>
									{__("Duplicate", "user-registration")}
								</button>
							</div>
						)}
					</div>
					<svg
						viewBox="0 0 24 24"
						width="24"
						height="24"
						stroke="currentColor"
						strokeWidth="2"
						fill="none"
						strokeLinecap="round"
						strokeLinejoin="round"
						className={`css-i6dzq1 ${isExpanded ? 'urcr-icon-active' : ''}`}
						onClick={(e) => {
							e.stopPropagation();
							onToggleExpand();
						}}
						style={{
							transform: isExpanded ? "rotate(180deg)" : "rotate(0deg)",
							transition: "transform 0.3s ease",
							cursor: "pointer"
						}}
					>
						<polyline points="6 9 12 15 18 9"></polyline>
					</svg>
				</div>
			</div>

			{/* Body */}
			<div
				className="user-registration-card__body ur-p-3 integration-body-info"
				style={{display: isExpanded ? "block" : "none"}}
			>
				{isSettingsOpen && (
					<SettingsPanel
						rule={rule}
						onRuleUpdate={onRuleUpdate}
					/>
				)}
				<RuleContentDisplay
					rule={rule}
					onRuleUpdate={onRuleUpdate}
				/>
			</div>
		</div>
	);
};

export default RuleCard;
