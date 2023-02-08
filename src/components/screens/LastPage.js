/**
 * External Dependencies
 */
import React from "react";
import { Flex, Image, Heading } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

function LastPage({ onBoardIconsURL }) {
	return (
		<Flex
			direction="column"
			justifyContent="space-between"
			alignItems="center"
		>
			<Image
				htmlWidth="80%"
				htmlHeight="auto"
				src={`${onBoardIconsURL}/onboard-end.png`}
				alt="Onboard Finish"
			/>
			<Heading
				as="h2"
				fontSize="22px"
				fontWeight="600"
				mb={4}
				mt={4}
				color="#222222"
			>
				{__("Congratulations, You’re all set! 🎉", "user-registration")}
			</Heading>
		</Flex>
	);
}

export default LastPage;
