import * as React from 'react';
import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '../../lib/utils';

const buttonVariants = cva('', {
	variants: {
		variant: {
			default: 'UR-UI-Button',
			destructive: 'UR-UI-Button UR-UI-Button--destructive',
			outline: 'UR-UI-Button UR-UI-Button--outline',
			secondary: 'UR-UI-Button UR-UI-Button--secondary',
			ghost: 'UR-UI-Button UR-UI-Button--ghost',
			link: 'UR-UI-Button UR-UI-Button--link',
		},
		size: {
			default: '',
			sm: 'UR-UI-Button--sm',
			lg: 'UR-UI-Button--lg',
			icon: 'UR-UI-Button--icon',
			'icon-sm': 'UR-UI-Button--icon-sm',
			'icon-lg': 'UR-UI-Button--icon-lg',
		},
	},
	defaultVariants: {
		variant: 'default',
		size: 'default',
	},
});

function Button({
	className,
	variant,
	size,
	asChild = false,
	...props
}: React.ComponentProps<'button'> &
	VariantProps<typeof buttonVariants> & {
		asChild?: boolean;
	}) {
	const Comp = asChild ? Slot : 'button';

	return (
		<Comp
			data-slot="button"
			className={cn(buttonVariants({ variant, size, className }))}
			{...props}
		/>
	);
}

export { Button, buttonVariants };
