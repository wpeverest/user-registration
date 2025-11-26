/**
 * Simple notification utility for WordPress admin notices
 */
export const showNotice = (message, type = "info", duration = 5000) => {
	const noticeId = `urcr-notice-${Date.now()}`;
	const noticeClass = `notice notice-${type} is-dismissible`;
	
	const notice = document.createElement("div");
	notice.id = noticeId;
	notice.className = noticeClass;
	notice.innerHTML = `<p>${message}</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>`;
	
	// Try to find the React app container first
	const reactContainer = document.getElementById("user-registration-content-access-rules");
	const wrap = document.querySelector(".wrap");
	
	let target = null;
	
	// Priority 1: Place inside React container if it exists
	if (reactContainer && reactContainer.parentElement) {
		// Insert at the beginning of the React container's parent (wrap)
		target = reactContainer.parentElement;
		// Insert before the React container
		target.insertBefore(notice, reactContainer);
	} 
	// Priority 2: Use wp-header-end if available
	else if (document.querySelector(".wp-header-end")) {
		target = document.querySelector(".wp-header-end");
		target.after(notice);
	} 
	// Priority 3: Use wrap h1
	else if (wrap) {
		const h1 = wrap.querySelector("h1");
		if (h1) {
			h1.after(notice);
		} else {
			wrap.insertBefore(notice, wrap.firstChild);
		}
	} 
	// Fallback: append to body at the top
	else {
		const adminMain = document.querySelector("#wpbody-content");
		if (adminMain) {
			adminMain.insertBefore(notice, adminMain.firstChild);
		} else {
			document.body.insertBefore(notice, document.body.firstChild);
		}
	}
	
	// Trigger WordPress dismiss functionality if available
	if (window.jQuery && window.jQuery.fn.on) {
		window.jQuery(document).trigger("wp-updates-notice-added");
	}
	
	// Handle dismiss button manually
	const dismissBtn = notice.querySelector(".notice-dismiss");
	if (dismissBtn) {
		dismissBtn.addEventListener("click", (e) => {
			e.preventDefault();
			notice.style.transition = "opacity 0.3s ease";
			notice.style.opacity = "0";
			setTimeout(() => {
				if (notice.parentNode) {
					notice.remove();
				}
			}, 300);
		});
	}
	
	// Auto-remove after duration
	if (duration > 0) {
		setTimeout(() => {
			if (notice.parentNode) {
				notice.style.transition = "opacity 0.3s ease";
				notice.style.opacity = "0";
				setTimeout(() => {
					if (notice.parentNode) {
						notice.remove();
					}
				}, 300);
			}
		}, duration);
	}
	
	return notice;
};

export const showSuccess = (message, duration = 3000) => {
	return showNotice(message, "success", duration);
};

export const showError = (message, duration = 5000) => {
	return showNotice(message, "error", duration);
};

export const showInfo = (message, duration = 5000) => {
	return showNotice(message, "info", duration);
};

