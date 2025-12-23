export type MembershipSetupType = "paid" | "free" | "other";
export type MembershipPlanType = "free" | "paid";
export type BillingPeriod = "weekly" | "monthly" | "yearly" | "one-time";
export type ContentAccessType =
	| "pages"
	| "posts"

export interface MembershipOptionFromApi {
	value: string;
	label: string;
	description: string;
}

export interface GettingStartedState {
	currentStep: number;
	membershipSetupType: MembershipSetupType;
	allowTracking: boolean;
	membershipPlans: MembershipPlan[];
	paymentSettings: PaymentSettings;
	isLoading: boolean;
	adminEmail: string;
	membershipOptions: MembershipOptionFromApi[];
}

export interface ContentAccess {
	id: string;
	type: ContentAccessType;
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
}

export interface PaymentSettings {
	offlinePayment: boolean;
	paypal: boolean;
	stripe: boolean;
}

export interface GettingStartedState {
	currentStep: number;
	membershipSetupType: MembershipSetupType;
	allowTracking: boolean;
	membershipPlans: MembershipPlan[];
	paymentSettings: PaymentSettings;
	isLoading: boolean;
	adminEmail: string;
}

export type GettingStartedAction =
	| { type: "SET_STEP"; payload: number }
	| { type: "NEXT_STEP" }
	| { type: "PREV_STEP" }
	| { type: "SET_MEMBERSHIP_SETUP_TYPE"; payload: MembershipSetupType }
	| { type: "SET_ALLOW_TRACKING"; payload: boolean }
	| { type: "ADD_MEMBERSHIP_PLAN" }
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
			type: "REMOVE_CONTENT_ACCESS";
			payload: { planId: string; accessId: string };
	  }
	| {
			type: "SET_PAYMENT_SETTING";
			payload: { key: keyof PaymentSettings; value: boolean };
	  }
	| { type: "SET_LOADING"; payload: boolean }
	| { type: "RESET" }
	| { type: "HYDRATE_FROM_API"; payload: Partial<GettingStartedState> }
	| { type: "SET_ADMIN_EMAIL"; payload: string };

const generateId = (): string => Math.random().toString(36).substring(2, 9);

const createDefaultPlan = (
	name: string = "",
	type: MembershipPlanType = "free"
): MembershipPlan => ({
	id: generateId(),
	name,
	type,
	price: "10.00",
	currency: "USD",
	billingPeriod: "yearly",
	contentAccess: [],
});

export const initialState: GettingStartedState = {
	currentStep: 1,
	membershipSetupType: "paid",
	allowTracking: true,
	membershipPlans: [
		createDefaultPlan("Silver", "free"),
		createDefaultPlan("Gold", "paid"),
	],
	paymentSettings: {
		offlinePayment: false,
		paypal: false,
		stripe: false,
	},
	isLoading: false,
	adminEmail: "",
	membershipOptions: [],
};


const reducer = (
	state: GettingStartedState,
	action: GettingStartedAction
): GettingStartedState => {
	switch (action.type) {
		case "SET_STEP":
			return {
				...state,
				currentStep: action.payload,
			};
		case "NEXT_STEP":
			return {
				...state,
				currentStep: Math.min(state.currentStep + 1, 4),
			};
		case "PREV_STEP":
			return {
				...state,
				currentStep: Math.max(state.currentStep - 1, 1),
			};
		case "SET_MEMBERSHIP_SETUP_TYPE":
			return {
				...state,
				membershipSetupType: action.payload,
			};
		case "SET_ALLOW_TRACKING":
			return {
				...state,
				allowTracking: action.payload,
			};
		case "ADD_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: [...state.membershipPlans, createDefaultPlan()],
			};
		case "UPDATE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.id
						? { ...plan, ...action.payload.updates }
						: plan
				),
			};
		case "REMOVE_MEMBERSHIP_PLAN":
			return {
				...state,
				membershipPlans: state.membershipPlans.filter(
					(plan) => plan.id !== action.payload
				),
			};
		case "ADD_CONTENT_ACCESS":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.planId
						? {
								...plan,
								contentAccess: [...plan.contentAccess, action.payload.access],
						  }
						: plan
				),
			};
		case "REMOVE_CONTENT_ACCESS":
			return {
				...state,
				membershipPlans: state.membershipPlans.map((plan) =>
					plan.id === action.payload.planId
						? {
								...plan,
								contentAccess: plan.contentAccess.filter(
									(access) => access.id !== action.payload.accessId
								),
						  }
						: plan
				),
			};
		case "SET_PAYMENT_SETTING":
			return {
				...state,
				paymentSettings: {
					...state.paymentSettings,
					[action.payload.key]: action.payload.value,
				},
			};
		case "SET_LOADING":
			return {
				...state,
				isLoading: action.payload,
			};
		case "RESET":
			return initialState;
		case "HYDRATE_FROM_API":
			return {
				...state,
				...action.payload,
			};
		case "SET_ADMIN_EMAIL":
			return {
				...state,
				adminEmail: action.payload,
			};
		default:
			return state;
	}
};

export default reducer;
