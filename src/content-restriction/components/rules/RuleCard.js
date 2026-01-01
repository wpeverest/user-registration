/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { toggleRuleStatus } from "../../api/content-access-rules-api";
import SettingsPanel from "../settings/SettingsPanel";
import RuleContentDisplay from "./RuleContentDisplay";
import DeleteRuleModal from "../modals/DeleteRuleModal";
import DuplicateRuleModal from "../modals/DuplicateRuleModal";
import { showSuccess, showError } from "../../utils/notifications";
import { isURDev } from "../../utils/localized-data";

/* global _UR_DASHBOARD_ */
const { adminURL } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const RuleCard = ({
	rule,
	isExpanded,
	isSettingsOpen,
	onToggleExpand,
	onToggleSettings,
	onRuleUpdate,
	onRuleStatusUpdate,
	onRuleDelete,
	onRuleDuplicate
}) => {
	const [isToggling, setIsToggling] = useState(false);
	const [menuOpen, setMenuOpen] = useState(false);
	const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
	const [isDuplicateModalOpen, setIsDuplicateModalOpen] = useState(false);
	const [activeTab, setActiveTab] = useState("rules");
	const menuWrapperRef = useRef(null);
	const isMembershipRule = rule.rule_type === "membership";
	const messageTabLabel = isMembershipRule ? __("Restriction Message", "user-registration") : __("Settings", "user-registration");

	const editUrl = adminURL
		? `${adminURL}admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=${rule.id}`
		: "#";

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				menuWrapperRef.current &&
				!menuWrapperRef.current.contains(event.target)
			) {
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
				if (onRuleStatusUpdate) {
					onRuleStatusUpdate(rule.id, newStatus);
				}
				showSuccess(
					response.message ||
						__("Rule status updated", "user-registration")
				);
			} else {
				showError(
					response.message ||
						__("Failed to update rule status", "user-registration")
				);
			}
		} catch (error) {
			showError(
				error.message || __("An error occurred", "user-registration")
			);
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
	const headerClass = `user-registration-card__header ur-d-flex ur-align-items-center ur-p-5 integration-header-info accordion${
		isExpanded ? " active" : ""
	}`;

	return (
		<div className="user-registration-card ur-mb-2 urcr-rule-card ">
			<div
				className={headerClass}
				onClick={(e) => {
					if (
						!e.target.closest(".integration-action") &&
						!e.target.closest(".ur-toggle-section")
					) {
						onToggleExpand();
					}
				}}
			>
				<div className="integration-detail urcr-integration-detail">
					<h3 className="user-registration-card__title">
						{rule.title}
					</h3>
					<span className="urcr-separator"> | </span>
					<span className="urcr-rule-id">ID: {formattedId}</span>
					<span className="urcr-separator"> | </span>
					<span className="urcr-status-label">
						{__("Status", "user-registration")} :
					</span>
					<div className="ur-toggle-section">
						<span
							className="user-registration-toggle-form"
							onClick={(e) => e.stopPropagation()}
						>
							<input
								type="checkbox"
								checked={rule.enabled}
								onChange={handleToggleStatus}
								disabled={isToggling}
							/>
							<span className="slider round"></span>
						</span>
						{isToggling && (
							<span className="urcr-toggle-loader spinner is-active"></span>
						)}
					</div>
				</div>

				<div className="integration-action urcr-integration-action">
					<div className="urcr-membership-tabs">
						<button
							className={`urcr-tab-button ${activeTab === "rules" ? "urcr-tab-active" : ""}`}
							type="button"
							onClick={(e) => {
								e.stopPropagation();
								setActiveTab("rules");
							}}
						>
							{__("Rules", "user-registration")}
						</button>
						<button
							className={`urcr-tab-button ${activeTab === "message" ? "urcr-tab-active" : ""}`}
							type="button"
							onClick={(e) => {
								e.stopPropagation();
								setActiveTab("message");
							}}
						>
							{messageTabLabel}
						</button>
					</div>
					<div className="urcr-menu-wrapper" ref={menuWrapperRef}>
						<button
							className={`urcr-menu-toggle button-link ${
								menuOpen ? "urcr-icon-active" : ""
							}`}
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
									disabled={
										(!isURDev() &&
											rule.rule_type === "membership") ||
										Boolean(rule.is_migrated)
									}
									onClick={(e) => {
										e.stopPropagation();
										if (
											!(
												(!isURDev() &&
													rule.rule_type ===
														"membership") ||
												Boolean(rule.is_migrated)
											)
										) {
											handleDeleteClick();
										}
									}}
									style={{
										opacity:
											(!isURDev() &&
												rule.rule_type ===
													"membership") ||
											Boolean(rule.is_migrated)
												? 0.5
												: 1,
										cursor:
											(!isURDev() &&
												rule.rule_type ===
													"membership") ||
											Boolean(rule.is_migrated)
												? "not-allowed"
												: "pointer"
									}}
								>
									<span className="dashicons dashicons-trash"></span>
									{__("Trash", "user-registration")}
								</button>
								<button
									className="urcr-menu-item urcr-menu-duplicate"
									type="button"
									disabled={
										!isURDev() &&
										rule.rule_type === "membership"
									}
									onClick={(e) => {
										e.stopPropagation();
										if (
											isURDev() ||
											rule.rule_type !== "membership"
										) {
											handleDuplicateClick();
										}
									}}
									style={{
										opacity:
											!isURDev() &&
											rule.rule_type === "membership"
												? 0.5
												: 1,
										cursor:
											!isURDev() &&
											rule.rule_type === "membership"
												? "not-allowed"
												: "pointer"
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
						className={`css-i6dzq1 ${
							isExpanded ? "urcr-icon-active" : ""
						}`}
						onClick={(e) => {
							e.stopPropagation();
							onToggleExpand();
						}}
						style={{
							transform: isExpanded
								? "rotate(180deg)"
								: "rotate(0deg)",
							transition: "transform 0.3s ease",
							cursor: "pointer"
						}}
					>
						<polyline points="6 9 12 15 18 9"></polyline>
					</svg>
				</div>
			</div>

			<div
				className="user-registration-card__body ur-p-3 integration-body-info"
				style={{ display: isExpanded ? "block" : "none" }}
			>
				<div className={activeTab === "rules" ? "urcr-tab-content urcr-tab-content-active" : "urcr-tab-content"}>
					<RuleContentDisplay
						rule={rule}
						onRuleUpdate={onRuleUpdate}
					/>
				</div>
				<div className={activeTab === "message" ? "urcr-tab-content urcr-tab-content-active" : "urcr-tab-content"}>
					<SettingsPanel
						rule={rule}
						onRuleUpdate={onRuleUpdate}
						onGoBack={() => setActiveTab("rules")}
					/>
				</div>
			</div>

			<DeleteRuleModal
				isOpen={isDeleteModalOpen}
				onClose={() => setIsDeleteModalOpen(false)}
				rule={rule}
				onDeleteSuccess={handleDeleteSuccess}
			/>

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
