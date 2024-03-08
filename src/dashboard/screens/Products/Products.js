/**
 *  External Dependencies
 */
import { Box, Heading, SimpleGrid, Stack } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React from "react";

/**
 *  Internal Dependencies
 */
import { PLUGINS, THEMES } from "../../Constants/Products";
import ProductCard from "./components/ProductCard";
import { useStateValue } from "../../../context/StateProvider";

const Products = () => {
	const [{ pluginsStatus, themesStatus }, dispatch] = useStateValue();
	return (
		<Stack my="8" mx="6">
			<Box>
				<Heading size="md" fontSize="xl" fontWeight="semibold" mb="8">
					{__("Plugins", "user-registration")}
				</Heading>
				<SimpleGrid
					columns={{ base: 1, md: 2, lg: 3, xl: 4 }}
					spacing="5"
				>
					{PLUGINS.map((plugin) => (
						<ProductCard
							key={plugin.slug}
							{...plugin}
							pluginsStatus={pluginsStatus}
							themesStatus={themesStatus}
						/>
					))}
				</SimpleGrid>
			</Box>
			<Box>
				<Heading size="md" fontSize="xl" fontWeight="semibold" my="8">
					{__("Themes", "user-registration")}
				</Heading>
				<SimpleGrid
					columns={{ base: 1, md: 2, lg: 3, xl: 4 }}
					spacing="5"
				>
					{THEMES.map((theme) => (
						<ProductCard
							key={theme.slug}
							{...theme}
							pluginsStatus={pluginsStatus}
							themesStatus={themesStatus}
						/>
					))}
				</SimpleGrid>
			</Box>
		</Stack>
	);
};

export default Products;
