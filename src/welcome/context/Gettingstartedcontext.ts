export type MembershipSetupType = "paid" | "free" | "other";

export interface ContentAccessRule {
	type: "posts" | "pages";
	value: number[];
}

export interface MembershipPlan {
	id: string;
	name: string;
	type: "free" | "paid";
	price: number;
	currency: string;
	billingPeriod: "weekly" | "monthly" | "yearly";
	contentAccess: ContentAccessRule[];
}

export interface PaymentSettings {
	offlinePayment: boolean;
	bankDetails: string;
	paypal: boolean;
	paypalEmail: string;
	stripe: boolean;
	stripeTestMode: boolean;
	stripeTestPublishableKey: string;
	stripeTestSecretKey: string;
	stripeLivePublishableKey: string;
	stripeLiveSecretKey: string;
}

export interface GettingStartedState {
	currentStep: number;
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

export const initialState: GettingStartedState = {
	currentStep: 1,
	isLoading: false,
	membershipSetupType: "paid",
	allowTracking: false,
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
	membershipPlans: [],
	paymentSettings: {
		offlinePayment: false,
		bankDetails: "",
		paypal: false,
		paypalEmail: "",
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
	| { type: "ADD_MEMBERSHIP_PLAN"; payload: MembershipPlan }
	| { type: "UPDATE_MEMBERSHIP_PLAN"; payload: MembershipPlan }
	| { type: "REMOVE_MEMBERSHIP_PLAN"; payload: string }
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
			return {
				...state,
				currentStep: Math.min(state.currentStep + 1, 4)
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
			return {
				...state,
				membershipPlans: [...state.membershipPlans, action.payload]
			};

		case "UPDATE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.id ? action.payload : plan
				)
			};

		case "REMOVE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.filter(
					(plan) => plan.id !== action.payload
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
			return { ...state, ...action.payload };

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
