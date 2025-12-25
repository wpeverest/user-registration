export type MembershipSetupType = "paid" | "free" | "other";
export type MembershipPlanType = "free" | "paid";
export type BillingPeriod = "weekly" | "monthly" | "yearly" | "one-time";

export interface ContentAccess {
	id: string;
	type: "posts" | "pages";
	value: number[];
}

export interface MembershipPlan {
	id: string;
	name: string;
	type: MembershipPlanType;
	price: string;
	currency: string;
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
}

const generateId = () => Math.random().toString(36).substring(2, 9);

const createDefaultPlan = (): MembershipPlan => ({
	id: generateId(),
	name: "",
	type: "free",
	price: "",
	currency: "USD",
	billingPeriod: "monthly",
	contentAccess: [],
	isNew: false
});

// Helper to get total steps for a membership type
export const getTotalStepsForType = (membershipType: MembershipSetupType): number => {
	switch (membershipType) {
		case "paid":
			return 4; // Welcome, Membership, Payment, Finish
		case "free":
			return 3; // Welcome, Membership, Finish
		case "other":
			return 2; // Welcome, Finish
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
				"Paid members can access protected content. Choose this even if you have combination of both free and paid."
		},
		{
			value: "free_membership",
			label: "Free Membership",
			description: "Registered users can access protected content."
		},
		{
			value: "normal",
			label: "Other URM Features (no membership now)",
			description:
				"I want registration and other features without membership."
		}
	],
	membershipPlans: [createDefaultPlan()],
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
	| { type: "UPDATE_MEMBERSHIP_PLAN"; payload: { id: string; updates: Partial<MembershipPlan> } }
	| { type: "REMOVE_MEMBERSHIP_PLAN"; payload: string }
	| { type: "ADD_CONTENT_ACCESS"; payload: { planId: string; access: ContentAccess } }
	| {
			type: "SET_PAYMENT_SETTING";
			payload: { key: keyof PaymentSettings; value: boolean | string };
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
			return { ...state, membershipSetupType: action.payload };

		case "SET_ALLOW_TRACKING":
			return { ...state, allowTracking: action.payload };

		case "SET_ADMIN_EMAIL":
			return { ...state, adminEmail: action.payload };

		case "ADD_MEMBERSHIP_PLAN":
			const newPlan = action.payload || {
				...createDefaultPlan(),
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
								contentAccess: [...plan.contentAccess, action.payload.access]
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

		case "HYDRATE_FROM_API":
			const hydratedStep = action.payload.currentStep || state.currentStep;
			return {
				...state,
				...action.payload,
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
