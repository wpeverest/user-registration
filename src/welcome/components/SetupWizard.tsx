import React, { useEffect, useMemo } from "react";
import { Box, Button, Flex, Link, useColorModeValue } from "@chakra-ui/react";
import { ArrowBackIcon, ArrowForwardIcon } from "@chakra-ui/icons";
import { useStateValue } from "../context/StateProvider";
import {
	MembershipPlan,
	MembershipSetupType
} from "../context/Gettingstartedcontext";
import {
	apiGet,
	apiPost,
	mapApiToSetupType,
	mapSetupToApiType,
	mapPaymentSettingsToApi
} from "../api/gettingStartedApi";
import Stepper from "./Stepper";
import WelcomeStep from "./steps/WelcomeStep";
import MembershipStep from "./steps/MembershipStep";
import PaymentStep from "./steps/PaymentStep";
import FinishStep from "./steps/FinishStep";

interface StepConfig {
	id: string;
	label: string;
	stepNumber: number;
}

const ALL_STEPS: StepConfig[] = [
	{ id: "welcome", label: "Welcome", stepNumber: 1 },
	{ id: "membership", label: "Membership", stepNumber: 2 },
	{ id: "payment", label: "Payment", stepNumber: 3 },
	{ id: "finish", label: "Finish", stepNumber: 4 }
];

// Get visible steps based on membership type and plan types
const getVisibleSteps = (
	membershipType: MembershipSetupType,
	hasPaidPlan: boolean
): StepConfig[] => {
	let filteredSteps: StepConfig[];

	switch (membershipType) {
		case "paid":
			// Show all steps: Welcome, Membership, Payment, Finish
			filteredSteps = ALL_STEPS;
			break;
		case "free":
			// Hide Payment: Welcome, Membership, Finish
			// But if user adds a paid plan in membership step, show Payment
			if (hasPaidPlan) {
				filteredSteps = ALL_STEPS;
			} else {
				filteredSteps = ALL_STEPS.filter(
					(step) => step.id !== "payment"
				);
			}
			break;
		case "other":
			// Hide Membership and Payment: Welcome, Finish
			filteredSteps = ALL_STEPS.filter(
				(step) => step.id !== "membership" && step.id !== "payment"
			);
			break;
		default:
			filteredSteps = ALL_STEPS;
	}

	// Renumber steps sequentially (1, 2, 3, ...)
	return filteredSteps.map((step, index) => ({
		...step,
		stepNumber: index + 1
	}));
};

// Get the step ID for a given display step number
const getStepIdByDisplayNumber = (
	displayNumber: number,
	visibleSteps: StepConfig[]
): string => {
	const step = visibleSteps.find((s) => s.stepNumber === displayNumber);
	return step ? step.id : "welcome";
};

// Get the display step number for a given step ID
const getDisplayNumberByStepId = (
	stepId: string,
	visibleSteps: StepConfig[]
): number => {
	const step = visibleSteps.find((s) => s.id === stepId);
	return step ? step.stepNumber : 1;
};

const HEADER_HEIGHT = "70px";

const SetupWizard: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const {
		currentStep,
		maxCompletedStep,
		isLoading,
		membershipSetupType,
		membershipPlans
	} = state;

	const cardBg = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.700");
	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const pageBg = useColorModeValue("gray.50", "gray.900");

	// Check if any membership plan is paid
	const hasPaidPlan = membershipPlans.some((plan) => plan.type === "paid");

	// Get visible steps based on membership type and paid plans
	const visibleSteps = useMemo(
		() => getVisibleSteps(membershipSetupType, hasPaidPlan),
		[membershipSetupType, hasPaidPlan]
	);

	const totalSteps = visibleSteps.length;

	// Get current step ID based on current step number
	const currentStepId = getStepIdByDisplayNumber(currentStep, visibleSteps);

	// Check if current step is the finish step
	const isFinishStep = currentStepId === "finish";

	useEffect(() => {
		const loadInitial = async () => {
			try {
				dispatch({ type: "SET_LOADING", payload: true });
				const wizard: any = await apiGet("");
				const welcome: any = await apiGet("/welcome").catch(() => null);

				dispatch({
					type: "HYDRATE_FROM_API",
					payload: {
						currentStep: wizard.current_step || 1,
						membershipSetupType: wizard.membership_type
							? mapApiToSetupType(wizard.membership_type)
							: state.membershipSetupType,
						allowTracking:
							typeof welcome?.allow_usage_tracking === "boolean"
								? welcome.allow_usage_tracking
								: state.allowTracking,
						adminEmail:
							typeof welcome?.admin_email === "string" &&
							welcome.admin_email
								? welcome.admin_email
								: state.adminEmail,
						membershipOptions: Array.isArray(
							welcome?.membership_options
						)
							? welcome.membership_options
							: state.membershipOptions
					}
				});
			} catch (e) {
				console.error(e);
			} finally {
				dispatch({ type: "SET_LOADING", payload: false });
			}
		};

		loadInitial();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, []);

	const mapPlanToApi = (plan: MembershipPlan) => {
		return {
			id: plan.id,
			name: plan.name,
			type: plan.type,
			price: plan.price,
			currency: plan.currency,
			billing_period: plan.billingPeriod,
			access: plan.contentAccess.map((a) => ({
				type: a.type,
				value: a.value
			}))
		};
	};

	const handleNext = async () => {
		try {
			dispatch({ type: "SET_LOADING", payload: true });

			if (currentStepId === "welcome") {
				await apiPost("/welcome", {
					membership_type: mapSetupToApiType(
						state.membershipSetupType
					),
					allow_usage_tracking: state.allowTracking,
					allow_email_updates: state.allowTracking,
					admin_email: state.adminEmail
				});

				// If "Other URM Features" is selected, finish is next step (step 2)
				if (membershipSetupType === "other") {
					await apiPost("/finish");
				}
			} else if (currentStepId === "membership") {
				await apiPost("/memberships", {
					memberships: state.membershipPlans.map(mapPlanToApi)
				});

				// If no paid plans, finish is next (skip payment)
				if (!hasPaidPlan) {
					await apiPost("/finish");
				}
			} else if (currentStepId === "payment") {
				await apiPost(
					"/payments",
					mapPaymentSettingsToApi(state.paymentSettings)
				);
				await apiPost("/finish");
			}

			// Move to next step (will be capped at totalSteps in reducer)
			if (currentStep < totalSteps) {
				dispatch({ type: "SET_STEP", payload: currentStep + 1 });
			}
		} catch (e) {
			console.error(e);
		} finally {
			dispatch({ type: "SET_LOADING", payload: false });
		}
	};

	const handleBack = () => {
		if (currentStep > 1) {
			dispatch({ type: "SET_STEP", payload: currentStep - 1 });
		}
	};

	const handleSkip = async () => {
		try {
			dispatch({ type: "SET_LOADING", payload: true });
			await apiPost("/skip", { step: currentStep });

			// Move to next step
			if (currentStep < totalSteps) {
				dispatch({ type: "SET_STEP", payload: currentStep + 1 });
			}
		} catch (e) {
			console.error(e);
		} finally {
			dispatch({ type: "SET_LOADING", payload: false });
		}
	};

	const handleStepClick = (stepNumber: number) => {
		if (stepNumber <= maxCompletedStep && stepNumber !== currentStep) {
			dispatch({ type: "SET_STEP", payload: stepNumber });
		}
	};

	const handleClose = () => {
		const dashboardUrl =
			(window as any).urmSetupWizard?.dashboardUrl ||
			"/wp-admin/admin.php?page=user-registration";
		window.location.href = dashboardUrl;
	};

	const renderStep = () => {
		switch (currentStepId) {
			case "welcome":
				return <WelcomeStep />;
			case "membership":
				return <MembershipStep />;
			case "payment":
				return <PaymentStep />;
			case "finish":
				return <FinishStep />;
			default:
				return <WelcomeStep />;
		}
	};

	return (
		<Box minH="100vh" bg={pageBg}>
			<Stepper
				steps={visibleSteps}
				currentStep={currentStep}
				maxCompletedStep={maxCompletedStep}
				onStepClick={handleStepClick}
				onClose={handleClose}
			/>

			<Box pt={HEADER_HEIGHT}>
				<Flex justify="center" align="flex-start" px={4} py={10}>
					<Box
						w="100%"
						maxW="800px"
						bg={cardBg}
						borderWidth="1px"
						borderColor={borderColor}
						borderRadius="xl"
						p={8}
						boxShadow="sm"
					>
						<Box mb={isFinishStep ? 0 : 8}>{renderStep()}</Box>
						{!isFinishStep && (
							<Flex justify="space-between" align="center">
								{/* Back Link */}
								<Link
									display="flex"
									alignItems="center"
									fontSize="sm"
									color={mutedColor}
									_hover={{
										color: textColor,
										textDecoration: "none"
									}}
									cursor={
										currentStep === 1
											? "not-allowed"
											: "pointer"
									}
									onClick={
										currentStep > 1 ? handleBack : undefined
									}
									opacity={currentStep === 1 ? 0.5 : 1}
								>
									<ArrowBackIcon mr={1} />
									Back
								</Link>

								<Flex gap={4} align="center">
									<Link
										fontSize="sm"
										color={mutedColor}
										_hover={{
											color: textColor,
											textDecoration: "none"
										}}
										cursor="pointer"
										onClick={handleSkip}
									>
										Skip this step
									</Link>
									<Button
										bg="#475BD8"
										color="white"
										rightIcon={<ArrowForwardIcon />}
										_hover={{ bg: "#3a4bc2" }}
										_active={{ bg: "#2f3da6" }}
										onClick={handleNext}
										isLoading={isLoading}
										px={6}
									>
										Next
									</Button>
								</Flex>
							</Flex>
						)}
					</Box>
				</Flex>
			</Box>
		</Box>
	);
};

export default SetupWizard;
