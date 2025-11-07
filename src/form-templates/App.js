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
				border={"1px solid #e1e1e1"}
				borderRadius={"13px"}
				backgroundColor="white"
				padding="24px 28px"
			>
				<Box gap="24px" display="flex" flexDirection="column">
					<HStack
						align="center"
						bg="white"
						direction={{ base: "column", md: "row" }}
						borderBottom="1px solid #e1e1e1"
						padding="0 0 10px"
						boxShadow="none"
					>
						<HStack gap="24px">
							<HStack gap="16px">
								<Heading
									textAlign={{ base: "center", md: "left" }}
									lineHeight="30px"
									fontWeight="600"
									margin="0"
									fontSize={{
										base: "16px",
										md: "18px",
										lg: "20px"
									}}
									color="#222222"
								>
									{__("Add New Form", "user-registration")}
								</Heading>
							</HStack>
							<Button
								style={{
									border: "1px solid #475bb2",
									borderRadius: "4px",
									transition: "all .3s ease-in-out"
								}}
								color="#475bb2"
								backgroundColor="#f6f7f7"
								fontWeight="500"
								height="34px"
								padding="0 12px"
								alignContent="center"
								fontSize="14px"
								lineHeight="20px"
								variant="outline"
								onClick={handleRefreshTemplates}
								textDecoration={"none"}
								boxSizing="border-box"
								width={{ base: "full", md: "auto" }}
								className="chakra-link "
								display={{ base: "none", md: "inline-flex" }} // Hide button on small screens
								gap="4px"
								_hover={{
									backgroundColor: "#475BB2",
									color: "white"
								}}
							>
								{__("Refresh Templates", "user-registration")}
							</Button>
						</HStack>
						<TabFilters onTabChange={handleTabChange} />
					</HStack>
					{/* Main Content Area */}
					<Box bg="white" p="0" rounded="md" gap="24px">
						<VStack align="start" gap="4px">
							<Heading
								as="h2"
								fontSize="16px"
								lineHeight="26px"
								m={0}
								color="#26262E"
								fontWeight="500"
								fontFamily={"inherit"}
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
