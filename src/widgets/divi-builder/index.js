import ContentRestriction from "./modules/ContentRestriction";
import EditPassword from "./modules/EditPassword";
import EditProfile from "./modules/EditProfile";
import LoginForm from "./modules/LoginForm";
import MembershipGroups from "./modules/MembershipGroups";
import MembershipThankYou from "./modules/MembershipThankYou";
import MyAccount from "./modules/MyAccount";
import RegistrationForm from "./modules/RegistrationForm";

const isProEnabled =
	process.env.UR_PRO === "true" || process.env.UR_PRO === true;

jQuery(window).on("et_builder_api_ready", (_, API) => {
	let modules = [
		RegistrationForm,
		LoginForm,
		MyAccount,
		EditPassword,
		EditProfile,
		MembershipGroups,
		MembershipThankYou,
		ContentRestriction
	];

	if (isProEnabled) {
		try {
			const proModules = require("./modules/pro").default;
			modules = [...modules, ...proModules()];
		} catch (error) {
			console.error("Failed to import proModules:", error);
		}
	}

	API.registerModules(modules);
});
