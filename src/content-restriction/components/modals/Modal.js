/**
 * External Dependencies
 */
import React, { useEffect } from "react";
import { __ } from "@wordpress/i18n";

/**
 * Common Modal Component
 * Reusable modal for various purposes (add, delete, confirm, etc.)
 */
const Modal = ({
	isOpen,
	onClose,
	title,
	icon,
	children,
	footer,
	onKeyDown,
	className = "",
}) => {
	useEffect(() => {
		if (isOpen) {
			// Prevent body scroll when modal is open
			document.body.style.overflow = "hidden";
			// Focus trap - focus on modal when opened
			const modal = document.querySelector(".urcr-modal");
			if (modal) {
				modal.focus();
			}
		} else {
			document.body.style.overflow = "";
		}

		return () => {
			document.body.style.overflow = "";
		};
	}, [isOpen]);

	useEffect(() => {
		const handleEscape = (e) => {
			if (e.key === "Escape" && isOpen) {
				onClose();
			}
		};

		if (isOpen) {
			document.addEventListener("keydown", handleEscape);
		}

		return () => {
			document.removeEventListener("keydown", handleEscape);
		};
	}, [isOpen, onClose]);

	if (!isOpen) {
		return null;
	}

	const handleBackdropClick = (e) => {
		if (e.target === e.currentTarget) {
			onClose();
		}
	};

	return (
		<>
			<div className="urcr-modal-backdrop" onClick={handleBackdropClick}></div>
			<div
				className={`urcr-modal ${className}`}
				role="dialog"
				aria-modal="true"
				aria-labelledby="urcr-modal-title"
				tabIndex={-1}
				onKeyDown={onKeyDown}
			>
				<div className="urcr-modal-content">
					{title && (
						<div className="urcr-modal-header">
							{icon && <span className={`dashicons ${icon}`}></span>}
							<h2 id="urcr-modal-title">{title}</h2>
						</div>
					)}
					{children && <div className="urcr-modal-body">{children}</div>}
					{footer && <div className="urcr-modal-footer">{footer}</div>}
				</div>
			</div>
		</>
	);
};

export default Modal;

