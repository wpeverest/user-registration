import React from 'react';
import ReactDOM from 'react-dom/client';
import { App } from './App';
import './main.scss';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ProgressIndicator } from './components/progress-indicator';

const rootElement = document.getElementById('UR-Pro-Analytics-Root');
const queryClient = new QueryClient();

if (rootElement) {
	const root = ReactDOM.createRoot(rootElement);
	root.render(
		<React.StrictMode>
			<React.Suspense fallback={<div>Loading...</div>}>
				<QueryClientProvider client={queryClient}>
					<App />
					<ProgressIndicator />
				</QueryClientProvider>
			</React.Suspense>
		</React.StrictMode>,
	);
}
