import React, { useEffect } from "react";
import {
	Box,
	Button,
	Flex,
	Heading,
	Text,
	Link,
	useColorModeValue,
	Spinner,
} from "@chakra-ui/react";
import { useStateValue } from "../context/StateProvider";
import { MembershipPlan } from "../context/Gettingstartedcontext";
import {
	apiGet,
	apiPost,
	mapApiToSetupType,
	mapSetupToApiType,
	mapPaymentSettingsToApi,
} from "../api/gettingStartedApi";
import WelcomeStep from "./steps/WelcomeStep";
import MembershipStep from "./steps/MembershipStep";
import PaymentStep from "./steps/PaymentStep";
import FinishStep from "./steps/FinishStep";

const SetupWizard: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { currentStep, isLoading } = state;

	const cardBg = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.700");
	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");

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
							typeof welcome?.admin_email === "string" && welcome.admin_email
								? welcome.admin_email
								: state.adminEmail,
						membershipOptions: Array.isArray(welcome?.membership_options)
							? welcome.membership_options
							: state.membershipOptions,
					},
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
				value: a.value,
			})),
		};
	};

	const handleNext = async () => {
		try {
			dispatch({ type: "SET_LOADING", payload: true });

			if (currentStep === 1) {
				await apiPost("/welcome", {
					membership_type: mapSetupToApiType(state.membershipSetupType),
					allow_usage_tracking: state.allowTracking,
					allow_email_updates: state.allowTracking,
					admin_email: state.adminEmail,
				});
			} else if (currentStep === 2) {
				await apiPost("/memberships", {
					memberships: state.membershipPlans.map(mapPlanToApi),
				});
			} else if (currentStep === 3) {
				// Use the new mapping function for payment settings
				await apiPost("/payments", mapPaymentSettingsToApi(state.paymentSettings));
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

	const renderStep = () => {
		if (currentStep === 1) return <WelcomeStep />;
		if (currentStep === 2) return <MembershipStep />;
		if (currentStep === 3) return <PaymentStep />;
		return <FinishStep />;
	};

	return (
		<Flex justify="center" align="center" minH="100vh" px={4}>
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
				<Flex justify="space-between" align="center" mb={6}>
					<Heading size="md" color={textColor}>
						User Registration Setup Wizard
					</Heading>
					<Text fontSize="sm" color={mutedColor}>
						Step {currentStep} of 4
					</Text>
				</Flex>

				<Box mb={8}>{renderStep()}</Box>

				<Flex justify="space-between" align="center">
					<Link
						fontSize="sm"
						color={mutedColor}
						_hover={{ color: textColor }}
						cursor="pointer"
						onClick={handleSkip}
					>
						Skip this step
					</Link>
					<Flex gap={3} align="center">
						<Button
							variant="outline"
							onClick={handleBack}
							isDisabled={currentStep === 1 || isLoading}
						>
							Back
						</Button>
						<Button
							colorScheme="brand"
							onClick={handleNext}
							isLoading={isLoading}
						>
							{currentStep === 4 ? "Finish" : "Next"}
						</Button>
						{isLoading && <Spinner size="sm" ml={2} />}
					</Flex>
				</Flex>
			</Box>
		</Flex>
	);
};

export default SetupWizard;
