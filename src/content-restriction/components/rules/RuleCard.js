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
								disabled={isToggling}
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
