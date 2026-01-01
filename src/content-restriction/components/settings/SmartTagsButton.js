/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const SmartTagsButton = ({ editorId, onTagInsert }) => {
	const [isOpen, setIsOpen] = useState(false);
	const [searchTerm, setSearchTerm] = useState("");
	const dropdownRef = useRef(null);
	const buttonRef = useRef(null);

	// Get smart tags list from localized data
	const smartTagsList = getURCRData("smart_tags_list", {});
	const showButton = getURCRData("show_smart_tags_button", true);
	const dropdownTitle = getURCRData("smart_tags_dropdown_title", __("Smart Tags", "user-registration"));
	const searchPlaceholder = getURCRData("smart_tags_dropdown_search_placeholder", __("Search Tags...", "user-registration"));


	// Filter smart tags based on search term
	const filteredTags = Object.entries(smartTagsList).filter(([key, value]) => {
		const searchLower = searchTerm.toLowerCase();
		return (
			key.toLowerCase().includes(searchLower) ||
			value.toLowerCase().includes(searchLower)
		);
	});

	// Close dropdown when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				dropdownRef.current &&
				!dropdownRef.current.contains(event.target) &&
				buttonRef.current &&
				!buttonRef.current.contains(event.target)
			) {
				setIsOpen(false);
				setSearchTerm("");
			}
		};

		if (isOpen) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [isOpen]);

	// Handle tag selection
	const handleTagSelect = (tag) => {
		if (onTagInsert) {
			onTagInsert(tag);
		} else {
			if (typeof wp !== "undefined" && wp.editor && window.tinymce) {
				const editor = window.tinymce.get(editorId);
				if (editor) {
					editor.execCommand("mceInsertContent", false, tag);
					editor.fire("change");
				}
			}
		}
		setIsOpen(false);
		setSearchTerm("");
	};

	if (!showButton || Object.keys(smartTagsList).length === 0) {
		return null;
	}

	const buttonContent = (
		<div 
			id={`urcr-smart-tags-wrapper-${editorId}`}
			className="urcr-smart-tags-button-wrapper"
		>
			<button
				ref={buttonRef}
				type="button"
				id={`ur-smart-tags-selector-${editorId}`}
				className="urcr-smart-tags-button"
				onClick={() => setIsOpen(!isOpen)}
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="16"
					height="16"
					viewBox="0 0 16 16"
					fill="none"
				>
					<path
						d="M10 3.33203L14.2 7.53203C14.3492 7.68068 14.4675 7.85731 14.5483 8.05179C14.629 8.24627 14.6706 8.45478 14.6706 8.66536C14.6706 8.87595 14.629 9.08446 14.5483 9.27894C14.4675 9.47342 14.3492 9.65005 14.2 9.7987L11.3333 12.6654"
						stroke="#6B6B6B"
						strokeWidth="1.33333"
						strokeLinecap="round"
						strokeLinejoin="round"
					/>
					<path
						d="M6.39132 3.7227C6.14133 3.47263 5.80224 3.33211 5.44865 3.33203H2.00065C1.82384 3.33203 1.65427 3.40227 1.52925 3.52729C1.40422 3.65232 1.33398 3.82189 1.33398 3.9987V7.4467C1.33406 7.80029 1.47459 8.13938 1.72465 8.38937L5.52732 12.192C5.83033 12.4931 6.24015 12.6621 6.66732 12.6621C7.09449 12.6621 7.50431 12.4931 7.80732 12.192L10.194 9.80537C10.4951 9.50236 10.6641 9.09253 10.6641 8.66537C10.6641 8.2382 10.4951 7.82837 10.194 7.52537L6.39132 3.7227Z"
						stroke="#6B6B6B"
						strokeWidth="1.33333"
						strokeLinecap="round"
						strokeLinejoin="round"
					/>
					<path
						d="M4.33333 6.66667C4.51743 6.66667 4.66667 6.51743 4.66667 6.33333C4.66667 6.14924 4.51743 6 4.33333 6C4.14924 6 4 6.14924 4 6.33333C4 6.51743 4.14924 6.66667 4.33333 6.66667Z"
						fill="#6B6B6B"
						stroke="#6B6B6B"
						strokeWidth="1.33333"
						strokeLinecap="round"
						strokeLinejoin="round"
					/>
				</svg>
				{__("Add Smart Tags", "user-registration")}
			</button>

			{isOpen && (
				<div
					ref={dropdownRef}
					className="urcr-smart-tags-dropdown"
				>
					{/* Title */}
					<div className="urcr-smart-tags-dropdown-title">
						<p>{dropdownTitle}</p>
					</div>

					{/* Search */}
					<div className="urcr-smart-tags-search">
						<span className="urcr-smart-tags-search-icon">
							<svg
								xmlns="http://www.w3.org/2000/svg"
								height="16px"
								width="16px"
								viewBox="0 0 24 24"
								fill="#a1a4b9"
							>
								<path d="M21.71,20.29,18,16.61A9,9,0,1,0,16.61,18l3.68,3.68a1,1,0,0,0,1.42,0A1,1,0,0,0,21.71,20.29ZM11,18a7,7,0,1,1,7-7A7,7,0,0,1,11,18Z" />
							</svg>
						</span>
						<input
							type="text"
							value={searchTerm}
							onChange={(e) => setSearchTerm(e.target.value)}
							placeholder={searchPlaceholder}
							autoFocus
						/>
					</div>

					{/* Tags List */}
					<div className="urcr-smart-tags-list">
						{filteredTags.length > 0 ? (
							filteredTags.map(([key, value]) => (
								<button
									key={key}
									type="button"
									className="urcr-smart-tags-item"
									onClick={() => handleTagSelect(key)}
								>
									{value}
								</button>
							))
						) : (
							<div className="urcr-smart-tags-empty">
								{__("No tags found", "user-registration")}
							</div>
						)}
					</div>
				</div>
			)}
		</div>
	);

	
	return buttonContent;
};

export default SmartTagsButton;
