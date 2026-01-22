import React from "react";
import { __ } from "@wordpress/i18n";
import * as Popover from "@radix-ui/react-popover";
import * as Tabs from "@radix-ui/react-tabs";
import {
	Input,
	VStack,
	NumberInput,
	NumberInputField,
	InputGroup
} from "@chakra-ui/react";

const DEFAULT_DRIP = {
	activeType: "fixed_date",
	value: {
		fixed_date: { date: "", time: "" },
		days_after: { days: 0 }
	}
};

const DripThisContent = ({
	target,
	contentTargets,
	onContentTargetsChange
}) => {
	if (
		[
			"whole_site",
			"masteriyo_courses",
			"whole_site",
			"masteriyo_courses",
			"menu_items",
			"files",
			"custom_uri"
		].includes(target?.type)
	)
		return null;

	const drip = target?.drip ?? DEFAULT_DRIP;

	const updateDrip = (newDrip) => {
		onContentTargetsChange(
			contentTargets.map((t) =>
				t.id === target.id ? { ...t, drip: newDrip } : t
			)
		);
	};

	/* ---------- setters ---------- */

	const setActiveType = (type) => {
		updateDrip({
			...drip,
			activeType: type
		});
	};

	const setFixedDateField = (field, value) => {
		updateDrip({
			...drip,
			activeType: "fixed_date",
			value: {
				...drip.value,
				fixed_date: {
					...drip?.value?.fixed_date,
					[field]: value
				}
			}
		});
	};

	const setDays = (days) => {
		updateDrip({
			...drip,
			activeType: "days_after",
			value: {
				...drip?.value,
				days_after: {
					days: Number.isFinite(days) ? days : 0
				}
			}
		});
	};

	const todayString = () => {
		const d = new Date();
		const year = d.getFullYear();
		const month = String(d.getMonth() + 1).padStart(2, "0");
		const day = String(d.getDate()).padStart(2, "0");
		return `${year}-${month}-${day}`; // "YYYY-MM-DD"
	};

	return (
		<Popover.Root modal={false}>
			<Popover.Trigger asChild>
				<button type="button" className="urcr-drip__trigger">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="16"
						height="16"
						fill="currentColor"
						viewBox="0 0 24 24"
					>
						<path d="M11.09 6.545a.91.91 0 1 1 1.82 0v4.893l3.133 1.567a.91.91 0 0 1-.813 1.626l-3.637-1.818a.91.91 0 0 1-.502-.813V6.545Z" />
						<path d="M20.182 12a8.182 8.182 0 1 0-16.364 0 8.182 8.182 0 0 0 16.364 0ZM22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Z" />
					</svg>
					{__("Drip This Content", "user-registration")}
				</button>
			</Popover.Trigger>

			<Popover.Portal>
				<Popover.Content
					className="urcr-drip__popover"
					side="bottom"
					align="end"
					sideOffset={10}
					collisionPadding={12}
				>
					<Popover.Arrow className="urcr-drip__arrow" />

					<Tabs.Root
						className="urcr-drip__tabs"
						value={drip.activeType}
						onValueChange={setActiveType}
					>
						<Tabs.List className="urcr-drip__tabList">
							<Tabs.Trigger
								value="fixed_date"
								className="urcr-drip__tab"
							>
								{__("Fixed Date", "user-registration")}
							</Tabs.Trigger>
							<Tabs.Trigger
								value="days_after"
								className="urcr-drip__tab"
							>
								{__("Days After", "user-registration")}
							</Tabs.Trigger>
						</Tabs.List>

						<div className="urcr-drip__panels">
							<Tabs.Content
								forceMount
								value="fixed_date"
								hidden={drip.activeType !== "fixed_date"}
								className="urcr-drip__panel"
							>
								<VStack spacing="12px" align="stretch">
									<InputGroup>
										<Input
											type="date"
											min={todayString()}
											className="urcr-drip__input"
											value={
												drip?.value?.fixed_date?.date
											}
											onChange={(e) =>
												setFixedDateField(
													"date",
													e.target.value
												)
											}
										/>
									</InputGroup>

									<InputGroup>
										<Input
											type="time"
											className="urcr-drip__input"
											value={
												drip?.value?.fixed_date?.time
											}
											onChange={(e) =>
												setFixedDateField(
													"time",
													e.target.value
												)
											}
										/>
									</InputGroup>
								</VStack>
							</Tabs.Content>

							<Tabs.Content
								forceMount
								value="days_after"
								hidden={drip.activeType !== "days_after"}
								className="urcr-drip__panel"
							>
								<NumberInput
									min={0}
									value={drip?.value?.days_after?.days}
									onChange={(_, valueAsNumber) =>
										setDays(valueAsNumber)
									}
								>
									<NumberInputField className="urcr-drip__input" />
								</NumberInput>
							</Tabs.Content>
						</div>
					</Tabs.Root>
				</Popover.Content>
			</Popover.Portal>
		</Popover.Root>
	);
};

export default DripThisContent;
