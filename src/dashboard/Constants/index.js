import { __ } from "@wordpress/i18n";

export const ROUTES = [
	{
		route: "/",
		label: __("Dashboard", "user-registration"),
	},
	{
		route: "/features",
		label: __("Features/Addons", "user-registration"),
	},
	{
		route: "/products",
		label: __("Products", "user-registration"),
	},
	{
		route: "/settings",
		label: __("Settings", "user-registration"),
	},
	{
		route: "/free-vs-pro",
		label: __("Free vs Pro", "user-registration"),
	},
	{
		route: "/help",
		label: __("Help", "user-registration"),
	},
];

export const CHANGELOG_TAG_COLORS = {
	fix: {
		color: "primary.500",
		bgColor: "primary.100",
		scheme: "primary",
	},
	feature: {
		color: "green.500",
		bgColor: "green.50",
		scheme: "green",
	},
	enhance: {
		color: "teal.500",
		bgColor: "teal.50",
		scheme: "teal",
	},
	refactor: {
		color: "pink.500",
		bgColor: "pink.50",
		scheme: "pink",
	},
	dev: {
		color: "orange.500",
		bgColor: "orange.50",
		scheme: "orange",
	},
	tweak: {
		color: "purple.500",
		bgColor: "purple.50",
		scheme: "purple",
	},
};
