import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";
import MediaButton from "./MediaButton";
import SmartTagsButton from "./SmartTagsButton";

/* global wp */

const MessageAction = ({
	rule,
	message,
	onMessageChange,
	isMembershipRule,
	isMigratedCustomRule,
	useGlobalMessage,
	onUseGlobalMessageChange
}) => {
	const editorRef = useRef(null);
	const editorId = `urcr-action-message-editor-${rule.id}`;
	const isMountedRef = useRef(true);
	const editorInitializedRef = useRef(false);
	const editorInitializingRef = useRef(false);
	const onMessageChangeRef = useRef(onMessageChange);
	const prevUseGlobalMessageRef = useRef(useGlobalMessage);
	const initTimerRef = useRef(null);
	const savedContentRef = useRef(null);

	useEffect(() => {
		onMessageChangeRef.current = onMessageChange;
	}, [onMessageChange]);

	// Determine if we should show global/custom message option
	// Show for membership rules, migrated custom rules, and regular custom rules
	// Since MessageAction is only rendered when actionType === "message", we show it for all rule types
	const shouldShowGlobalMessageOption = true;

	useEffect(() => {
		const wasUsingGlobal = prevUseGlobalMessageRef.current;

		if (shouldShowGlobalMessageOption && useGlobalMessage && !wasUsingGlobal) {
			const cleanupEditor = () => {
				if (
					typeof wp !== "undefined" &&
					wp.editor &&
					window.tinymce
				) {
					const tinymceEditor = window.tinymce.get(editorId);
					if (tinymceEditor) {
						try {
							const currentContent = tinymceEditor.getContent();
							savedContentRef.current = currentContent;
							if (onMessageChangeRef.current) {
								onMessageChangeRef.current(currentContent);
							}
							wp.editor.remove(editorId);
							editorInitializedRef.current = false;
						} catch (error) {
						}
					}
				}
			};
			cleanupEditor();
		}
	}, [shouldShowGlobalMessageOption, useGlobalMessage, editorId]);

	useEffect(() => {
		if (shouldShowGlobalMessageOption && useGlobalMessage) {
			prevUseGlobalMessageRef.current = useGlobalMessage;
			return;
		}

		const wasUsingGlobal = prevUseGlobalMessageRef.current;
		const switchingToCustom = shouldShowGlobalMessageOption && !useGlobalMessage && wasUsingGlobal;

		if (editorInitializedRef.current && !switchingToCustom) {
			prevUseGlobalMessageRef.current = useGlobalMessage;
			return;
		}

		if (switchingToCustom) {
			editorInitializedRef.current = false;
			editorInitializingRef.current = false;
		}

		isMountedRef.current = true;

		let contentToUse = message;
		if (switchingToCustom && (!message || message === "")) {
			if (savedContentRef.current) {
				contentToUse = savedContentRef.current;
			} else {
				const defaultMessage = getURCRData("membership_default_message", "");
				if (defaultMessage) {
					contentToUse = defaultMessage;
					if (onMessageChangeRef.current) {
						onMessageChangeRef.current(defaultMessage);
					}
				}
			}
		}

		if (typeof contentToUse === "undefined" || contentToUse === null) {
			return;
		}

		const initEditor = () => {
			const editorElement = document.getElementById(editorId);

			if (
				!isMountedRef.current ||
				typeof wp === "undefined" ||
				!wp.editor ||
				!editorElement
			) {
				return;
			}

			if (editorInitializingRef.current) {
				return;
			}

			if (switchingToCustom && window.tinymce && window.tinymce.get(editorId)) {
				try {
					wp.editor.remove(editorId);
					editorInitializedRef.current = false;
					editorInitializingRef.current = false;
				} catch (error) {
				}
			}

			if (window.tinymce && window.tinymce.get(editorId)) {
				editorInitializedRef.current = true;
				editorInitializingRef.current = false;
				return;
			}

			if (editorInitializedRef.current && !switchingToCustom) {
				editorInitializingRef.current = false;
				return;
			}

			editorInitializingRef.current = true;
			if (initTimerRef.current) {
				clearTimeout(initTimerRef.current);
				initTimerRef.current = null;
			}

			if (editorElement && contentToUse) {
				editorElement.value = contentToUse;
				if (switchingToCustom && savedContentRef.current && onMessageChangeRef.current) {
					onMessageChangeRef.current(savedContentRef.current);
				}
			}

			try {
				wp.editor.initialize(editorId, {
					quicktags: false,
					mediaButtons: true,
					tinymce: {
						toolbar1:
							"undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat",
						statusbar: false,
						plugins:
							"wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists",
						min_height: 200,
						autoresize_min_height: 200,
						theme_advanced_buttons1:
							"bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator",
						theme_advanced_buttons2: "",
						setup: (editor) => {
							if (contentToUse && editor) {
								editor.on("init", () => {
									if (contentToUse && editor.getContent() !== contentToUse) {
										editor.setContent(contentToUse);
									}
								});
							}
						},
						init_instance_callback: (editor) => {
							editorInitializedRef.current = true;
							editorInitializingRef.current = false;
							prevUseGlobalMessageRef.current = useGlobalMessage;
							if (isMountedRef.current) {
								if (contentToUse && editor.getContent() !== contentToUse) {
									editor.setContent(contentToUse);
								}

								setTimeout(() => {
									if (
										editor &&
										editor.theme &&
										editor.theme.resizeTo
									) {
										editor.theme.resizeTo(null, 200);
									}

									editor.on("change keyup", () => {
										if (isMountedRef.current) {
											const content = wp.editor.getContent(editorId);
											if (onMessageChangeRef.current) {
												onMessageChangeRef.current(content);
											}
										}
									});
								}, 150);
							}
						}
					}
				});
			} catch (error) {
				editorInitializingRef.current = false;
			}
		};

		const attemptInit = (attempt = 0) => {
			if (editorInitializedRef.current && !switchingToCustom) {
				if (window.tinymce && window.tinymce.get(editorId)) {
					return;
				}
			}

			const editorElement = document.getElementById(editorId);
			if (
				!editorElement ||
				typeof wp === "undefined" ||
				!wp.editor
			) {
				if (attempt < 3) {
					initTimerRef.current = setTimeout(() => attemptInit(attempt + 1), attempt === 0 ? 100 : attempt === 1 ? 300 : 500);
				}
				return;
			}

			initEditor();
		};

		initTimerRef.current = setTimeout(() => attemptInit(), 100);

		return () => {
			if (initTimerRef.current) {
				clearTimeout(initTimerRef.current);
				initTimerRef.current = null;
			}
			editorInitializingRef.current = false;
		};
		}, [editorId, rule.id, shouldShowGlobalMessageOption, useGlobalMessage]);

	useEffect(() => {
		return () => {
			isMountedRef.current = false;
			editorInitializedRef.current = false;
			editorInitializingRef.current = false;

			const cleanupEditor = () => {
				if (
					typeof wp !== "undefined" &&
					wp.editor &&
					window.tinymce
				) {
					const tinymceEditor = window.tinymce.get(editorId);
					if (tinymceEditor) {
						try {
							const currentContent = tinymceEditor.getContent();
							if (onMessageChangeRef.current) {
								onMessageChangeRef.current(currentContent);
							}
							wp.editor.remove(editorId);
						} catch (error) {
						}
					}
				}
			};

			setTimeout(cleanupEditor, 200);
		};
	}, [editorId]);

	useEffect(() => {
		if (
			typeof window.jQuery !== "undefined" &&
			typeof window.jQuery.fn.tooltipster !== "undefined"
		) {
			const $helpTips = window.jQuery(".user-registration-help-tip");
			if ($helpTips.length > 0) {
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});

				$helpTips.tooltipster({
					theme: "tooltipster-borderless",
					maxWidth: 200,
					multiple: true,
					interactive: true,
					position: "bottom",
					contentAsHTML: true,
					functionInit: function (instance, helper) {
						const tip = window
							.jQuery(helper.origin)
							.attr("data-tip");
						if (tip) {
							instance.content(tip);
						}
					}
				});
			}

			return () => {
				$helpTips.each(function () {
					const $tip = window.jQuery(this);
					if ($tip.hasClass("tooltipstered")) {
						$tip.tooltipster("destroy");
					}
				});
			};
		}
	}, []);

	return (
		<>
			{shouldShowGlobalMessageOption && (
				<div className="urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span className="urcr-target-content-label">
							{__("Restriction Message", "user-registration")}
						</span>
					</label>
					<div className="urcr-input-container">
						<div className="urcr-checkbox-radio-group">
							<label
								className={`urcr-checkbox-radio-option ${
									useGlobalMessage ? "is-checked" : ""
								}`}
							>
								<input
									type="radio"
									name={`message-type-${rule.id}`}
									value="global"
									checked={useGlobalMessage}
									onChange={() => onUseGlobalMessageChange(true)}
									className="urcr-checkbox-radio-input"
								/>
								<span className="urcr-checkbox-radio-label">
									{__(
										"Use global restriction message",
										"user-registration"
									)}
								</span>
							</label>
							<label
								className={`urcr-checkbox-radio-option ${
									!useGlobalMessage ? "is-checked" : ""
								}`}
							>
								<input
									type="radio"
									name={`message-type-${rule.id}`}
									value="custom"
									checked={!useGlobalMessage}
									onChange={() => onUseGlobalMessageChange(false)}
									className="urcr-checkbox-radio-input"
								/>
								<span className="urcr-checkbox-radio-label">
									{__("Custom message", "user-registration")}
								</span>
							</label>
						</div>
					</div>
				</div>
			)}
			{!useGlobalMessage && (
				<div className="urcr-title-body-pair urcr-rule-action-input-container urcrra-message-input-container ur-form-group">
					<label className="urcr-label-container ur-col-4">
						<span className="urcr-target-content-label">

						</span>
					</label>
					<div className="urcr-body">
						<div className="wp-media-buttons">
							<MediaButton editorId={editorId} />

							<SmartTagsButton
								editorId={editorId}
								onTagInsert={(tag) => {
									if (
										typeof wp !== "undefined" &&
										wp.editor &&
										window.tinymce
									) {
										const editor = window.tinymce.get(editorId);
										if (editor) {
											editor.execCommand("mceInsertContent", false, tag);
											editor.fire("change");
											const content = wp.editor.getContent(editorId);
											if (onMessageChange) {
												onMessageChange(content);
											}
										}
									}
								}}
							/>
						</div>
						<div className="wp-editor-container">
							<div
								className="wp-core-ui wp-editor-wrap tmce-active"
								id={`wp-${editorId}-wrap`}
							>
								<div id={`wp-${editorId}-editor-container`}>
									<textarea
										id={editorId}
										ref={editorRef}
										value={message || ""}
										onChange={(e) => {
											if (onMessageChange) {
												onMessageChange(e.target.value);
											}
										}}
										className="wp-editor-area"
									/>
								</div>
							</div>
						</div>
					</div>
				</div>
			)}
		</>
	);
};

export default MessageAction;


