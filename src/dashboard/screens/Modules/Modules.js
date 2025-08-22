/**
 *  External Dependencies
 */
import React, { useState, useEffect, useCallback, useRef } from "react";
import {
	Box,
	Container,
	Stack,
	Tabs,
	Tab,
	TabList,
	TabPanels,
	TabPanel,
	Button,
	InputGroup,
	InputLeftElement,
	Input,
	FormControl,
	useToast,
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { debounce } from "lodash";
import { Select } from "chakra-react-select";

import { Search, PageNotFound } from "../../components/Icon/Icon";
import {
	getAllModules,
	bulkActivateModules,
	bulkDeactivateModules
} from "./components/modules-api";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/dashboardContext";
import ModuleBody from "./components/ModuleBody";

const Modules = () => {
	const toast = useToast();
	const [
		{ allModules, isMembershipActivated, isPaymentAddonActivated },
		dispatch
	] = useStateValue();
	const [state, setState] = useState({
		modules: [],
		originalModules: [],
		modulesLoaded: false,
		selectedModuleData: [],
		bulkAction: "",
		isPerformingBulkAction: false,
		searchItem: "",
		noItemFound: false,
		isSearching: false,
		error: null
	});
	const [tabIndex, setTabIndex] = useState(0);
	const [IsStateUpdated, setIsStateUpdated] = useState(false);
	const searchItemRef = useRef(state.searchItem);
	const isFirstRender = useRef(true);
	const resetIsStateUpdated = () => {
		setIsStateUpdated(false);
	};

	const fetchModules = useCallback(
		(for_payments = false) => {
			getAllModules()
				.then((data) => {
					if (data.success) {
						dispatch({
							type: actionTypes.GET_ALL_MODULES,
							allModules: data.modules_lists
						});
						setState((prev) => ({
							...prev,
							originalModules: data.modules_lists,
							modulesLoaded: true
						}));
						filterModules(data.modules_lists, tabIndex);
						if (for_payments) {
							setIsStateUpdated(true);
						}
					}
				})
				.catch((error) =>
					setState((prev) => ({
						...prev,
						error: error.message,
						modulesLoaded: true
					}))
				);
		},
		[dispatch]
	);

	useEffect(() => {
		fetchModules();
	}, [fetchModules]);

	useEffect(() => {
		if (isFirstRender.current) {
			isFirstRender.current = false;
			return;
		}
		fetchModules(true);
	}, [isMembershipActivated, isPaymentAddonActivated]);

	// Filter Modules by Tabs
	const filterModules = (modules, index) => {
		let filtered = modules;

		if (index === 1) {
			filtered = filtered.filter((mod) => mod.type === "feature");
		} else if (index === 2) {
			filtered = filtered.filter((mod) => mod.type === "addon");
		}

		const searchValue = searchItemRef.current.toLowerCase();
		filtered = filtered.filter((mod) =>
			mod.title.toLowerCase().includes(searchValue)
		);

		setState((prev) => ({
			...prev,
			modules: filtered,
			noItemFound: filtered.length === 0
		}));
	};

	// Bulk Actions
	const handleBulkActions = () => {
		const { selectedModuleData, bulkAction } = state;

		if (!Object.keys(selectedModuleData).length) {
			showToast("Please select at least a feature", "error");
			return;
		}

		setState((prev) => ({ ...prev, isPerformingBulkAction: true }));
		const actionFunc =
			bulkAction === "activate"
				? bulkActivateModules
				: bulkDeactivateModules;

		actionFunc(selectedModuleData)
			.then((data) =>
				showToast(data.message, data.success ? "success" : "error")
			)
			.catch((e) => showToast(e.message, "error"))
			.finally(() => {
				setState((prev) => ({
					...prev,
					isPerformingBulkAction: false,
					selectedModuleData: {}
				}));
				fetchModules();
			});
	};

	const showToast = (title, status) => {
		toast({
			title: __(title, "user-registration"),
			status,
			duration: 3000
		});
	};

	// Search Modules
	const debounceSearch = useCallback(
		debounce((val) => {
			const lowerVal = val.toLowerCase();
			const filtered = state.originalModules.filter(
				(mod) =>
					(tabIndex === 1 ? mod.type === "feature" : true) &&
					(tabIndex === 2 ? mod.type === "addon" : true) &&
					mod.title.toLowerCase().includes(lowerVal)
			);
			setState((prev) => ({
				...prev,
				modules: filtered,
				noItemFound: filtered.length === 0,
				isSearching: false
			}));
		}, 800)
	);

	const handleSearchInputChange = (e) => {
		const val = e.target.value;
		setState((prev) => ({ ...prev, searchItem: val, isSearching: true }));
		searchItemRef.current = val;
		debounceSearch(val);
	};

	const parseDate = (dateString) => {
		const [day, month, year] = dateString.split("/").map(Number);
		return new Date(year, month - 1, day);
	};

	const handleSorterChange = (sortType, data) => {
		switch (sortType) {
			case "newest":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(secondAddonInContext.released_date) -
							parseDate(firstAddonInContext.released_date)
					)
				}));

				break;
			case "oldest":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(firstAddonInContext.released_date) -
							parseDate(secondAddonInContext.released_date)
					)
				}));
				break;
			case "asc":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							firstAddonInContext.title.localeCompare(
								secondAddonInContext.title
							)
					)
				}));
				break;
			case "desc":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							secondAddonInContext.title.localeCompare(
								firstAddonInContext.title
							)
					)
				}));
				break;
			default:
				setState((prev) => ({
					...prev,
					modulesLoaded: false
				}));
		}
	};

	const sortOptions = [
		{ label: __("Popular", "user-registration"), value: "default" },
		{ label: __("Newest", "user-registration"), value: "newest" },
		{ label: __("Oldest", "user-registration"), value: "oldest" },
		{ label: __("Ascending", "user-registration"), value: "asc" },
		{ label: __("Descending", "user-registration"), value: "desc" }
	];
	const bulkOptions = [
		{ label: __("Activate", "user-registration"), value: "activate" },
		{ label: __("Deactivate", "user-registration"), value: "deactivate" }
	];

	return (
		<Box
			top="var(--wp-admin--admin-bar--height, 0)"
			zIndex={1}
			my="4"
			mx="6"
		>
			<Container maxW="100%">
				<Stack
					direction="row"
					minH="70px"
					justify="space-between"
					px="6"
				>
					<Stack direction="row" align="center" gap="5">
						<Select
							options={sortOptions}
							onChange={(selectedOption) => {
								handleSorterChange(
									selectedOption?.value,
									state.modules
								);
							}}
							chakraStyles={{
								control: (base) => ({
									...base,
									backgroundColor: "white",
									borderColor: "#DFDFE0",
									width: "fit-content"
								}),
								option: (base, state) => ({
									...base,
									backgroundColor: state.isFocused
										? "#475bb2"
										: "white",
									color: state.isFocused ? "white" : "black"
								}),
								dropdownIndicator: (base) => ({
									...base,
									backgroundColor: "transparent",
									paddingRight: "10px",
									color: "black",
									":hover": {
										backgroundColor: "transparent"
									}
								}),
								indicatorSeparator: () => ({
									display: "none"
								})
							}}
						/>
						<Tabs
							index={tabIndex}
							onChange={(index) => setTabIndex(index)}
						>
							<TabList
								borderBottom="0px"
								border="1px solid #DFDFE0"
								borderRadius="4px"
							>
								<Tab
									fontSize="14px"
									borderRadius="4px 0 0 4px"
									style={{
										boxSizing: "border-box"
									}}
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#475bb2",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									boxShadow="none !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: {
												value: state.searchItem
											}
										})
									}
								>
									{__("All Modules", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box"
									}}
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#475bb2",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									boxShadow="none !important"
									borderRight="1px solid #E9E9E9"
									borderLeft="1px solid #E9E9E9"
									marginLeft="0px !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: { value: state.searchItem }
										})
									}
								>
									{__("Features", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box"
									}}
									borderRadius="0 4px 4px 0"
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#475bb2",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									marginLeft="0px !important"
									boxShadow="none !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: {
												value: state.searchItem
											}
										})
									}
								>
									{__("Addons", "user-registration")}
								</Tab>
							</TabList>
						</Tabs>
						<Box display="flex" gap="8px">
							<Select
								placeholder={__(
									"Bulk Actions",
									"user-registration"
								)}
								options={bulkOptions}
								onChange={(e) =>
									setState((prev) => ({
										...prev,
										bulkAction: e.value
									}))
								}
								chakraStyles={{
									control: (base) => ({
										...base,
										backgroundColor: "white",
										borderColor: "#DFDFE0",
										width: "fit-content"
									}),
									option: (base, state) => ({
										...base,
										backgroundColor: state.isFocused
											? "#475bb2"
											: "white",
										color: state.isFocused
											? "white"
											: "black"
									}),
									dropdownIndicator: (base) => ({
										...base,
										backgroundColor: "transparent",
										paddingRight: "10px",
										color: "black",
										":hover": {
											backgroundColor: "transparent"
										}
									}),
									indicatorSeparator: () => ({
										display: "none"
									})
								}}
							/>
							<Button
								fontSize="14px"
								variant="outline"
								fontWeight="normal"
								color="gray.600"
								borderRadius="base"
								border="1px solid #DFDFE0 !important"
								textDecor="none !important"
								padding="6px 12px"
								onClick={handleBulkActions}
								isLoading={state.isPerformingBulkAction}
							>
								{__("Apply", "user-registration")}
							</Button>
						</Box>
					</Stack>
					<Stack direction="row" align="center" gap="7">
						<FormControl>
							<InputGroup>
								<InputLeftElement
									pointerEvents="none"
									top="2px"
								>
									<Search h="5" w="5" color="gray.300" />
								</InputLeftElement>
								<Input
									type="text"
									placeholder={__(
										"Search...",
										"user-registration"
									)}
									paddingLeft="32px !important"
									value={state.searchItem}
									onChange={handleSearchInputChange}
									_focus={{
										borderColor: "#475BB2 !important"
									}}
								/>
							</InputGroup>
						</FormControl>
					</Stack>
				</Stack>
			</Container>
			<Container maxW="100%">
				{state.isSearching ? (
					<AddonSkeleton />
				) : state.noItemFound && state.searchItem ? (
					<Box
						display="flex"
						justifyContent="center"
						flexDirection="column"
						padding="100px"
						gap="10px"
						alignItems="center"
					>
						<PageNotFound color="gray.300" />
						<Text fontSize="20px" fontWeight="600">
							{__("Sorry, no result found.", "user-registration")}
						</Text>
						<Text fontSize="14px" color="gray.500">
							{__(
								"Please try another search",
								"user-registration"
							)}
						</Text>
					</Box>
				) : (
					<Box>
						<Tabs index={tabIndex}>
							<TabPanels>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
										}
										IsStateUpdated={IsStateUpdated}
										resetIsStateUpdated={
											resetIsStateUpdated
										}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
										}
										IsStateUpdated={IsStateUpdated}
										resetIsStateUpdated={
											resetIsStateUpdated
										}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
										}
										IsStateUpdated={IsStateUpdated}
										resetIsStateUpdated={
											resetIsStateUpdated
										}
									/>
								</TabPanel>
							</TabPanels>
						</Tabs>
					</Box>
				)}
			</Container>
		</Box>
	);
};

export default Modules;
