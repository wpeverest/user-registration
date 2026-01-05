import { CheckIcon } from "@chakra-ui/icons";
import {
	Box,
	Circle,
	Flex,
	Icon,
	Text,
	Tooltip,
	useBreakpointValue,
	useColorModeValue
} from "@chakra-ui/react";
import React from "react";
import { URMLogo } from "./Icon/Icon";

interface Step {
	id: string;
	label: string;
	stepNumber: number;
}

interface StepperProps {
	steps: Step[];
	currentStep: number;
	maxCompletedStep?: number;
	onStepClick: (stepNumber: number) => void;
	onClose?: () => void;
}

const CloseXIcon: React.FC = () => (
	<svg
		width="22"
		height="22"
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
	const activeColor = "#475BB2";
	const lineColor = useColorModeValue("gray.300", "gray.600");
	const closeIconColor = "#909090";

	const showLabels = useBreakpointValue({ base: false, md: false, lg: true });

	const circleSize = useBreakpointValue({ base: "24px", md: "28px" });

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
			py={3}
			px={{ base: 3, md: 4, lg: 6 }}
			zIndex={1000}
		>
			<Flex justify="space-between" align="center" width="100%">
				{/* Logo - Far Left - Hidden on mobile and tablet */}
				<Flex
					align="center"
					flexShrink={0}
					display={{ base: "none", lg: "flex" }}
				>
					<Box
						sx={{
							"& svg": {
								height: { base: "32px", md: "40px" },
								width: "auto"
							}
						}}
					>
						<URMLogo />
					</Box>
				</Flex>

				<Flex
					position={{ base: "relative", lg: "absolute" }}
					left={{ base: "auto", lg: "50%" }}
					transform={{ base: "none", lg: "translateX(-50%)" }}
					align="center"
					justify="center"
					w="100%"
					maxW="920px"
					px={{ base: 3, md: 4 }}
				>
					<Flex align="center" justify="space-between" w="100%">
						{steps.map((step, index) => (
							<React.Fragment key={step.id}>
								<Tooltip
									label={step.label}
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
									<Flex
										align="center"
										cursor={
											isStepClickable(step.stepNumber)
												? "pointer"
												: "default"
										}
										onClick={() => {
											if (
												isStepClickable(step.stepNumber)
											) {
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
										<Circle
											size={circleSize}
											bg={
												isStepCompleted(step.stepNumber)
													? activeColor
													: "white"
											}
											borderWidth="2px"
											borderColor={
												isStepCompleted(
													step.stepNumber
												) ||
												isStepCurrent(step.stepNumber)
													? activeColor
													: mutedColor
											}
											color={
												isStepCompleted(step.stepNumber)
													? "white"
													: isStepCurrent(
															step.stepNumber
													  )
													? activeColor
													: mutedColor
											}
											flexShrink={0}
										>
											{isStepCompleted(
												step.stepNumber
											) ? (
												<Icon
													as={CheckIcon}
													boxSize={{
														base: 2.5,
														md: 3
													}}
												/>
											) : (
												<Text
													fontSize={{
														base: "10px",
														md: "xs"
													}}
													fontWeight="600"
												>
													{step.stepNumber}
												</Text>
											)}
										</Circle>

										{/* Show labels only on larger screens */}
										{showLabels && (
											<Text
												ml={2}
												fontSize="sm"
												fontWeight={
													isStepCurrent(
														step.stepNumber
													) ||
													isStepCompleted(
														step.stepNumber
													)
														? "600"
														: "400"
												}
												color={
													isStepCurrent(
														step.stepNumber
													)
														? activeColor
														: isStepCompleted(
																step.stepNumber
														  )
														? textColor
														: mutedColor
												}
												whiteSpace="nowrap"
											>
												{step.label}
											</Text>
										)}
									</Flex>
								</Tooltip>

								{/* Connector line - stretches to fill space */}
								{index < steps.length - 1 && (
									<Box
										h="2px"
										flex={1}
										mx={{ base: 2, md: 3 }}
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

				<Box flexShrink={0} display={{ base: "none", lg: "block" }}>
					{onClose && (
						<Tooltip
							label="Skip to Dashboard"
							hasArrow
							placement="bottom"
							bg="white"
							color="gray.700"
							fontSize="sm"
							px={3}
							py={2}
							borderRadius="md"
							boxShadow="md"
							border="1px solid"
							borderColor="gray.200"
						>
							<Box
								as="button"
								onClick={onClose}
								p={{ base: 1, md: 2 }}
								borderRadius="md"
								color={closeIconColor}
								display="flex"
								alignItems="center"
								justifyContent="center"
								transition="all 0.2s"
								_hover={{
									color: textColor
								}}
								aria-label="Skip to Dashboard"
							>
								<CloseXIcon />
							</Box>
						</Tooltip>
					)}
				</Box>
			</Flex>
		</Box>
	);
};

export default Stepper;
