import LoginForm from "./modules/LoginForm";
import RegistrationForm from "./modules/RegistrationForm";

jQuery(window).on("et_builder_api_ready", (_, API) => {
		API.registerModules([RegistrationForm, LoginForm]);
	});
