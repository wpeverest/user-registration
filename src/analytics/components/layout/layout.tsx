import * as React from 'react';
import { cn } from '../../lib/utils';

export interface LayoutProps {
	children: React.ReactNode;
	className?: string;
}

export interface LayoutHeaderProps {
	children: React.ReactNode;
	className?: string;
}

export interface LayoutBodyProps {
	children: React.ReactNode;
	className?: string;
}

const Layout = React.forwardRef<HTMLDivElement, LayoutProps>(
	({ children, className, ...props }, ref) => {
		return (
			<div
				ref={ref}
				className={cn('UR-Analytics-Layout', className)}
				{...props}
			>
				{children}
			</div>
		);
	},
);
Layout.displayName = 'Layout';

const LayoutHeader = React.forwardRef<HTMLDivElement, LayoutHeaderProps>(
	({ children, className, ...props }, ref) => {
		return (
			<header
				ref={ref}
				className={cn('UR-Analytics-Layout-Header', className)}
				{...props}
			>
				{children}
			</header>
		);
	},
);
LayoutHeader.displayName = 'LayoutHeader';

const LayoutBody = React.forwardRef<HTMLDivElement, LayoutBodyProps>(
	({ children, className, ...props }, ref) => {
		return (
			<main
				ref={ref}
				className={cn('UR-Analytics-Layout-Body', className)}
				{...props}
			>
				{children}
			</main>
		);
	},
);
LayoutBody.displayName = 'LayoutBody';

export { Layout, LayoutHeader, LayoutBody };
