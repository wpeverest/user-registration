import { Box, Button, Flex } from "@chakra-ui/react";
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
		<Text as="h2" fontSize="21px" fontWeight="600" mb="10px" letterSpacing="-0.01em" color="gray.800">
			{__("Quick email delivery checkup", "user-registration")}
		</Text>
		<Text fontSize="14px" lineHeight="1.62" color="gray.600" mb="22px">
			{__(
				"We'll check your settings, send a real test email, then ask whether it actually arrived — the one thing no automated check can confirm on its own.",
				"user-registration"
			)}
		</Text>
		<Flex justify="flex-end">
			<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onStart}>
				{__("Scan Email Settings", "user-registration")}
			</Button>
		</Flex>
	</Box>
);

export default IntroStep;
