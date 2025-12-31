import React from "react";
import ReactDOM from "react-dom/client";
import { App } from "./App";
import "./main.scss";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ProgressIndicator } from "./components/progress-indicator";

const rootElement = document.getElementById("UR-Pro-Analytics-Root");
const queryClient = new QueryClient();

const AnalyticsApp = React.lazy(async () => {
	try {
		// @ts-ignore
		return await import("./ProApp");
	} catch {
		return {
			default: App
		};
	}
});

if (rootElement) {
	const root = ReactDOM.createRoot(rootElement);
	root.render(
		<React.StrictMode>
			<React.Suspense fallback={<div>Loading...</div>}>
				<QueryClientProvider client={queryClient}>
					<AnalyticsApp />
					<ProgressIndicator />
				</QueryClientProvider>
			</React.Suspense>
		</React.StrictMode>
	);
}
