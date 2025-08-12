import { __ } from "@wordpress/i18n";
import React, { useState, useMemo } from "react";
import Main from "./components/Main";

import {
	ChakraProvider,
	Box,
	HStack,
	Text,
	Tabs,
	TabList,
	Tab,
	Button,
	Icon,
	Divider,
	VStack,
	Heading
} from "@chakra-ui/react";

const TabFilters = ({ onTabChange }) => {
	const filters = useMemo(
		() => [
			__("All", "user-registration"),
			__("Free", "user-registration"),
			__("Premium", "user-registration")
		],
		[]
	);

	return (
		<Tabs variant="unstyled" ml="auto" onChange={onTabChange}>
			<TabList gap="8px">
				{filters.map((label) => (
					<Tab
						key={label}
						_selected={{
							color: "#475bb2",
							borderBottom: "2px solid",
							borderColor: "#475bb2"
						}}
						color="#383838"
						fontWeight="500"
						fontSize="16px"
						lineHeight="20px"
						px={{ base: 1, md: 2 }} // Add horizontal padding to tabs
						boxShadow="none !important"
					>
						{label}
					</Tab>
				))}
			</TabList>
		</Tabs>
	);
};

const App = () => {
	const [selectedTab, setSelectedTab] = useState(
		__("All", "user-registration")
	);

	// Handle tab changes
	const handleTabChange = (index) => {
		const filters = [
			__("All", "user-registration"),
			__("Free", "user-registration"),
			__("Premium", "user-registration")
		];
		setSelectedTab(filters[index]);
	};

	// Handle refresh button click
	const handleRefreshTemplates = () => {
		const url = new URL(window.location.href);
		url.searchParams.set("refresh", Date.now().toString());
		window.location.href = url.toString();
	};
	return (
		<ChakraProvider>
			<Box
				border={"1px solid #EDF2F7"}
				borderRadius={"4px"}
				backgroundColor="white"
			>
				<Box
					padding="0px 20px 50px"
					gap="24px"
					display="flex"
					flexDirection="column"
				>
					<HStack
						align="center"
						bg="white"
						direction={{ base: "column", md: "row" }}
						borderBottom="1px solid #CDD0D8"
						padding="20px 10px"
						boxShadow="none"
					>
						<HStack gap="24px">
							<HStack gap="16px">
								<Text
									fontSize={{
										base: "12px",
										md: "14px",
										lg: "16px"
									}}
									fontWeight="500"
									textAlign={{ base: "center", md: "left" }}
									color="#383838"
								>
									{__("Add New Form", "user-registration")}
								</Text>
							</HStack>
							<Button
								style={{
									borderColor: "#475bb2",
									color: "#475bb2"
								}}
								variant="outline"
								onClick={handleRefreshTemplates}
								width={{ base: "full", md: "auto" }}
								className="chakra-link "
								display={{ base: "none", md: "inline-flex" }} // Hide button on small screens
								padding="8px 12px"
								gap="4px"
								fontWeight="500"
								fontSize="16px"
								_hover={{
									backgroundColor: "#475BB2",
									color: "white !important"
								}}
							>
								{__("Refresh Templates", "user-registration")}
							</Button>
						</HStack>
						<TabFilters onTabChange={handleTabChange} />
					</HStack>
					{/* Main Content Area */}
					<Box bg="white" p="0px 8px" rounded="md" gap="24px">
						<VStack align="start" gap="8px">
							<Heading
								as="h2"
								fontSize="20px"
								lineHeight="28px"
								m={0}
								color="#26262E"
								fontWeight="500"
								letterSpacing="0.5px"
							>
								{__("Select a Template", "user-registration")}
							</Heading>
							<Text
								fontSize="14px"
								lineHeight="25px"
								fontWeight="400"
								color="#4D4D4D"
								m={0}
							>
								{__(
									"To get started quickly, you can pick from our ready-made templates, begin with a blank form, or design your own.",
									"user-registration"
								)}
							</Text>
						</VStack>
					</Box>
					<Box bg="white" rounded="md" gap="24px" boxShadow="none">
						<Main filter={selectedTab} />
					</Box>
				</Box>
			</Box>
		</ChakraProvider>
	);
};

export default App;
