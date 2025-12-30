import { createRoot } from "react-dom/client";
import App from "./components/App";

document.addEventListener("DOMContentLoaded", () => {
	const appRoot = document.getElementById("user-registration-setup-wizard");
	if (appRoot) {
		const root = createRoot(appRoot);
		root.render(<App />);
	}
});
