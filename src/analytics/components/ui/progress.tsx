'use client';

import * as React from 'react';
import * as ProgressPrimitive from '@radix-ui/react-progress';

import { cn } from '../../lib/utils';

function Progress({
	className,
	value,
	...props
}: React.ComponentProps<typeof ProgressPrimitive.Root>) {
	return (
		<ProgressPrimitive.Root
			data-slot="progress"
			className={cn('UR-UI-Progress-Root', className)}
			{...props}
		>
			<ProgressPrimitive.Indicator
				data-slot="progress-indicator"
				className="UR-UI-Progress-Indicator"
				style={{ transform: `translateX(-${100 - (value || 0)}%)` }}
			/>
		</ProgressPrimitive.Root>
	);
}

export { Progress };
