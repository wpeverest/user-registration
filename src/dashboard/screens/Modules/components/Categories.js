/**
 * External Dependencies
 */
import React from "react";
import {
	Box,
	HStack,
	Text
} from "@chakra-ui/react";

const Categories = ({
	categories,
	selectedCategory,
	highlightedCategories = [],
	onCategoryChange
}) => {
	return (
		<Box p="4" textAlign="center">
			<Box 
				display="inline-block" 
				borderBottom="1px solid" 
				borderColor="gray.200"
				pb="4"
			>
				<HStack spacing="8" align="center" justifyContent="center">
					{categories.map((category, index) => {
						// Determine if this category should be highlighted
						const isSelected = selectedCategory === category.value;
						const isHighlighted = highlightedCategories.length > 0 && highlightedCategories.includes(category.internalValue);
						
						// When searching (highlightedCategories has items), only highlight categories with results
						// When not searching, highlight the selected category
						const shouldHighlight = highlightedCategories.length > 0 ? isHighlighted : isSelected;
						
						return (
							<Box
								key={`${category.value}-${category.internalValue}-${index}`}
								position="relative"
								cursor="pointer"
								onClick={() => onCategoryChange(category.value, category.internalValue)}
								_hover={{
									opacity: 0.8
								}}
							>
								<Text
									fontSize="sm"
									fontWeight="600"
									color={shouldHighlight ? "#4263EB" : "#4A5568"}
									transition="color 0.2s"
									sx={{
										color: shouldHighlight ? "#4263EB !important" : "#4A5568 !important",
										fontWeight: shouldHighlight ? "600 !important" : "500 !important"
									}}
								>
									{category.label}
								</Text>
								{shouldHighlight && (
									<Box
										position="absolute"
										bottom="-17px"
										left="0"
										right="0"
										height="2px"
										bg="#4263EB"
										borderRadius="1px"
									/>
								)}
							</Box>
						);
					})}
				</HStack>
			</Box>
		</Box>
	);
};

export default Categories;
