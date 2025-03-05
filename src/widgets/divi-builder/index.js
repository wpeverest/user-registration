import RegistrationForm from "./modules/RegistrationForm";

(($) => {
	$(window).on("et_builder_api_ready", (_, API) => {
		API.registerModules([RegistrationForm]);
	});
})("jQuery");
