import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";

const Settings = ({ to }) => {
	useEffect(() => {
		window.location.replace(to);
	}, [to]);

	return null; // Render nothing or a loading indicator
};

export default Settings;
