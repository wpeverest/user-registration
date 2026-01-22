/**
 * External Dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useState } from "react";
import { toggleRuleStatus } from "../../api/content-access-rules-api";
import { isURDev } from "../../utils/localized-data";
import { showError, showSuccess } from "../../utils/notifications";
import DeleteRuleModal from "../modals/DeleteRuleModal";
import DuplicateRuleModal from "../modals/DuplicateRuleModal";
import SettingsPanel from "../settings/SettingsPanel";
import RuleContentDisplay from "./RuleContentDisplay";

/* global _URCR_DASHBOARD_ */
const { adminURL } =
	typeof _URCR_DASHBOARD_ !== "undefined" && _URCR_DASHBOARD_;

const RuleCard = ({
	rule,
	isExpanded,
	isSettingsOpen,
	isHighlighted = false,
	onToggleExpand,
	onToggleSettings,
	onRuleUpdate,
	onRuleStatusUpdate,
	onRuleDelete,
	onRuleDuplicate
}) => {
	const [isToggling, setIsToggling] = useState(false);
	const [isSaving, setIsSaving] = useState(false);
	const [menuOpen, setMenuOpen] = useState(false);
	const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
	const [isDuplicateModalOpen, setIsDuplicateModalOpen] = useState(false);
	const [activeTab, setActiveTab] = useState("rules");
	const [isEditingTitle, setIsEditingTitle] = useState(false);
	const [editedTitle, setEditedTitle] = useState(rule.title || "");
	const menuWrapperRef = useRef(null);
	const titleInputRef = useRef(null);
	const isMembershipRule = rule.rule_type === "membership";
	const isMigratedRule = Boolean(rule.is_migrated);
	const shouldHideMenu = (!isURDev() && isMembershipRule) || isMigratedRule;
	const messageTabLabel = isMembershipRule
		? __("Restriction Message", "user-registration")
		: __("Settings", "user-registration");

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

	// Update editedTitle when rule.title changes
	useEffect(() => {
		setEditedTitle(rule.title || "");
	}, [rule.title]);

	// Focus input when entering edit mode
	useEffect(() => {
		if (isEditingTitle && titleInputRef.current) {
			titleInputRef.current.focus();
		}
	}, [isEditingTitle]);

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				isEditingTitle &&
				titleInputRef.current &&
				!titleInputRef.current.contains(event.target) &&
				!event.target.closest(".user-registration-editable-title__icon")
			) {
				if (onRuleUpdate) {
					onRuleUpdate({
						...rule,
						title: editedTitle.trim() || rule.title
					});
				}
				setIsEditingTitle(false);
			}
		};

		if (isEditingTitle) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [isEditingTitle, editedTitle, rule, onRuleUpdate]);

	const handleToggleStatus = async () => {
		if (isToggling || isSaving) {
			return;
		}
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

	const handleEditTitle = (e) => {
		e.stopPropagation();
		if (isEditingTitle) {
			if (titleInputRef.current) {
				titleInputRef.current.focus();
			}
		} else {
			setIsEditingTitle(true);
		}
	};

	const handleTitleChange = (e) => {
		setEditedTitle(e.target.value);
	};

	const handleTitleKeyDown = (e) => {
		if (e.key === "Enter") {
			e.preventDefault();
			// Update rule object with new title but don't save to server
			if (onRuleUpdate) {
				onRuleUpdate({
					...rule,
					title: editedTitle.trim() || rule.title
				});
			}
			setIsEditingTitle(false);
		} else if (e.key === "Escape") {
			setEditedTitle(rule.title || "");
			setIsEditingTitle(false);
		}
	};

	const formattedId = String(rule.id).padStart(2, "0");
	const headerClass = `user-registration-card__header ur-d-flex ur-align-items-center ur-p-5 integration-header-info accordion${
		isExpanded ? " active" : ""
	}`;

	const cardClassName = `user-registration-card ur-mb-2 urcr-rule-card ${
		isHighlighted ? "urcr-rule-highlighted" : ""
	}`;

	return (
		<div className={cardClassName} data-rule-id={rule.id}>
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
					<div className="user-registration-editable-title urcr-rule-title">
						<input
							ref={titleInputRef}
							type="text"
							className={`user-registration-editable-title__input ${
								isEditingTitle ? "is-editing" : ""
							}`}
							value={editedTitle}
							onChange={handleTitleChange}
							onKeyDown={handleTitleKeyDown}
							onClick={(e) => e.stopPropagation()}
							disabled={!isEditingTitle}
						/>
						{!isMembershipRule && (
							<span
								className="user-registration-editable-title__icon dashicons dashicons-edit"
								onClick={handleEditTitle}
								style={{ cursor: "pointer" }}
							></span>
						)}
					</div>
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
								disabled={isToggling || isSaving}
							/>
							<span className="slider round"></span>
						</span>
						{isToggling && (
							<span className="urcr-toggle-loader spinner is-active"></span>
						)}
					</div>

					{/* <span className="urcr-separator"> | </span> */}
				</div>

				<div className="integration-action urcr-integration-action">
					<div className="urcr-membership-tabs">
						<button
							className={`urcr-tab-button ${
								activeTab === "rules" ? "urcr-tab-active" : ""
							}`}
							type="button"
							onClick={(e) => {
								e.stopPropagation();
								setActiveTab("rules");
							}}
						>
							{__("Rule", "user-registration")}
						</button>
						<button
							className={`urcr-tab-button ${
								activeTab === "message" ? "urcr-tab-active" : ""
							}`}
							type="button"
							onClick={(e) => {
								e.stopPropagation();
								setActiveTab("message");
							}}
						>
							{messageTabLabel}
						</button>
					</div>
					{!shouldHideMenu && (
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
								aria-label={__(
									"More options",
									"user-registration"
								)}
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
										<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
											<path d="M9.3 16.546V11.09c0-.502.403-.91.9-.91s.9.408.9.91v5.455a.905.905 0 0 1-.9.909.905.905 0 0 1-.9-.91Zm3.6 0V11.09c0-.502.403-.91.9-.91s.9.408.9.91v5.455a.905.905 0 0 1-.9.909.905.905 0 0 1-.9-.91Z"/>
											<path d="M4.8 19.273V6.545c0-.502.403-.909.9-.909s.9.407.9.91v12.727c0 .24.095.472.264.643.168.17.397.266.636.266h9a.895.895 0 0 0 .636-.266.914.914 0 0 0 .264-.643V6.545c0-.502.403-.909.9-.909s.9.407.9.91v12.727c0 .723-.285 1.416-.791 1.928A2.686 2.686 0 0 1 16.5 22h-9a2.686 2.686 0 0 1-1.909-.799 2.741 2.741 0 0 1-.791-1.928Z"/>
											<path d="M20.1 5.636c.497 0 .9.407.9.91a.905.905 0 0 1-.9.909H3.9a.905.905 0 0 1-.9-.91c0-.502.403-.909.9-.909h16.2Z"/>
											<path d="M14.7 6.545V4.727a.914.914 0 0 0-.264-.642.895.895 0 0 0-.636-.267h-3.6a.895.895 0 0 0-.636.267.914.914 0 0 0-.264.642v1.818c0 .503-.403.91-.9.91a.905.905 0 0 1-.9-.91V4.727c0-.723.285-1.417.791-1.928A2.686 2.686 0 0 1 10.2 2h3.6c.716 0 1.403.288 1.909.799.506.511.791 1.205.791 1.928v1.818c0 .503-.403.91-.9.91a.905.905 0 0 1-.9-.91Z"/>
										</svg>
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
										<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
											<path d="M13.818 17.454V12a.91.91 0 1 1 1.818 0v5.454a.91.91 0 1 1-1.818 0Z"/>
											<path d="M17.454 13.818a.91.91 0 1 1 0 1.818H12a.91.91 0 1 1 0-1.818h5.454Z"/>
											<path d="M20.182 10.182a.91.91 0 0 0-.91-.91h-9.09a.91.91 0 0 0-.91.91v9.09c0 .503.408.91.91.91h9.09a.91.91 0 0 0 .91-.91v-9.09ZM22 19.272A2.727 2.727 0 0 1 19.273 22h-9.091a2.727 2.727 0 0 1-2.727-2.727v-9.091a2.727 2.727 0 0 1 2.727-2.727h9.09A2.727 2.727 0 0 1 22 10.182v9.09Z"/>
											<path d="M14.727 4.727a.914.914 0 0 0-.909-.909h-9.09a.914.914 0 0 0-.91.91v9.09c0 .498.411.91.91.91a.91.91 0 1 1 0 1.818A2.733 2.733 0 0 1 2 13.818v-9.09A2.733 2.733 0 0 1 4.727 2h9.091a2.733 2.733 0 0 1 2.728 2.727.91.91 0 0 1-1.819 0Z"/>
										</svg>
										{__("Duplicate", "user-registration")}
									</button>
								</div>
							)}
						</div>
					)}
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
				<div
					className={
						activeTab === "rules"
							? "urcr-tab-content urcr-tab-content-active"
							: "urcr-tab-content"
					}
				>
				<RuleContentDisplay
					rule={rule}
					onRuleUpdate={onRuleUpdate}
					isToggling={isToggling}
				/>
				</div>
				<div
					className={
						activeTab === "message"
							? "urcr-tab-content urcr-tab-content-active"
							: "urcr-tab-content"
					}
				>
				<SettingsPanel
					rule={rule}
					onRuleUpdate={onRuleUpdate}
					onGoBack={() => setActiveTab("rules")}
					isToggling={isToggling}
					onSavingChange={setIsSaving}
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
