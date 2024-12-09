import { __ } from '@wordpress/i18n';
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
  Heading,
} from "@chakra-ui/react";

const URIcon = (props) => (
  <Icon viewBox="0 0 36 36" {...props}>
    <path
			fill="#475BB2"
			fillRule="evenodd"
			d="M32.8951 2.53619C30.4998 3.94519 28.2454 5.77691 26.2728 7.74951C24.3002 9.72212 22.4685 11.9765 21.0595 14.2309C22.0458 16.2035 22.8912 18.1761 23.5957 20.2896C24.8638 17.6125 26.4137 15.2172 28.3863 13.1037V20.7123C28.3863 25.362 25.2865 29.4481 20.7777 30.7162H20.4959C20.2141 27.0528 19.3687 23.5303 18.1006 20.1487C16.2689 15.499 13.4508 11.272 9.78744 7.74951C7.81484 5.77691 5.56046 4.08609 3.16515 2.53619L2.60156 2.25437V20.5714C2.60156 28.0391 7.95574 34.5205 15.4234 35.7887H15.5644C17.2552 36.0704 18.946 36.0704 20.4959 35.7887H20.6368C28.1045 34.5205 33.4587 28.0391 33.4587 20.5714V2.25437L32.8951 2.53619ZM7.67394 20.5714V13.1037C12.0418 18.0352 14.719 24.0939 15.2826 30.5753C10.7738 29.3072 7.67394 25.2211 7.67394 20.5714ZM17.9597 0C15.8462 0 14.1554 1.69078 14.1554 3.80428C14.1554 5.91778 15.8462 7.6086 17.9597 7.6086C20.0732 7.6086 21.764 5.91778 21.764 3.80428C21.764 1.69078 20.0732 0 17.9597 0Z"
			clipRule="evenodd"
		/>
  </Icon>
);

const TabFilters = ({ onTabChange }) => {
  const filters = useMemo(() => [__("All", "user-registration"), __("Free", "user-registration"), __("Premium", "user-registration")], []);

  return (
    <Tabs variant="unstyled" ml="auto" onChange={onTabChange}>
      <TabList>
        {filters.map((label) => (
          <Tab
            key={label}
            _selected={{
              color: "purple.500",
              fontWeight: "bold",
              borderBottom: "2px solid",
              borderColor: "purple.500",
            }}
            fontSize={{ base: "sm", md: "md", lg: "lg" }}
            px={{ base: 1, md: 2 }} // Add horizontal padding to tabs
          >
            {label}
          </Tab>
        ))}
      </TabList>
    </Tabs>
  );
};

const App = () => {
  const [selectedTab, setSelectedTab] = useState(__("All", "user-registration"));

  // Handle tab changes
  const handleTabChange = (index) => {
    const filters = [__("All", "user-registration"), __("Free", "user-registration"), __("Premium", "user-registration")];
    setSelectedTab(filters[index]);
  };

  // Handle refresh button click
  const handleRefreshTemplates = () => {
    const url = new URL(window.location.href);
    url.searchParams.set('refresh', Date.now().toString());
    window.location.href = url.toString();
  };
	  return (
	  <ChakraProvider>
      <Box margin={{ base: 4, md: 6, lg: 10 }} boxShadow="md">
        <HStack
          spacing={{ base: 4, md: 6 }} // Adjust spacing
          align="center"
          mb={5}
          bg="white"
          p={{ base: 3, md: 4 }}
          boxShadow="sm"
          direction={{ base: "column", md: "row" }}
        >
          <URIcon boxSize={{ base: 5, md: 6, lg: 7 }} />
          <Divider orientation="vertical" height={{ base: "16px", md: "24px", lg: "32px" }} />
          <Text fontSize={{ base: "md", md: "lg", lg: "xl" }} fontWeight="bold" textAlign={{ base: "center", md: "left" }}>
            {__("Add New Form", "user-registration")}
          </Text>
          <Button
            colorScheme="purple"
            variant="outline"
            onClick={handleRefreshTemplates}
            size={{ base: "sm", md: "md", lg: "lg" }}
            width={{ base: "full", md: "auto" }}
            display={{ base: "none", md: "inline-flex" }} // Hide button on small screens
          >
            {__("Refresh Templates", "user-registration")}
          </Button>
          <TabFilters onTabChange={handleTabChange} />
        </HStack>

        {/* Main Content Area */}
        <Box bg="white" p={{ base: 3, md: 5, lg: 6 }} rounded="md" boxShadow="sm">
          <VStack align="start" spacing={4}>
            <Heading as="h1" size={{ base: "md", md: "lg", lg: "xl" }} m={0}>
              {__("Select a Template", "user-registration")}
            </Heading>
            <Text fontSize={{ base: "sm", md: "md", lg: "lg" }} color="gray.600">
              {__(
                "To get started quickly, you can pick from our ready-made templates, begin with a blank form, or design your own.",
                "user-registration"
              )}
            </Text>
          </VStack>
		  <Main filter={selectedTab} />
        </Box>
      </Box>
    </ChakraProvider>
  );
};

export default App;
