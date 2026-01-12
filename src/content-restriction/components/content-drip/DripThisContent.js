// DripThisContent.jsx
import React from "react";
import { __ } from "@wordpress/i18n";
import * as Popover from "@radix-ui/react-popover";
import * as Tabs from "@radix-ui/react-tabs";
import {
	Input,
	VStack,
	NumberInput,
	NumberInputField,
	InputGroup,
	InputRightElement
} from "@chakra-ui/react";

const DripThisContent = ({ target }) => {
	if (target?.type === "whole_site") return null;

	return (
		<Popover.Root modal={false}>
			<Popover.Trigger asChild>
				<button type="button" className="urcr-drip__trigger">
					<span className="dashicons dashicons-plus-alt2" />
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
					// If you want "closeOnBlur={false}" behavior:
					// onInteractOutside={(e) => e.preventDefault()}
					// onEscapeKeyDown={(e) => e.preventDefault()}
				>
					{/* Screenshot-style: no arrow (remove if you want it) */}
					<Popover.Arrow className="urcr-drip__arrow" />

					<Tabs.Root className="urcr-drip__tabs" defaultValue="fixed">
						<Tabs.List
							className="urcr-drip__tabList"
							aria-label="Drip options"
						>
							<Tabs.Trigger
								className="urcr-drip__tab"
								value="fixed"
							>
								{__("Fixed Date", "user-registration")}
							</Tabs.Trigger>
							<Tabs.Trigger
								className="urcr-drip__tab"
								value="days"
							>
								{__("Days After", "user-registration")}
							</Tabs.Trigger>
						</Tabs.List>

						<div className="urcr-drip__panels">
							<Tabs.Content
								className="urcr-drip__panel"
								value="fixed"
							>
								<VStack spacing="12px" align="stretch">
									<InputGroup className="urcr-drip__inputGroup">
										<Input
											type="date"
											fontSize="14px"
											borderRadius="6px"
											className="urcr-drip__input"
										/>
										{/* <InputRightElement className="urcr-drip__rightIconWrap">
											<span className="dashicons dashicons-calendar-alt urcr-drip__rightIcon" />
										</InputRightElement> */}
									</InputGroup>

									<InputGroup className="urcr-drip__inputGroup">
										<Input
											type="time"
											fontSize="14px"
											borderRadius="6px"
											className="urcr-drip__input"
										/>
										{/* <InputRightElement className="urcr-drip__rightIconWrap">
											<span className="dashicons dashicons-clock urcr-drip__rightIcon" />
										</InputRightElement> */}
									</InputGroup>
								</VStack>
							</Tabs.Content>

							<Tabs.Content
								className="urcr-drip__panel"
								value="days"
							>
								<NumberInput
									min={0}
									defaultValue={1}
									className="urcr-drip__number"
								>
									<NumberInputField
										fontSize="14px"
										borderRadius="6px"
										className="urcr-drip__input urcr-drip__numberField"
									/>
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
