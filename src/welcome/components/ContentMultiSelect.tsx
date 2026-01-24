import {
	Box,
	Checkbox,
	CheckboxGroup,
	HStack,
	Menu,
	MenuButton,
	MenuItem,
	MenuList,
	Tag,
	TagLabel,
	Text,
	useColorModeValue
} from "@chakra-ui/react";
import React from "react";

interface Option {
	value: number;
	label: string;
}

interface ContentMultiSelectProps {
	options: Option[];
	value: number[];
	onChange: (value: number[]) => void;
	placeholder?: string;
}

const ContentMultiSelect: React.FC<ContentMultiSelectProps> = ({
	options,
	value,
	onChange,
	placeholder = "Select items"
}) => {
	const borderColor = useColorModeValue("gray.200", "gray.600");
	const bg = useColorModeValue("white", "gray.800");
	const placeholderColor = useColorModeValue("gray.400", "gray.500");

	const selectedOptions = options.filter((opt) => value.includes(opt.value));

	const handleChange = (vals: (string | number)[]) => {
		const ids = vals.map((v) => Number(v));
		onChange(ids);
	};

	return (
		<Menu closeOnSelect={false}>
			<MenuButton
				as={Box}
				w="100%"
				minH="40px"
				borderWidth="1px"
				borderRadius="md"
				borderColor={borderColor}
				bg={bg}
				px={3}
				py={2}
				textAlign="left"
				cursor="pointer"
				_hover={{ borderColor: "gray.300" }}
			>
				{selectedOptions.length === 0 ? (
					<Text fontSize="sm" color={placeholderColor}>
						{placeholder}
					</Text>
				) : (
					<HStack spacing={2} wrap="wrap" align="center">
						{selectedOptions.map((opt) => (
							<Tag key={opt.value} size="sm" borderRadius="full">
								<TagLabel>{opt.label}</TagLabel>
							</Tag>
						))}
					</HStack>
				)}
			</MenuButton>
			<MenuList maxH="250px" overflowY="auto">
				<CheckboxGroup
					value={value.map(String)}
					onChange={(vals) => handleChange(vals)}
				>
					{options.map((opt) => (
						<MenuItem key={opt.value} closeOnSelect={false} px={3}>
							<Checkbox value={String(opt.value)}>
								{opt.label}
							</Checkbox>
						</MenuItem>
					))}
				</CheckboxGroup>
			</MenuList>
		</Menu>
	);
};

export default ContentMultiSelect;
