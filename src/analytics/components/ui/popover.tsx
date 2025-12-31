import * as React from 'react';
import * as PopoverPrimitive from '@radix-ui/react-popover';
import { cn } from '../../lib/utils';

function Popover({
	...props
}: React.ComponentProps<typeof PopoverPrimitive.Root>) {
	return <PopoverPrimitive.Root data-slot="popover" {...props} />;
}

function PopoverTrigger({
	className,
	...props
}: React.ComponentProps<typeof PopoverPrimitive.Trigger>) {
	return (
		<PopoverPrimitive.Trigger
			data-slot="popover-trigger"
			className={cn('UR-UI-Popover-Trigger', className)}
			{...props}
		/>
	);
}

function PopoverContent({
	className,
	align = 'center',
	sideOffset = 4,
	...props
}: React.ComponentProps<typeof PopoverPrimitive.Content>) {
	return (
		<PopoverPrimitive.Portal>
			<PopoverPrimitive.Content
				data-slot="popover-content"
				align={align}
				sideOffset={sideOffset}
				className={cn('UR-UI-Popover', className)}
				{...props}
			/>
		</PopoverPrimitive.Portal>
	);
}

function PopoverAnchor({
	className,
	...props
}: React.ComponentProps<typeof PopoverPrimitive.Anchor>) {
	return (
		<PopoverPrimitive.Anchor
			data-slot="popover-anchor"
			className={cn('UR-UI-Popover-Anchor', className)}
			{...props}
		/>
	);
}

export { Popover, PopoverTrigger, PopoverContent, PopoverAnchor };
