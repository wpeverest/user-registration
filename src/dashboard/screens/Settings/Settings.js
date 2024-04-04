import { useEffect } from "react";

const Settings = ({ to }) => {
	useEffect(() => {
		window.open(to);
		window.history.back();
	}, [to]);

	return null; // Render nothing or a loading indicator
};

export default Settings;
