import * as React from 'react';
import { DayButton, DayPicker, getDefaultClassNames } from 'react-day-picker';

import { cn } from '../../lib/utils';
import { Button, buttonVariants } from './button';
import { ChevronLeft, ChevronRight, ChevronDown } from '../icons';

export type CalendarProps = React.ComponentProps<typeof DayPicker> & {
	buttonVariant?: React.ComponentProps<typeof Button>['variant'];
};

function Calendar({
	className,
	classNames,
	showOutsideDays = true,
	captionLayout = 'label',
	buttonVariant = 'ghost',
	formatters,
	components,
	...props
}: CalendarProps): JSX.Element {
	const defaultClassNames = getDefaultClassNames();

	return (
		<DayPicker
			showOutsideDays={showOutsideDays}
			className={cn('UR-UI-Calendar', className)}
			captionLayout={captionLayout}
			formatters={{
				formatMonthDropdown: (date) =>
					date.toLocaleString('default', { month: 'short' }),
				...formatters,
			}}
			classNames={{
				root: cn('UR-UI-Calendar-Root', defaultClassNames.root),
				months: cn('UR-UI-Calendar-Months', defaultClassNames.months),
				month: cn('UR-UI-Calendar-Month', defaultClassNames.month),
				nav: cn('UR-UI-Calendar-Nav', defaultClassNames.nav),
				button_previous: cn(
					buttonVariants({ variant: buttonVariant }),
					'UR-UI-Calendar-NavButton',
					'UR-UI-Calendar-NavButtonPrevious',
					defaultClassNames.button_previous,
				),
				button_next: cn(
					buttonVariants({ variant: buttonVariant }),
					'UR-UI-Calendar-NavButton',
					'UR-UI-Calendar-NavButtonNext',
					defaultClassNames.button_next,
				),
				month_caption: cn(
					'UR-UI-Calendar-MonthCaption',
					defaultClassNames.month_caption,
				),
				dropdowns: cn('UR-UI-Calendar-Dropdowns', defaultClassNames.dropdowns),
				dropdown_root: cn(
					'UR-UI-Calendar-DropdownRoot',
					defaultClassNames.dropdown_root,
				),
				dropdown: cn('UR-UI-Calendar-Dropdown', defaultClassNames.dropdown),
				caption_label: cn(
					'UR-UI-Calendar-CaptionLabel',
					defaultClassNames.caption_label,
				),
				weekdays: cn('UR-UI-Calendar-Weekdays', defaultClassNames.weekdays),
				weekday: cn('UR-UI-Calendar-Weekday', defaultClassNames.weekday),
				week: cn('UR-UI-Calendar-Week', defaultClassNames.week),
				week_number_header: cn(
					'UR-UI-Calendar-WeekNumberHeader',
					defaultClassNames.week_number_header,
				),
				week_number: cn(
					'UR-UI-Calendar-WeekNumber',
					defaultClassNames.week_number,
				),
				day: cn('UR-UI-Calendar-Day', defaultClassNames.day),
				range_start: cn(
					'UR-UI-Calendar-RangeStart',
					defaultClassNames.range_start,
				),
				range_middle: cn(
					'UR-UI-Calendar-RangeMiddle',
					defaultClassNames.range_middle,
				),
				range_end: cn('UR-UI-Calendar-RangeEnd', defaultClassNames.range_end),
				today: cn('UR-UI-Calendar-Today', defaultClassNames.today),
				outside: cn('UR-UI-Calendar-Outside', defaultClassNames.outside),
				disabled: cn('UR-UI-Calendar-Disabled', defaultClassNames.disabled),
				hidden: cn('UR-UI-Calendar-Hidden', defaultClassNames.hidden),
				...classNames,
			}}
			components={{
				Root: ({ className, rootRef, ...props }) => {
					return (
						<div
							data-slot="calendar"
							ref={rootRef}
							className={cn(className)}
							{...props}
						/>
					);
				},
				Chevron: ({ className, orientation, ...props }) => {
					if (orientation === 'left') {
						return (
							<ChevronLeft
								className={cn('UR-UI-Calendar-Chevron', className)}
								size={16}
								{...props}
							/>
						);
					}

					if (orientation === 'right') {
						return (
							<ChevronRight
								className={cn('UR-UI-Calendar-Chevron', className)}
								size={16}
								{...props}
							/>
						);
					}

					return (
						<ChevronDown
							className={cn('UR-UI-Calendar-Chevron', className)}
							size={16}
							{...props}
						/>
					);
				},
				DayButton: CalendarDayButton,
				WeekNumber: ({ children, ...props }) => {
					return (
						<td {...props}>
							<div className="UR-UI-Calendar-WeekNumberCell">{children}</div>
						</td>
					);
				},
				...components,
			}}
			{...props}
		/>
	);
}

function CalendarDayButton({
	className,
	day,
	modifiers,
	...props
}: React.ComponentProps<typeof DayButton>): JSX.Element {
	const defaultClassNames = getDefaultClassNames();

	const ref = React.useRef<HTMLButtonElement>(null);
	React.useEffect(() => {
		if (modifiers.focused) ref.current?.focus();
	}, [modifiers.focused]);

	return (
		<Button
			ref={ref}
			variant="ghost"
			size="icon"
			data-day={day.date.toLocaleDateString()}
			data-selected-single={
				modifiers.selected &&
				!modifiers.range_start &&
				!modifiers.range_end &&
				!modifiers.range_middle
			}
			data-range-start={modifiers.range_start}
			data-range-end={modifiers.range_end}
			data-range-middle={modifiers.range_middle}
			data-day-same={modifiers.range_start && modifiers.range_end}
			className={cn(
				'UR-UI-Calendar-DayButton',
				modifiers.selected && 'UR-UI-Calendar-DayButton--selected-single',
				modifiers.range_start && 'UR-UI-Calendar-DayButton--range-start',
				modifiers.range_end && 'UR-UI-Calendar-DayButton--range-end',
				modifiers.range_middle && 'UR-UI-Calendar-DayButton--range-middle',
				modifiers.range_start &&
					modifiers.range_end &&
					'UR-UI-Calendar-DayButton--same',
				defaultClassNames.day,
				className,
			)}
			{...props}
		/>
	);
}

Calendar.displayName = 'Calendar';

export { Calendar, CalendarDayButton };
