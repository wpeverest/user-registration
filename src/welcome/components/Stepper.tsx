import React from "react";
import {
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
	maxCompletedStep?: number; // Optional - kept for backward compatibility
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
		stepNumber <= currentStep + 1 && stepNumber <= steps.length;

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
				width="100%"
				position="relative"
			>
				<Flex align="center" flexShrink={0} zIndex={2}>
					<URMLogo height="40px" />
				</Flex>

				<Flex
					position="absolute"
					left="0"
					right="0"
					justify="center"
					align="center"
					pointerEvents="none"
					px={{ base: 4, md: 6, lg: 10 }}
				>
					<Flex
						align="center"
						justify="space-between"
						w="100%"
						maxW="750px"
						pointerEvents="auto"
					>
						{steps.map((step, index) => (
							<React.Fragment key={step.id}>
								{/* Step Item */}
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
										isStepClickable(step.stepNumber)
											? 1
											: 0.6
									}
									_hover={
										isStepClickable(step.stepNumber)
											? { opacity: 0.8 }
											: undefined
									}
									role="button"
									aria-label={`Go to ${step.label}`}
									tabIndex={
										isStepClickable(step.stepNumber)
											? 0
											: -1
									}
									flexShrink={0}
								>
									{/* Step Circle */}
									<Circle
										size="28px"
										bg={
											isStepCompleted(step.stepNumber)
												? activeColor
												: "white"
										}
										borderWidth="2px"
										borderColor={
											isStepCompleted(step.stepNumber) ||
											isStepCurrent(step.stepNumber)
												? activeColor
												: mutedColor
										}
										color={
											isStepCompleted(step.stepNumber)
												? "white"
												: isStepCurrent(step.stepNumber)
												? activeColor
												: mutedColor
										}
										flexShrink={0}
									>
										{isStepCompleted(step.stepNumber) ? (
											<Icon as={CheckIcon} boxSize={3} />
										) : (
											<Text
												fontSize="xs"
												fontWeight="600"
											>
												{step.stepNumber}
											</Text>
										)}
									</Circle>

									{/* Step Label - Beside Circle */}
									<Text
										ml={2}
										fontSize="sm"
										fontWeight={
											isStepCurrent(step.stepNumber) ||
											isStepCompleted(step.stepNumber)
												? "600"
												: "400"
										}
										color={
											isStepCurrent(step.stepNumber) ||
											isStepCompleted(step.stepNumber)
												? textColor
												: mutedColor
										}
										whiteSpace="nowrap"
									>
										{step.label}
									</Text>
								</Flex>

								{index < steps.length - 1 && (
									<Box
										flex={1}
										h="2px"
										mx={{ base: 2, md: 4 }}
										minW="20px"
										bg={
											step.stepNumber < currentStep
												? activeColor
												: lineColor
										}
										transition="background-color 0.3s ease"
									/>
								)}
							</React.Fragment>
						))}
					</Flex>
				</Flex>

				<Box flexShrink={0} zIndex={2}>
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
