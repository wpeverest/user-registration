/**
 * External Dependencies
 */
import React from "react";
import {
	Box,
	HStack,
	Heading,
	Text,
	SimpleGrid,
	Divider
} from "@chakra-ui/react";
import AddonCard from "./AddonCard";

const CardsGrid = ({
	modules,
	selectedCategory,
	showToast
}) => {
	// Group modules by category for "All" view
	const getModulesByCategory = () => {
		const modulesByCategory = modules.reduce((acc, module) => {
			const category = module.category || 'Uncategorized';
			if (!acc[category]) {
				acc[category] = [];
			}
			acc[category].push(module);
			return acc;
		}, {});

		// Map category names to display names
		const categoryDisplayNames = {};

		return Object.entries(modulesByCategory).map(([category, categoryModules]) => ({
			category,
			displayName: categoryDisplayNames[category] || category,
			modules: categoryModules
		}));
	};

	if (selectedCategory === "All") {
		const categoriesData = getModulesByCategory();

		return (
			<Box>
				{categoriesData.map(({ category, displayName, modules: categoryModules }) => (
					<Box key={category} mb="8" background={"white"} p={10} borderRadius={12}>
						<HStack justify="space-between" mb="4">
							<Heading size="md" color="gray.800">
								{displayName}
							</Heading>
							<Text fontSize="sm" color="gray.500">
								{categoryModules.length} {categoryModules.length === 1 ? 'Item' : 'Items'}
							</Text>
						</HStack>
						<Divider mb="6" borderColor="gray.200" />

						<SimpleGrid columns={{ base: 1, md: 2, lg: 3 }} spacing="6">
							{categoryModules.map((addon) => (
								<AddonCard
									key={addon.slug}
									addon={addon}
									showToast={showToast}
								/>
							))}
						</SimpleGrid>
					</Box>
				))}
			</Box>
		);
	}

	// Single category view - show with same styling as "All" view
	const categoryName = modules.length > 0 ? (modules[0].category || 'Uncategorized') : 'Uncategorized';

	return (
		<Box>
			<Box mb="8" background={"white"} p={10} borderRadius={12}>
				<HStack justify="space-between" mb="4">
					<Heading size="md" color="gray.800">
						{categoryName}
					</Heading>
					<Text fontSize="sm" color="gray.500">
						{modules.length} {modules.length === 1 ? 'Item' : 'Items'}
					</Text>
				</HStack>
				<Divider mb="6" borderColor="gray.200" />

				<SimpleGrid columns={{ base: 1, md: 2, lg: 3 }} spacing="6">
					{modules.map((addon) => (
						<AddonCard
							key={addon.slug}
							addon={addon}
							showToast={showToast}
						/>
					))}
				</SimpleGrid>
			</Box>
		</Box>
	);
};

export default CardsGrid;
