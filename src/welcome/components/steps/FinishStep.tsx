import { ArrowForwardIcon } from "@chakra-ui/icons";
import {
	Button,
	Flex,
	Heading,
	Skeleton,
	Text,
	useColorModeValue
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useEffect, useState } from "react";
import { apiGet, apiPost } from "../../api/gettingStartedApi";

interface FinishLinks {
	dashboard?: string;
}

const FinishStep: React.FC = () => {
	const [isLoadingData, setIsLoadingData] = useState(true);
	const [isSaving, setIsSaving] = useState(false);
	const [links, setLinks] = useState<FinishLinks>({});

	const textColor = useColorModeValue("gray.800", "white");
	const subtextColor = useColorModeValue("gray.600", "gray.300");

	useEffect(() => {
		const loadFinishData = async () => {
			try {
				setIsLoadingData(true);
				const response = await apiGet("/finish");
				if (response.links) setLinks(response.links);
			} catch (e) {
				console.error("Failed to load finish data:", e);
			} finally {
				setIsLoadingData(false);
			}
		};

		loadFinishData();
	}, []);

	const handleGoToDashboard = async () => {
		try {
			setIsSaving(true);
			await apiPost("/finish", {});
			if (links.dashboard) window.location.href = links.dashboard;
		} catch (e) {
			console.error("Failed to complete action:", e);
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<>
			<Heading
				fontFamily="Inter"
				fontWeight={600}
				fontSize="24px"
				lineHeight="32px"
				letterSpacing="-0.01em"
				color={textColor}
				mb={2}
			>
				{__("Congratulations! 🎉", "user-registration")}
			</Heading>

			<Heading
				fontFamily="Inter"
				fontWeight={500}
				fontSize="16px"
				lineHeight="24px"
				color={textColor}
				mb={4}
			>
				{__("Setup complete!", "user-registration")}
			</Heading>

			<Text fontSize="14px" lineHeight="22px" color={subtextColor} mb={8}>
				{__(
					"We have created all the pages you need and your site is ready to go. You can customize everything from the URM dashboard.",
					"user-registration"
				)}
			</Text>

			<Flex justify="flex-end" mt={8}>
				{isLoadingData ? (
					<Skeleton height="44px" width="180px" borderRadius="md" />
				) : (
					<Button
						bg="#475BB2"
						color="white"
						rightIcon={<ArrowForwardIcon />}
						_hover={{ bg: "#3a4bc2" }}
						_active={{ bg: "#2f3da6" }}
						px={6}
						h="44px"
						fontSize="14px"
						fontWeight={500}
						borderRadius="md"
						minW="180px"
						onClick={handleGoToDashboard}
						isLoading={isSaving}
						isDisabled={!links.dashboard}
					>
						{__("Go to Dashboard", "user-registration")}
					</Button>
				)}
			</Flex>
		</>
	);
};

export default FinishStep;
