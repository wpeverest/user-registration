import EditPassword from "./modules/EditPassword";
import EditProfile from "./modules/EditProfile";
import LoginForm from "./modules/LoginForm";
import MembershipGroups from "./modules/MembershipGroups";
import MembershipThankYou from "./modules/MembershipThankYou";
import MyAccount from "./modules/MyAccount";
import RegistrationForm from "./modules/RegistrationForm";

jQuery(window).on("et_builder_api_ready", (_, API) => {
	API.registerModules([
		RegistrationForm,
		LoginForm,
		MyAccount,
		EditPassword,
		EditProfile,
		MembershipGroups,
		MembershipThankYou
	]);
});
