import React from "react";
import {
	HStack,
	Box,
	Text,
	Circle,
	Flex,
	Icon,
	useColorModeValue
} from "@chakra-ui/react";
import { CheckIcon } from "@chakra-ui/icons";
import { URMLogo } from "./Icon/Icon";

interface Step {
	id: string;
	label: string;
	stepNumber: number;
}

interface StepperProps {
	steps: Step[];
	currentStep: number;
	maxCompletedStep: number;
	onStepClick: (stepNumber: number) => void;
	onClose?: () => void;
}

// Custom X icon component
const CloseXIcon: React.FC = () => (
	<svg
		width="18"
		height="18"
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="2"
		strokeLinecap="round"
		strokeLinejoin="round"
	>
		<line x1="18" y1="6" x2="6" y2="18" />
		<line x1="6" y1="6" x2="18" y2="18" />
	</svg>
);

const Stepper: React.FC<StepperProps> = ({
	steps,
	currentStep,
	maxCompletedStep,
	onStepClick,
	onClose
}) => {
	const bgColor = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.700");
	const textColor = useColorModeValue("gray.700", "gray.200");
	const mutedColor = useColorModeValue("gray.400", "gray.500");
	const activeColor = "#475BD8";
	const lineColor = useColorModeValue("gray.300", "gray.600");
	const hoverBg = useColorModeValue("gray.100", "gray.700");

	const isStepCompleted = (stepNumber: number) => stepNumber < currentStep;
	const isStepCurrent = (stepNumber: number) => stepNumber === currentStep;
	const isStepClickable = (stepNumber: number) =>
		stepNumber <= maxCompletedStep;
	const isStepVisited = (stepNumber: number) =>
		stepNumber <= maxCompletedStep && stepNumber > currentStep;

	return (
		<Box
			position="fixed"
			top={0}
			left={0}
			right={0}
			bg={bgColor}
			borderBottomWidth="1px"
			borderColor={borderColor}
			py={4}
			px={{ base: 4, md: 6, lg: 10 }}
			zIndex={1000}
		>
			<Flex
				justify="space-between"
				align="center"
				maxW="1400px"
				mx="auto"
				width="100%"
			>
				<Flex align="center" flexShrink={0} mr={{ base: 2, md: 6 }}>
					<URMLogo h="40px" />
				</Flex>

				<HStack
					spacing={0}
					justify="center"
					flex={1}
					mx={{ base: 2, md: 6 }}
					overflowX="auto"
					css={{
						"&::-webkit-scrollbar": {
							display: "none"
						},
						scrollbarWidth: "none"
					}}
				>
					{steps.map((step, index) => (
						<React.Fragment key={step.id}>
							<Flex
								align="center"
								cursor={
									isStepClickable(step.stepNumber)
										? "pointer"
										: "default"
								}
								onClick={() => {
									if (isStepClickable(step.stepNumber)) {
										onStepClick(step.stepNumber);
									}
								}}
								opacity={
									isStepClickable(step.stepNumber) ? 1 : 0.6
								}
								_hover={
									isStepClickable(step.stepNumber)
										? { opacity: 0.8 }
										: undefined
								}
								role="button"
								aria-label={`Go to ${step.label}`}
								tabIndex={
									isStepClickable(step.stepNumber) ? 0 : -1
								}
								flexShrink={0}
							>
								<Circle
									size="26px"
									bg={
										isStepCompleted(step.stepNumber) ||
										isStepVisited(step.stepNumber)
											? activeColor
											: "white"
									}
									borderWidth="2px"
									borderColor={
										isStepCompleted(step.stepNumber) ||
										isStepCurrent(step.stepNumber) ||
										isStepVisited(step.stepNumber)
											? activeColor
											: mutedColor
									}
									color={
										isStepCompleted(step.stepNumber) ||
										isStepVisited(step.stepNumber)
											? "white"
											: isStepCurrent(step.stepNumber)
											? activeColor
											: mutedColor
									}
								>
									{isStepCompleted(step.stepNumber) ||
									isStepVisited(step.stepNumber) ? (
										<Icon as={CheckIcon} boxSize={3} />
									) : (
										<Text fontSize="xs" fontWeight="600">
											{step.stepNumber}
										</Text>
									)}
								</Circle>

								<Text
									ml={2}
									fontSize="sm"
									fontWeight={
										isStepCurrent(step.stepNumber) ||
										isStepCompleted(step.stepNumber) ||
										isStepVisited(step.stepNumber)
											? "600"
											: "400"
									}
									color={
										isStepCurrent(step.stepNumber) ||
										isStepCompleted(step.stepNumber) ||
										isStepVisited(step.stepNumber)
											? textColor
											: mutedColor
									}
									whiteSpace="nowrap"
								>
									{step.label}
								</Text>
							</Flex>

							{/* Connector Line */}
							{index < steps.length - 1 && (
								<Box
									h="2px"
									w={{ base: "30px", md: "50px", lg: "70px" }}
									mx={{ base: 2, md: 3 }}
									bg={
										steps[index + 1].stepNumber <=
										maxCompletedStep
											? activeColor
											: lineColor
									}
									borderRadius="full"
									flexShrink={0}
								/>
							)}
						</React.Fragment>
					))}
				</HStack>

				{/* Close Button - Right */}
				<Box flexShrink={0}>
					{onClose && (
						<Box
							as="button"
							onClick={onClose}
							p={2}
							borderRadius="md"
							color={mutedColor}
							display="flex"
							alignItems="center"
							justifyContent="center"
							transition="all 0.2s"
							_hover={{
								bg: hoverBg,
								color: textColor
							}}
							aria-label="Close wizard"
						>
							<CloseXIcon />
						</Box>
					)}
				</Box>
			</Flex>
		</Box>
	);
};

export default Stepper;
