import React, { useState, useCallback, useMemo } from "react";
import { Box, Flex, Spinner, useBreakpointValue } from "@chakra-ui/react";
import Sidebar from "./Sidebar";
import TemplateList from "./TemplateList";
import { useQuery } from "@tanstack/react-query";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

const { restURL, security } = ur_templates_script;

const fetchTemplates = async () => {
	const response = await apiFetch({
		path: `${restURL}user-registration/v1/form-templates`,
		method: "GET",
		headers: {
			"X-WP-Nonce": security
		}
	});
	if (response && Array.isArray(response.templates)) {
		const allTemplates = response.templates.flatMap(
			(category) => category.templates
		);
		return allTemplates;
	} else {
		throw new Error(__("Unexpected response format.", "user-registration"));
	}
};

const Main = ({ filter }) => {
	const [state, setState] = useState({
		selectedCategory: __("All Forms", "user-registration"),
		searchTerm: ""
	});

	const { selectedCategory, searchTerm } = state;

	const {
		data: templates = [],
		isLoading,
		error
	} = useQuery(["templates"], fetchTemplates);

	const categories = useMemo(() => {
		const categoriesSet = new Set();
		templates.forEach((template) => {
			template.categories.forEach((category) =>
				categoriesSet.add(category)
			);
		});

		return [
			{
				name: __("All Forms", "user-registration"),
				count: templates.length
			},
			...Array.from(categoriesSet).map((category) => ({
				name: category,
				count: templates.filter((template) =>
					template.categories.includes(category)
				).length
			}))
		];
	}, [templates]);

	const filteredTemplates = useMemo(() => {
		return templates.filter(
			(template) =>
				(selectedCategory === __("All Forms", "user-registration") ||
					template.categories.includes(selectedCategory)) &&
				template.title
					.toLowerCase()
					.includes(searchTerm.toLowerCase()) &&
				(filter === "All" ||
					(filter === "Free" && !template.isPro) ||
					(filter === "Premium" && template.isPro))
		);
	}, [selectedCategory, searchTerm, templates, filter]);

	const handleCategorySelect = useCallback((category) => {
		setState((prevState) => ({ ...prevState, selectedCategory: category }));
	}, []);

	const handleSearchChange = useCallback((searchTerm) => {
		setState((prevState) => ({ ...prevState, searchTerm }));
	}, []);

	const sidebarWidth = useBreakpointValue({ base: "100%", md: "250px" });

	if (isLoading)
		return (
			<Flex justify="center" align="center" height="100vh">
				<Spinner size="xl" />
			</Flex>
		);
	if (error) return <div>{error.message}</div>;

	return (
		<Box>
			<Flex direction={{ base: "column", md: "row" }}>
				<Box
					width={sidebarWidth}
					mr={{ base: 0, md: 4 }}
					mb={{ base: 4, md: 0 }}
				>
					<Sidebar
						categories={categories}
						selectedCategory={state.selectedCategory}
						onCategorySelect={handleCategorySelect}
						onSearchChange={handleSearchChange}
					/>
				</Box>
				<Box
					width="1px"
					bg="linear-gradient(90deg, #CDD0D8 0%, rgba(255, 255, 255, 0) 158.04%)"
					mx="4"
					marginRight="28px"
				/>
				<Box flex={1}>
					<TemplateList
						selectedCategory={selectedCategory}
						templates={filteredTemplates}
					/>
				</Box>
			</Flex>
		</Box>
	);
};

export default Main;
