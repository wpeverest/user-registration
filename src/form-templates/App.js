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

const URIcon = (props) => (
	<Icon viewBox="0 0 32 32" width="26px" height="26px">
		<path
			fill="#475BB2"
			fillRule="evenodd"
			d="M29.2401 2.25439C27.1109 3.50683 25.107 5.13503 23.3536 6.88846C21.6002 8.64188 19.972 10.6458 18.7195 12.6497C19.5962 14.4031 20.3477 16.1566 20.9739 18.0352C22.1011 15.6556 23.4788 13.5264 25.2323 11.6477V18.4109C25.2323 22.544 22.4769 26.1761 18.4691 27.3033H18.2185C17.9681 24.047 17.2166 20.9158 16.0894 17.91C14.4612 13.7769 11.9563 10.0196 8.69995 6.88846C6.94652 5.13503 4.94263 3.63208 2.81347 2.25439L2.3125 2.00388V18.2857C2.3125 24.9237 7.07177 30.6849 13.7097 31.8121H13.835C15.3379 32.0626 16.8409 32.0626 18.2185 31.8121H18.3438C24.9818 30.6849 29.7411 24.9237 29.7411 18.2857V2.00388L29.2401 2.25439ZM6.82128 18.2857V11.6477C10.7039 16.0313 13.0835 21.4168 13.5845 27.1781C9.57669 26.0509 6.82128 22.4188 6.82128 18.2857ZM15.9642 0C14.0855 0 12.5825 1.50291 12.5825 3.38158C12.5825 5.26025 14.0855 6.7632 15.9642 6.7632C17.8428 6.7632 19.3457 5.26025 19.3457 3.38158C19.3457 1.50291 17.8428 0 15.9642 0Z"
			clipRule="evenodd"
		/>
	</Icon>
);

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
								<URIcon boxSize={{ base: 5, md: 6, lg: 7 }} />
								<Divider
									orientation="vertical"
									height={{
										base: "16px",
										md: "24px",
										lg: "32px"
									}}
									border="1px solid #CDDOD8"
								/>
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
