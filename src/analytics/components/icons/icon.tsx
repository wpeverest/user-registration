import * as React from 'react';
import { cn } from '../../lib/utils';

export interface IconProps extends React.SVGProps<SVGSVGElement> {
	size?: number | string;
	strokeWidth?: number | string;
	className?: string;
	color?: string;
	circle?: boolean;
}

export type Icon = React.ForwardRefExoticComponent<
	IconProps & React.RefAttributes<SVGSVGElement>
>;

export type IconNode = Array<[string, Record<string, any>]>;

export const createIcon = (displayName: string, iconNode: IconNode): Icon => {
	const IconComponent = React.forwardRef<SVGSVGElement, IconProps>(
		(
			{
				className,
				size = 24,
				strokeWidth = 2,
				color = 'currentColor',
				circle,
				...props
			},
			ref,
		) => {
			return (
				<svg
					ref={ref}
					xmlns="http://www.w3.org/2000/svg"
					width={size}
					height={size}
					viewBox="0 0 24 24"
					fill="none"
					stroke={color}
					strokeWidth={strokeWidth}
					strokeLinecap="round"
					strokeLinejoin="round"
					className={cn('UR-UI-Icon', className)}
					{...props}
				>
					{circle && (
						<circle
							cx="12"
							cy="12"
							r="10"
							fill="none"
							stroke={color}
							strokeWidth={strokeWidth}
						/>
					)}
					{iconNode.map(([tag, attrs], index) =>
						React.createElement(tag, { key: index, ...attrs }),
					)}
				</svg>
			);
		},
	);

	IconComponent.displayName = displayName;

	return IconComponent;
};
