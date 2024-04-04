/**
 *  External Dependencies
 */
import React, { useState, useEffect } from "react";
import {
	Box,
	Container,
	Stack,
	Select,
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
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useOnType } from "use-ontype";

/**
 *  Internal Dependencies
 */
import { Search } from "../../components/Icon/Icon";
import { isEmpty } from "../../../utils/utils";

import {
	getAllModules,
	bulkActivateModules,
	bulkDeactivateModules,
} from "./components/modules-api";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/dashboardContext";
import ModuleBody from "./components/ModuleBody";

const Modules = () => {
	const toast = useToast();
	const [isSearching, setIsSearching] = useState(false);
	const [{ allModules }, dispatch] = useStateValue();
	const [modulesLoaded, setModulesLoaded] = useState(false);
	const [selectedModuleData, setSelectedModuleData] = useState("");
	const [bulkAction, setBulkAction] = useState("");
	const [isPerformingBulkAction, setIsPerformingBulkAction] = useState(false);
	const [tabIndex, setTabIndex] = useState(0);

	const [modules, setModules] = useState([]);

	useEffect(() => {}, [selectedModuleData]);
	useEffect(() => {
		if (!modulesLoaded) {
			getAllModules()
				.then((data) => {
					if (data.success) {
						dispatch({
							type: actionTypes.GET_ALL_MODULES,
							allModules: data.modules_lists,
						});

						if (tabIndex === 1) {
							var feature_lists = [];
							data.modules_lists.map((module) => {
								if (module.type === "feature") {
									feature_lists.push(module);
								}
							});
							setModules(feature_lists);
						} else if (tabIndex === 2) {
							var addon_lists = [];
							data.modules_lists.map((module) => {
								if (module.type === "addon") {
									addon_lists.push(module);
								}
							});
							setModules(addon_lists);
						} else {
							setModules(data.modules_lists);
						}
						setModulesLoaded(true);
					}
				})
				.catch((e) => {
					toast({
						title: e.message,
						status: "error",
						duration: 3000,
					});
				});
		}
	}, [tabIndex, modules, modulesLoaded, isPerformingBulkAction]);

	const handleBulkActions = () => {
		setIsPerformingBulkAction(true);

		if (bulkAction === "activate") {
			bulkActivateModules(selectedModuleData)
				.then((data) => {
					if (data.success) {
						toast({
							title: data.message,
							status: "success",
							duration: 3000,
						});
					} else {
						toast({
							title: data.message,
							status: "error",
							duration: 3000,
						});
					}
				})
				.catch((e) => {
					toast({
						title: e.message,
						status: "error",
						duration: 3000,
					});
				})
				.finally(() => {
					setModulesLoaded(false);
					setIsPerformingBulkAction(false);
					setSelectedModuleData({});
				});
		} else if (bulkAction === "deactivate") {
			bulkDeactivateModules(selectedModuleData)
				.then((data) => {
					if (data.success) {
						toast({
							title: data.message,
							status: "success",
							duration: 3000,
						});
					} else {
						toast({
							title: data.message,
							status: "error",
							duration: 3000,
						});
					}
				})
				.catch((e) => {
					toast({
						title: e.message,
						status: "error",
						duration: 3000,
					});
				})
				.finally(() => {
					setModulesLoaded(false);
					setIsPerformingBulkAction(false);
					setSelectedModuleData({});
				});
		}
	};

	const onSearchInput = useOnType(
		{
			onTypeStart: (val) => {
				setIsSearching(true);
			},
			onTypeFinish: (val) => {
				if (isEmpty(val)) {
					setModulesLoaded(false);
				} else {
					var searchedData = {};

					if (tabIndex === 1) {
						searchedData = allModules?.filter(
							(module) =>
								module.type === "feature" &&
								module.title
									.toLowerCase()
									.includes(val.toLowerCase()),
						);
					} else if (tabIndex === 2) {
						searchedData = allModules?.filter(
							(module) =>
								module.type === "addon" &&
								module.title
									.toLowerCase()
									.includes(val.toLowerCase()),
						);
					} else {
						searchedData = allModules?.filter((module) =>
							module.title
								.toLowerCase()
								.includes(val.toLowerCase()),
						);
					}

					if (!isEmpty(searchedData)) {
						setModules(searchedData);
						setModulesLoaded(true);
					} else {
						setModulesLoaded(false);
					}
				}
				setIsSearching(false);
			},
		},
		800,
	);

	const parseDate = (dateString) => {
		const [day, month, year] = dateString.split("/").map(Number);
		return new Date(year, month - 1, day);
	};

	const handleSorterChange = (sortType, data, setData) => {
		switch (sortType) {
			case "newest":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(secondAddonInContext.released_date) -
							parseDate(firstAddonInContext.released_date),
					),
				);
				break;
			case "oldest":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(firstAddonInContext.released_date) -
							parseDate(secondAddonInContext.released_date),
					),
				);
				break;
			case "asc":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							firstAddonInContext.title.localeCompare(
								secondAddonInContext.title,
							),
					),
				);
				break;
			case "desc":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							secondAddonInContext.title.localeCompare(
								firstAddonInContext.title,
							),
					),
				);
				break;
			default:
				setModulesLoaded(false);
		}
	};
	return (
		<Box top="var(--wp-admin--admin-bar--height, 0)" zIndex={1}>
			<Container maxW="container.xl">
				<Stack
					direction="row"
					minH="70px"
					justify="space-between"
					px="6"
				>
					<Stack direction="row" align="center" gap="5">
						<Select
							display="inline-flex"
							alignItems="center"
							size="md"
							bg="#DFDFE0"
							onChange={(e) => {
								handleSorterChange(
									e.target.value,
									modules,
									setModules,
								);
							}}
							border="1px solid #DFDFE0 !important"
							borderRadius="4px !important"
							icon=""
							width="fit-content"
						>
							<option value="default">
								{__("Popular", "user-registration")}
							</option>
							<option value="newest">
								{__("Newest", "user-registration")}
							</option>
							<option value="oldest">
								{__("Oldest", "user-registration")}
							</option>
							<option value="asc">
								{__("Ascending", "user-registration")}
							</option>
							<option value="desc">
								{__("Descending", "user-registration")}
							</option>
						</Select>

						<Tabs
							index={tabIndex}
							onChange={(index) => {
								setIsSearching(true);
								setTabIndex(index);
								setModulesLoaded(false);
								new Promise(function (resolve, reject) {
									setTimeout(resolve, 1000);
								}).then(function () {
									setIsSearching(false);
								});
							}}
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
										boxSizing: "border-box",
									}}
									_focus={{
										boxShadow: "none",
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none",
									}}
									boxShadow="none !important"
									transition="none !important"
								>
									{__("All", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box",
									}}
									_focus={{
										boxShadow: "none",
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none",
									}}
									boxShadow="none !important"
									borderRight="1px solid #E9E9E9"
									borderLeft="1px solid #E9E9E9"
									marginLeft="0px !important"
									transition="none !important"
								>
									{__("Features", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box",
									}}
									borderRadius="0 4px 4px 0"
									_focus={{
										boxShadow: "none",
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none",
									}}
									marginLeft="0px !important"
									boxShadow="none !important"
									transition="none !important"
								>
									{__("Addons", "user-registration")}
								</Tab>
							</TabList>
						</Tabs>

						<Box display="flex" gap="8px">
							<Select
								display="inline-flex"
								alignItems="center"
								size="md"
								bg="#DFDFE0"
								placeholder={__(
									"Bulk Actions",
									"user-registration",
								)}
								onChange={(e) => setBulkAction(e.target.value)}
								icon=""
								width="fit-content"
								border="1px solid #DFDFE0 !important"
								borderRadius="4px !important"
							>
								<option value="activate">
									{__("Activate", "user-registration")}
								</option>
								<option value="deactivate">
									{__("Deactivate", "user-registration")}
								</option>
							</Select>

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
								isLoading={isPerformingBulkAction}
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
										"user-registration",
									)}
									paddingLeft="32px !important"
									{...onSearchInput}
								/>
							</InputGroup>
						</FormControl>
					</Stack>
				</Stack>
			</Container>
			<Container maxW="container.xl">
				{isSearching ? (
					<AddonSkeleton />
				) : (
					<Box>
						<Tabs index={tabIndex}>
							<TabPanels>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
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
