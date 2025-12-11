/**
 * External Dependencies
 */
import React, {useState, useEffect, useRef} from "react";
import {__} from "@wordpress/i18n";
import {toggleRuleStatus} from "../../api/content-access-rules-api";
import SettingsPanel from "../settings/SettingsPanel";
import RuleContentDisplay from "./RuleContentDisplay";
import DeleteRuleModal from "../modals/DeleteRuleModal";
import DuplicateRuleModal from "../modals/DuplicateRuleModal";
import {showSuccess, showError} from "../../utils/notifications";

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
					  onRuleDelete,
					  onRuleDuplicate,
				  }) => {
	const [isToggling, setIsToggling] = useState(false);
	const [menuOpen, setMenuOpen] = useState(false);
	const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
	const [isDuplicateModalOpen, setIsDuplicateModalOpen] = useState(false);
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

	const handleDeleteClick = () => {
		setMenuOpen(false);
		setIsDeleteModalOpen(true);
	};

	const handleDeleteSuccess = () => {
		if (onRuleDelete) {
			onRuleDelete(rule.id);
		}
	};

	const handleDuplicateClick = () => {
		setMenuOpen(false);
		setIsDuplicateModalOpen(true);
	};

	const handleDuplicateSuccess = () => {
		if (onRuleDuplicate) {
			onRuleDuplicate();
		}
	};

	const formattedId = String(rule.id).padStart(2, "0");
	const headerClass = `user-registration-card__header ur-d-flex ur-align-items-center ur-p-5 integration-header-info accordion${isExpanded ? " active" : ""}`;

	return (
		<div className="user-registration-card ur-mb-2 urcr-rule-card ">
			{/* Header */}
			<div
				className={headerClass}
				onClick={(e) => {
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
										handleDeleteClick();
									}}
								>
									<span className="dashicons dashicons-trash"></span>
									{__("Trash", "user-registration")}
								</button>
								<button
									className="urcr-menu-item urcr-menu-duplicate"
									type="button"
									onClick={(e) => {
										e.stopPropagation();
										handleDuplicateClick();
									}}
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
				<div style={{display: isSettingsOpen ? "block" : "none"}}>
					<SettingsPanel
						rule={rule}
						onRuleUpdate={onRuleUpdate}
						onGoBack={onToggleSettings}
					/>
				</div>
				<div style={{display: !isSettingsOpen ? "block" : "none"}}>
					<RuleContentDisplay
						rule={rule}
						onRuleUpdate={onRuleUpdate}
					/>
				</div>
			</div>

			{/* Delete Modal */}
			<DeleteRuleModal
				isOpen={isDeleteModalOpen}
				onClose={() => setIsDeleteModalOpen(false)}
				rule={rule}
				onDeleteSuccess={handleDeleteSuccess}
			/>

			{/* Duplicate Modal */}
			<DuplicateRuleModal
				isOpen={isDuplicateModalOpen}
				onClose={() => setIsDuplicateModalOpen(false)}
				rule={rule}
				onDuplicateSuccess={handleDuplicateSuccess}
			/>
		</div>
	);
};

export default RuleCard;
