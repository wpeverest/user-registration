import { useEffect } from "react";

const Settings = () => {
	const { isPro, settingsURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	useEffect(() => {
		window.open(settingsURL);
		window.history.back();
	}, [settingsURL]);

	return null; // Render nothing or a loading indicator
};

export default Settings;
