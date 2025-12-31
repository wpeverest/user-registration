export type MembershipSetupType = "paid" | "free" | "other";
export type MembershipPlanType = "free" | "paid";
export type BillingPeriod = "weekly" | "monthly" | "yearly" | "one-time";

export interface ContentAccess {
	id: string;
	type: "posts" | "pages" | "wholesite";
	value: number[];
}

export interface MembershipPlan {
	id: string;
	name: string;
	type: MembershipPlanType;
	price: string;
	billingPeriod: BillingPeriod;
	contentAccess: ContentAccess[];
	isNew?: boolean;
}

export interface PaymentSettings {
	currency: string;
	offlinePayment: boolean;
	bankDetails: string;
	paypal: boolean;
	paypalEmail: string;
	paypalClientId: string;
	paypalClientSecret: string;
	stripe: boolean;
	stripeTestMode: boolean;
	stripeTestPublishableKey: string;
	stripeTestSecretKey: string;
	stripeLivePublishableKey: string;
	stripeLiveSecretKey: string;
}

export interface GettingStartedState {
	currentStep: number;
	maxCompletedStep: number;
	isLoading: boolean;
	membershipSetupType: MembershipSetupType;
	allowTracking: boolean;
	adminEmail: string;
	membershipOptions: {
		value: string;
		label: string;
		description: string;
	}[];
	membershipPlans: MembershipPlan[];
	paymentSettings: PaymentSettings;
	registrationSettings: {
		loginOption: string;
		defaultRole: string;
	};
}

const generateId = () => Math.random().toString(36).substring(2, 9);

const createDefaultPlan = (
	type: MembershipPlanType = "free"
): MembershipPlan => ({
	id: generateId(),
	name: "",
	type: type,
	price: "",
	billingPeriod: "one-time",
	contentAccess: [],
	isNew: false
});


export const getTotalStepsForType = (
	membershipType: MembershipSetupType
): number => {
	switch (membershipType) {
		case "paid":
			return 4;
		case "free":
			return 3;
		case "other":
			return 3; // Welcome → Settings → Finish
		default:
			return 4;
	}
};

export const initialState: GettingStartedState = {
	currentStep: 1,
	maxCompletedStep: 1,
	isLoading: false,
	membershipSetupType: "paid",
	allowTracking: true,
	adminEmail: "",
	membershipOptions: [
		{
			value: "paid_membership",
			label: "Paid Membership",
			description:
				"Charge users to access premium content (you can offer free plans too)."
		},
		{
			value: "free_membership",
			label: "Free Membership",
			description:
				"Let users register for free and access members-only content."
		},
		{
			value: "normal",
			label: "Advanced Registration",
			description:
				"Complete registration system to replace WordPress's basic signup. Custom signup fields, login & account pages, and user approval."
		}
	],
	membershipPlans: [createDefaultPlan("paid")],
	paymentSettings: {
		currency: "USD",
		offlinePayment: false,
		bankDetails: "",
		paypal: false,
		paypalEmail: "",
		paypalClientId: "",
		paypalClientSecret: "",
		stripe: false,
		stripeTestMode: false,
		stripeTestPublishableKey: "",
		stripeTestSecretKey: "",
		stripeLivePublishableKey: "",
		stripeLiveSecretKey: ""
	},
	registrationSettings: {
		loginOption: "default",
		defaultRole: "subscriber"
	}
};

export type Action =
	| { type: "SET_LOADING"; payload: boolean }
	| { type: "NEXT_STEP" }
	| { type: "PREV_STEP" }
	| { type: "SET_STEP"; payload: number }
	| { type: "SET_MEMBERSHIP_SETUP_TYPE"; payload: MembershipSetupType }
	| { type: "SET_ALLOW_TRACKING"; payload: boolean }
	| { type: "SET_ADMIN_EMAIL"; payload: string }
	| { type: "ADD_MEMBERSHIP_PLAN"; payload?: MembershipPlan }
	| {
			type: "UPDATE_MEMBERSHIP_PLAN";
			payload: { id: string; updates: Partial<MembershipPlan> };
	  }
	| { type: "REMOVE_MEMBERSHIP_PLAN"; payload: string }
	| {
			type: "ADD_CONTENT_ACCESS";
			payload: { planId: string; access: ContentAccess };
	  }
	| {
			type: "SET_PAYMENT_SETTING";
			payload: { key: keyof PaymentSettings; value: boolean | string };
	  }
	| {
			type: "SET_REGISTRATION_SETTINGS";
			payload: { loginOption: string; defaultRole: string };
	  }
	| {
			type: "HYDRATE_FROM_API";
			payload: Partial<GettingStartedState>;
	  }
	| {
			type: "HYDRATE_PAYMENT_SETTINGS";
			payload: Partial<PaymentSettings>;
	  };

export const reducer = (
	state: GettingStartedState,
	action: Action
): GettingStartedState => {
	switch (action.type) {
		case "SET_LOADING":
			return { ...state, isLoading: action.payload };

		case "NEXT_STEP":
			const totalSteps = getTotalStepsForType(state.membershipSetupType);
			const nextStep = Math.min(state.currentStep + 1, totalSteps);
			return {
				...state,
				currentStep: nextStep,
				maxCompletedStep: Math.max(state.maxCompletedStep, nextStep)
			};

		case "PREV_STEP":
			return {
				...state,
				currentStep: Math.max(state.currentStep - 1, 1)
			};

		case "SET_STEP":
			return { ...state, currentStep: action.payload };

		case "SET_MEMBERSHIP_SETUP_TYPE":

			const newDefaultType: MembershipPlanType =
				action.payload === "paid" ? "paid" : "free";


			const updatedPlansOnTypeChange = state.membershipPlans.map(
				(plan, index) => {
					if (index === 0 && plan.name === "") {
						return { ...plan, type: newDefaultType };
					}
					return plan;
				}
			);

			return {
				...state,
				membershipSetupType: action.payload,
				membershipPlans: updatedPlansOnTypeChange
			};

		case "SET_ALLOW_TRACKING":
			return { ...state, allowTracking: action.payload };

		case "SET_ADMIN_EMAIL":
			return { ...state, adminEmail: action.payload };

		case "ADD_MEMBERSHIP_PLAN":
			const defaultTypeForNewPlan: MembershipPlanType =
				state.membershipSetupType === "paid" ? "paid" : "free";
			const newPlan = action.payload || {
				...createDefaultPlan(defaultTypeForNewPlan),
				isNew: true
			};
			return {
				...state,
				membershipPlans: [...state.membershipPlans, newPlan]
			};

		case "UPDATE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.id
						? { ...plan, ...action.payload.updates }
						: plan
				)
			};

		case "REMOVE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.filter(
					(plan) => plan.id !== action.payload
				)
			};

		case "ADD_CONTENT_ACCESS":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.planId
						? {
								...plan,
								contentAccess: [
									...plan.contentAccess,
									action.payload.access
								]
						  }
						: plan
				)
			};

		case "SET_PAYMENT_SETTING":
			return {
				...state,
				paymentSettings: {
					...state.paymentSettings,
					[action.payload.key]: action.payload.value
				}
			};

		case "SET_REGISTRATION_SETTINGS":
			return {
				...state,
				registrationSettings: {
					...state.registrationSettings,
					...action.payload
				}
			};

		case "HYDRATE_FROM_API":
			const hydratedStep =
				action.payload.currentStep || state.currentStep;
			const hydratedSetupType =
				action.payload.membershipSetupType || state.membershipSetupType;

			// Handle membership plans hydration
			let hydratedPlans = state.membershipPlans;

			if (
				action.payload.membershipPlans &&
				action.payload.membershipPlans.length > 0
			) {
				// If API returns saved memberships, use them directly
				hydratedPlans = action.payload.membershipPlans;
			} else if (
				state.membershipPlans.length > 0 &&
				state.membershipPlans[0].name === ""
			) {
				// If there are only default plans with empty names, update their type based on membershipSetupType
				const syncedType: MembershipPlanType =
					hydratedSetupType === "paid" ? "paid" : "free";
				hydratedPlans = state.membershipPlans.map((plan, index) => {
					if (index === 0 && plan.name === "") {
						return { ...plan, type: syncedType };
					}
					return plan;
				});
			}

			return {
				...state,
				...action.payload,
				membershipPlans: hydratedPlans,
				maxCompletedStep: Math.max(
					state.maxCompletedStep,
					hydratedStep,
					action.payload.maxCompletedStep || 1
				)
			};

		case "HYDRATE_PAYMENT_SETTINGS":
			return {
				...state,
				paymentSettings: {
					...state.paymentSettings,
					...action.payload
				}
			};

		default:
			return state;
	}
};
