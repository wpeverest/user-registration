import React, { useEffect } from "react";
import { Box, Button, Flex, Link, useColorModeValue } from "@chakra-ui/react";
import { ArrowBackIcon, ArrowForwardIcon } from "@chakra-ui/icons";
import { useStateValue } from "../context/StateProvider";
import { MembershipPlan } from "../context/Gettingstartedcontext";
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

const WIZARD_STEPS = [
	{ id: "welcome", label: "Welcome", stepNumber: 1 },
	{ id: "membership", label: "Membership", stepNumber: 2 },
	{ id: "payment", label: "Payment", stepNumber: 3 },
	{ id: "finish", label: "Finish", stepNumber: 4 }
];

const HEADER_HEIGHT = "70px";

const SetupWizard: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { currentStep, maxCompletedStep, isLoading } = state;

	const cardBg = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.700");
	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const pageBg = useColorModeValue("gray.50", "gray.900");

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

			if (currentStep === 1) {
				await apiPost("/welcome", {
					membership_type: mapSetupToApiType(
						state.membershipSetupType
					),
					allow_usage_tracking: state.allowTracking,
					allow_email_updates: state.allowTracking,
					admin_email: state.adminEmail
				});
			} else if (currentStep === 2) {
				await apiPost("/memberships", {
					memberships: state.membershipPlans.map(mapPlanToApi)
				});
			} else if (currentStep === 3) {
				await apiPost(
					"/payments",
					mapPaymentSettingsToApi(state.paymentSettings)
				);
			}

			dispatch({ type: "NEXT_STEP" });

			if (currentStep === 3) {
				await apiPost("/finish");
			}
		} catch (e) {
			console.error(e);
		} finally {
			dispatch({ type: "SET_LOADING", payload: false });
		}
	};

	const handleBack = () => {
		if (currentStep > 1) {
			dispatch({ type: "PREV_STEP" });
		}
	};

	const handleSkip = async () => {
		try {
			dispatch({ type: "SET_LOADING", payload: true });
			await apiPost("/skip", { step: currentStep });
			dispatch({ type: "NEXT_STEP" });
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
		if (currentStep === 1) return <WelcomeStep />;
		if (currentStep === 2) return <MembershipStep />;
		if (currentStep === 3) return <PaymentStep />;
		return <FinishStep />;
	};

	const isFinishStep = currentStep === 4;

	return (
		<Box minH="100vh" bg={pageBg}>
			{/* Fixed Full-width Stepper Header */}
			<Stepper
				steps={WIZARD_STEPS}
				currentStep={currentStep}
				maxCompletedStep={maxCompletedStep}
				onStepClick={handleStepClick}
				onClose={handleClose}
			/>

			{/* Content Area - with top padding for fixed header */}
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
						{/* Step Content */}
						<Box mb={isFinishStep ? 0 : 8}>{renderStep()}</Box>

						{/* Footer Navigation - only show on non-finish steps */}
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

								{/* Right Side Actions */}
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
