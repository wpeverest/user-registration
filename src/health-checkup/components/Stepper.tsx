import { Circle, Flex, Tooltip, useBreakpointValue } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { FiCheck } from "react-icons/fi";
import Text from "./Text";

export type WizardStep =
	| "intro"
	| "settings"
	| "delivery"
	| "test"
	| "result-good"
	| "result-none"
	| "result-spam";

/**
 * The two steps that present scan findings. They share one scan: "settings"
 * fetches it, "delivery" reads what it left behind — so anything keyed off
 * "is this step showing scan results" has to name both.
 */
export const SCAN_STEPS: WizardStep[] = ["settings", "delivery"];

// Zero-based position of each step; the stepper itself counts from one.
const STEP_INDEX: Record<WizardStep, number> = {
	intro: 0,
	settings: 1,
	delivery: 2,
	test: 3,
	"result-none": 4,
	"result-spam": 4,
	"result-good": 4,
};

/** One node per stage, and the denominator StepHeader counts against. */
export const TOTAL_STEPS = 5;

/** Named, so the bar reads as a map of the run — as the setup wizard's does. */
// "Mail delivery" and "Test delivery" were a poor pair: both read as "we test
// whether mail is delivered", so which one you were on told you nothing. They
// name different things — one inspects the machinery, the other sends a real
// message — so the labels now say which is which.
const STEP_LABELS = [
	__("Start", "user-registration"),
	__("Settings", "user-registration"),
	__("Mail server", "user-registration"),
	__("Live test", "user-registration"),
	__("Result", "user-registration"),
];

// The wizard's own values, not tokens, so the two bars can't drift apart.
const ACTIVE_COLOR = "#475BB2";
const MUTED_COLOR = "gray.400";
const LINE_COLOR = "gray.300";

/**
 * Progress through the run, built to the setup wizard's stepper: numbered
 * circles that become ticks, the label beside each on wide screens and in a
 * tooltip otherwise, and a connector filling the gap between them.
 *
 * Deliberately not clickable, where the wizard's is. The wizard's steps are a
 * form the admin can revisit; these are a sequence with side effects — jumping
 * to "Live test" would send a real email, and jumping to "Result" would ask for
 * an outcome that doesn't exist yet. Back and Next own the navigation.
 *
 * A failed send is not marked here. The step still ran, and its outcome is what
 * carried the run to the result — which states the failure in full. Singling the
 * node out only put a second, quieter verdict in the progress bar.
 *
 * @param step Where the run is now.
 */
const Stepper = ({ step }: { step: WizardStep }) => {
	const current = STEP_INDEX[step] + 1;
	// Every result is terminal: the run is over whatever the outcome was, so the
	// last node reads as complete rather than still in progress.
	const finished = step.startsWith("result-");

	const showLabels = useBreakpointValue({ base: false, md: false, lg: true });
	const circleSize = useBreakpointValue({ base: "24px", md: "28px" });

	return (
		<Flex align="center" justify="space-between" w="100%">
			{STEP_LABELS.map((label, index) => {
				const stepNumber = index + 1;

				const isCurrent = stepNumber === current;
				const isCompleted = stepNumber < current || (isCurrent && finished);

				const ringColor = isCompleted || isCurrent ? ACTIVE_COLOR : MUTED_COLOR;

				const labelColor = isCurrent
					? ACTIVE_COLOR
					: isCompleted
						? "gray.700"
						: MUTED_COLOR;

				return (
					<Flex
						key={label}
						align="center"
						flex={stepNumber === TOTAL_STEPS ? "0 0 auto" : "1 1 auto"}
					>
						<Tooltip
							label={label}
							hasArrow
							placement="bottom"
							isDisabled={showLabels}
							bg="gray.700"
							color="white"
							fontSize="xs"
							px={2}
							py={1}
							borderRadius="md"
						>
							<Flex align="center" flexShrink={0}>
								<Circle
									size={circleSize}
									flexShrink={0}
									bg={isCompleted ? ACTIVE_COLOR : "white"}
									borderWidth="2px"
									borderColor={ringColor}
									color={isCompleted ? "white" : ringColor}
									transition="background 300ms ease, border-color 300ms ease"
								>
									{isCompleted ? (
										<FiCheck size={13} />
									) : (
										<Text fontSize={{ base: "10px", md: "xs" }} fontWeight="600">
											{stepNumber}
										</Text>
									)}
								</Circle>

								{showLabels && (
									<Text
										ml={2}
										fontSize="sm"
										fontWeight={isCurrent || isCompleted ? "600" : "400"}
										color={labelColor}
										whiteSpace="nowrap"
									>
										{label}
									</Text>
								)}
							</Flex>
						</Tooltip>

						{stepNumber < TOTAL_STEPS && (
							<Flex
								h="2px"
								flex={1}
								mx={{ base: 2, md: 3 }}
								bg={stepNumber < current ? ACTIVE_COLOR : LINE_COLOR}
								transition="background-color 300ms ease"
							/>
						)}
					</Flex>
				);
			})}
		</Flex>
	);
};

export default Stepper;
