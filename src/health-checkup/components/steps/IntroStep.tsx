import { Box, Button } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import Text from "../Text";

const IntroStep = ({ onStart }: { onStart: () => void }) => (
	<Box>
		<Text
			fontSize="11.5px"
			fontWeight="700"
			letterSpacing="0.06em"
			textTransform="uppercase"
			color="primary.500"
			mb="9px"
		>
			{__("Takes about 2 minutes", "user-registration")}
		</Text>
		<Text as="h2" fontSize="25px" fontWeight="700" mb="10px" letterSpacing="-0.015em">
			{__("Quick email delivery checkup", "user-registration")}
		</Text>
		<Text fontSize="14.5px" lineHeight="1.62" color="gray.600" mb="22px" maxW="60ch">
			{__(
				"We'll scan your current settings for common issues, then ask you to confirm one thing we can't check on our own — whether the test email actually arrives.",
				"user-registration"
			)}
		</Text>
		<Button
			width="100%"
			size="lg"
			colorScheme="primary"
			fontSize="14.5px"
			fontWeight="600"
			onClick={onStart}
		>
			{__("Scan my settings", "user-registration")}
		</Button>
	</Box>
);

export default IntroStep;
