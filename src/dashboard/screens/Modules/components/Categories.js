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
	onCategoryChange
}) => {
	return (
		<Box  p="4">
			<HStack spacing="8" align="center" justifyContent="center">
				{categories.map((category, index) => (
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
							fontWeight="500"
							color={selectedCategory === category.value ? "#4263EB" : "#4A5568"}
							transition="color 0.2s"
						>
							{category.label}
						</Text>
						{selectedCategory === category.value && (
							<Box
								position="absolute"
								bottom="-4px"
								left="0"
								right="0"
								height="2px"
								bg="#4263EB"
								borderRadius="1px"
							/>
						)}
					</Box>
				))}
			</HStack>
			<Box
				mt="4"
				height="1px"
				bg="gray.200"
				width="100%"
			/>
		</Box>
	);
};

export default Categories;
