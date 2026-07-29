import { Circle, Flex } from "@chakra-ui/react";
import { FiCheck, FiMail } from "react-icons/fi";

export type WizardStep =
	| "intro"
	| "scan"
	| "test"
	| "result-good"
	| "result-none"
	| "result-spam";

const STEP_INDEX: Record<WizardStep, number> = {
	intro: 0,
	scan: 1,
	test: 3,
	"result-none": 3,
	"result-spam": 3,
	"result-good": 4,
};

const NODE_COUNT = 5;
const ACTIVE_COLOR = "#475BB2";

const Stepper = ({ step }: { step: WizardStep }) => {
	const current = STEP_INDEX[step];
	const success = step === "result-good";

	return (
		<Flex align="center" px="2px" pb="24px">
			{Array.from({ length: NODE_COUNT }).map((_, index) => {
				const isCompleted = index < current;
				const isCurrent = index === current;

				return (
					<Flex key={index} align="center" flex={index === NODE_COUNT - 1 ? "0 0 auto" : "1 1 auto"}>
						<Circle
							size="28px"
							flexShrink={0}
							bg={isCompleted || (isCurrent && success) ? ACTIVE_COLOR : "white"}
							borderWidth="2px"
							borderColor={isCompleted || isCurrent ? ACTIVE_COLOR : "gray.300"}
							color={isCompleted || (isCurrent && success) ? "white" : isCurrent ? ACTIVE_COLOR : "gray.400"}
							transition="background 300ms ease, border-color 300ms ease"
						>
							{isCompleted || (isCurrent && success) ? (
								<FiCheck size={13} />
							) : isCurrent ? (
								<FiMail size={13} />
							) : null}
						</Circle>

						{index < NODE_COUNT - 1 && (
							<Flex
								h="2px"
								flex={1}
								mx="8px"
								bg={index < current ? ACTIVE_COLOR : "gray.200"}
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
