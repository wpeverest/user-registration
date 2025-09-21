/**
 * External Dependencies
 */
import React from "react";
import {
	Box,
	Stack,
	HStack,
	InputGroup,
	InputLeftElement,
	Input,
	IconButton,
	Tooltip
} from "@chakra-ui/react";
import { FaUndo } from "react-icons/fa";
import { Select } from "chakra-react-select";
import { Search } from "../../../components/Icon/Icon";

const Filters = ({
	sortOptions,
	statusOptions,
	planOptions,
	selectedSortValue,
	selectedStatusValue,
	selectedPlanValue,
	onSortChange,
	onStatusChange,
	onPlanChange,
	searchValue,
	onSearchChange,
	onReset
}) => {

	return (
		<Stack direction="row" justify="space-between" align="center" mb="4">
			<HStack spacing="3">
				<Select
					instanceId="plan-select"
					options={planOptions}
					value={selectedPlanValue}
					placeholder="All Plans"
					isSearchable={false}
					isClearable={false}
					onChange={onPlanChange}
					menuPortalTarget={document.body}
					menuPosition="fixed"
					menuShouldBlockScroll={false}
					onMenuOpen={() => {}}
					onMenuClose={() => {}}
					chakraStyles={{
						control: (base) => ({
							...base,
							backgroundColor: "white",
							borderColor: "#DFDFE0",
							width: "140px",
							minHeight: "36px",
							cursor: "pointer",
							borderRadius: "6px",
							fontSize: "14px",
							pointerEvents: "auto"
						}),
						valueContainer: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						input: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						placeholder: (base) => ({
							...base,
							color: "#9CA3AF",
							fontSize: "14px"
						}),
						singleValue: (base) => ({
							...base,
							color: "#374151",
							fontSize: "14px"
						}),
						indicatorSeparator: () => ({
							display: "none"
						}),
						dropdownIndicator: (base) => ({
							...base,
							color: "#6B7280",
							backgroundColor: "white",
							width: "20px",
							height: "20px",
							cursor: "pointer"
						})
					}}
				/>
				<Select
					instanceId="sort-select"
					options={sortOptions}
					value={selectedSortValue}
					placeholder="All"
					isSearchable={false}
					isClearable={false}
					onChange={onSortChange}
					menuPortalTarget={document.body}
					menuPosition="fixed"
					menuShouldBlockScroll={false}
					onMenuOpen={() => {}}
					onMenuClose={() => {}}
					chakraStyles={{
						control: (base) => ({
							...base,
							backgroundColor: "white",
							borderColor: "#DFDFE0",
							width: "140px",
							minHeight: "36px",
							cursor: "pointer",
							borderRadius: "6px",
							fontSize: "14px",
							pointerEvents: "auto"
						}),
						valueContainer: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						input: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						placeholder: (base) => ({
							...base,
							color: "#9CA3AF",
							fontSize: "14px"
						}),
						singleValue: (base) => ({
							...base,
							color: "#374151",
							fontSize: "14px"
						}),
						indicatorSeparator: () => ({
							display: "none"
						}),
						dropdownIndicator: (base) => ({
							...base,
							color: "#6B7280",
							backgroundColor: "white",
							width: "20px",
							height: "20px",
							cursor: "pointer"
						})
					}}
				/>
				<Select
					instanceId="status-select"
					options={statusOptions}
					value={selectedStatusValue}
					placeholder="All Status"
					isSearchable={false}
					isClearable={false}
					onChange={onStatusChange}
					menuPortalTarget={document.body}
					menuPosition="fixed"
					menuShouldBlockScroll={false}
					onMenuOpen={() => {}}
					onMenuClose={() => {}}
					chakraStyles={{
						control: (base) => ({
							...base,
							backgroundColor: "white",
							borderColor: "#DFDFE0",
							width: "140px",
							minHeight: "36px",
							cursor: "pointer",
							borderRadius: "6px",
							fontSize: "14px",
							pointerEvents: "auto"
						}),
						valueContainer: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						input: (base) => ({
							...base,
							cursor: "pointer",
							pointerEvents: "auto"
						}),
						placeholder: (base) => ({
							...base,
							color: "#9CA3AF",
							fontSize: "14px"
						}),
						singleValue: (base) => ({
							...base,
							color: "#374151",
							fontSize: "14px"
						}),
						indicatorSeparator: () => ({
							display: "none"
						}),
						dropdownIndicator: (base) => ({
							...base,
							color: "#6B7280",
							backgroundColor: "white",
							width: "20px",
							height: "20px",
							cursor: "pointer"
						})
					}}
				/>

			</HStack>
			<HStack spacing="3">
				<Tooltip label="Reset all filters" placement="top">
					<IconButton
						aria-label="Reset filters"
						icon={<FaUndo />}
						size="sm"
						variant="outline"
						bg="white"
						borderColor="#DFDFE0"
						color="gray.600"
						borderRadius="6px"
						height="36px"
						width="36px"
						_hover={{
							bg: "gray.50",
							borderColor: "#9CA3AF",
							color: "gray.700"
						}}
						_focus={{
							boxShadow: "0 0 0 1px #DFDFE0"
						}}
						onClick={onReset}
					/>
				</Tooltip>
				<InputGroup maxW="300px">
					<InputLeftElement pointerEvents="none">
						<Search h="4" w="4" color="gray.400" />
					</InputLeftElement>
					<Input
						key="search-input"
						placeholder="Search (min 3 characters)..."
						value={searchValue}
						onChange={onSearchChange}
						bg="white"
						borderColor="#DFDFE0"
						borderRadius="6px"
						height="36px"
						fontSize="14px"
					/>
				</InputGroup>
			</HStack>
		</Stack>
	);
};

export default Filters;
