import React from "react";
import {
	Heading,
	HStack,
	Button,
	Link,
	Flex,
	useColorModeValue,
} from "@chakra-ui/react";
import { ArrowBackIcon, ArrowForwardIcon } from "@chakra-ui/icons";

const FinishStep: React.FC = () => {
	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");

	return (
		<>
			<Heading size="lg" color={textColor} mb={10}>
				Success! You're all set!
			</Heading>

			<Flex justify="space-between" align="center">
				<Link
					href="#"
					display="flex"
					alignItems="center"
					color={mutedColor}
					fontSize="sm"
					_hover={{ color: textColor }}
				>
					<ArrowBackIcon mr={2} />
					View Registration Page
				</Link>

				<Button
					bg="#475BD8"
					color="white"
					rightIcon={<ArrowForwardIcon />}
					_hover={{ bg: "#3a4bc2" }}
					_active={{ bg: "#2f3da6" }}
					px={6}
				>
					Visit Dashboard
				</Button>
			</Flex>
		</>
	);
};

export default FinishStep;
