import React, { useState, useCallback } from "react";
import {
	Box,
	VStack,
	HStack,
	Text,
	Spacer,
	Input,
	InputLeftElement,
	InputGroup,
	Badge,
	CardHeader,
	CardFooter,
	Button,
	Card,
	Heading
} from "@chakra-ui/react";
import { IoSearchOutline } from "react-icons/io5";
import debounce from "lodash.debounce";
import { __ } from "@wordpress/i18n";

const Sidebar = React.memo(
	({ categories, selectedCategory, onCategorySelect, onSearchChange }) => {
		const [searchTerm, setSearchTerm] = useState("");

		const debouncedSearchChange = useCallback(
			debounce((value) => {
				onSearchChange(value);
			}, 300),
			[onSearchChange]
		);

		const handleSearchChange = (e) => {
			const value = e.target.value;
			setSearchTerm(value);
			debouncedSearchChange(value);
		};

		const favorites = categories.find((cat) => cat.name === "Favorites");

		const orderedCategories =
			favorites && favorites.count > 0
				? [
						favorites,
						...categories.filter((cat) => cat.name !== "Favorites")
				  ]
				: categories;

		return (
			<Box>
				<InputGroup mb="26px">
					<InputLeftElement
						pointerEvents="none"
						padding="0px 0px 0px 8px"
					>
						<IoSearchOutline
							style={{ width: "18px", height: "18px" }}
							color="#737373"
						/>
					</InputLeftElement>
					<Input
						placeholder={__(
							"Search Templates",
							"user-registration"
						)}
						value={searchTerm}
						onChange={handleSearchChange}
						minHeight={"38px"}
						border={"1px solid #e1e1e1"}
						fontSize="14px"
						fontWeight="400"
						lineHeight="24px"
						color="#383838"
						borderRadius="4px"
						_focus={{
							borderColor: "#475bb2",
							outline: "none",
							boxShadow: "none"
						}}
						_placeholder={{
							color: "#737373"
						}}
					/>
				</InputGroup>

				<VStack align="stretch" gap="2px">
					{orderedCategories.map((category) => (
						<HStack
							key={category.name}
							p="12px"
							_hover={{
								bg: "#EDEFF7",
								color: "#475bb2",
								"& > .badge": {
									bg:
										selectedCategory === category.name
											? "#FFFFFF"
											: "#FFFFFF"
								}
							}}
							borderRadius="md"
							cursor="pointer"
							bg={
								selectedCategory === category.name
									? "#EDEFF7"
									: "transparent"
							}
							onClick={() => onCategorySelect(category.name)}
							justifyContent="space-between"
						>
							<Text
								color={
									selectedCategory === category.name
										? "#475bb2"
										: ""
								}
								fontWeight="500"
								margin="0px"
								fontSize="16px"
								lineHeight="20px"
								alignItems="center"
							>
								{category.name}
							</Text>
							<Badge
								className="badge"
								display="flex"
								alignItems="center"
								justifyContent="center"
								width="32px"
								height="32px"
								padding="6px"
								borderRadius="8px"
								gap="10px"
								color={
									selectedCategory === category.name
										? "#475bb2"
										: ""
								}
								bg={
									selectedCategory === category.name
										? "white"
										: "#F2F2F2"
								}
							>
								{category.count}
							</Badge>
						</HStack>
					))}
					<Card
						align="center"
						background="linear-gradient(70deg, #475bb2, #94A7E7B2)"
						padding="40px 24px"
						marginTop="26px"
					>
						<CardHeader padding="0px">
							<Heading
								fontSize="18px"
								color="#ffffff"
								lineHeight="28px"
								padding="0px 20px"
								textAlign="center"
							>
								{__(
									"Can't Find The Form Template You Need?",
									"user-registration"
								)}
							</Heading>
						</CardHeader>
						<CardFooter padding="0" width="100%">
							<a
								href="https://wpuserregistration.com/request-template"
								target="_blank"
								rel="noopener noreferrer"
								style={{ width: "inherit" }}
							>
								<Button
									backgroundColor="#FFFFFF"
									color="#475bb2"
									padding="12px 10px"
									borderRadius="4px"
									width="inherit"
									fontWeight="500"
									_hover={{
										backgroundColor: "#475BB2",
										color: "white !important"
									}}
								>
									{__(
										"Request Template",
										"user-registration"
									)}
								</Button>
							</a>
						</CardFooter>
					</Card>
				</VStack>
			</Box>
		);
	}
);

export default Sidebar;
