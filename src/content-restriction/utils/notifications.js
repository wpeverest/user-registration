let toastContainer = null;

const getToastContainer = () => {
	if (!toastContainer) {
		toastContainer = document.createElement("div");
		toastContainer.className = "urcr-toast-container";
		document.body.appendChild(toastContainer);
	}
	return toastContainer;
};

export const showNotice = (message, type = "info", duration = 5000) => {
	const noticeId = `urcr-toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
	const container = getToastContainer();
	
	const toast = document.createElement("div");
	toast.id = noticeId;
	toast.className = `urcr-toast urcr-toast--${type}`;
	
	let icon = "";
	if (type === "success") {
		icon = '<span class="dashicons dashicons-yes-alt"></span>';
	} else if (type === "error") {
		icon = '<span class="dashicons dashicons-warning"></span>';
	} else {
		icon = '<span class="dashicons dashicons-info"></span>';
	}
	
	toast.innerHTML = `
		<div class="urcr-toast__icon">${icon}</div>
		<div class="urcr-toast__message">${message}</div>
		<button type="button" class="urcr-toast__close" aria-label="Dismiss">
			<span class="dashicons dashicons-no-alt"></span>
		</button>
	`;
	
	container.appendChild(toast);
	
	requestAnimationFrame(() => {
		toast.classList.add("urcr-toast--show");
	});
	
	const closeBtn = toast.querySelector(".urcr-toast__close");
	if (closeBtn) {
		closeBtn.addEventListener("click", () => {
			dismissToast(toast);
		});
	}
	
	if (duration > 0) {
		setTimeout(() => {
			dismissToast(toast);
		}, duration);
	}
	
	return toast;
};

const dismissToast = (toast) => {
	if (!toast || !toast.parentNode) return;
	
	toast.classList.remove("urcr-toast--show");
	toast.classList.add("urcr-toast--hide");
	
	setTimeout(() => {
		if (toast.parentNode) {
			toast.remove();
		}
		if (toastContainer && toastContainer.children.length === 0) {
			toastContainer.remove();
			toastContainer = null;
		}
	}, 300);
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

