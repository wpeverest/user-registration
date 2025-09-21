/**
 *  External Dependencies
 */
import React, { useState, useEffect, useCallback, useRef, useMemo } from "react";
import {
	Box,
	Container,
	useToast,
	Text,
	IconButton,
	useDisclosure
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { debounce } from "lodash";
import { FaArrowUp } from "react-icons/fa";

import { PageNotFound } from "../../components/Icon/Icon";
import {
	getAllModules,
	bulkActivateModules,
	bulkDeactivateModules
} from "./components/modules-api";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/dashboardContext";
import { useUpgradeModal } from "./hooks/useUpgradeModal";
import { useLicenseActivation } from "./hooks/useLicenseActivation";

// Import new components
import Filters from "./components/Filters";
import Categories from "./components/Categories";
import CardsGrid from "./components/CardsGrid";

const Modules = () => {
	const toast = useToast();
	const [
		{ allModules, isMembershipActivated, isPaymentAddonActivated, upgradeModal },
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
		error: null,
		selectedCategory: "All",
		selectedSort: "default",
		selectedStatus: "all",
		selectedPlan: "all",
		isLoading: false
	});
	const [tabIndex, setTabIndex] = useState(0);
	const [IsStateUpdated, setIsStateUpdated] = useState(false);
	const [showScrollTop, setShowScrollTop] = useState(false);
	const searchItemRef = useRef(state.searchItem);
	const isFirstRender = useRef(true);

	const resetIsStateUpdated = () => {
		setIsStateUpdated(false);
	};

	// Dynamic categories based on modules data
	const getDynamicCategories = () => {
		if (!state.originalModules || state.originalModules.length === 0) {
			return ["All"];
		}

		// Get unique categories from modules
		const uniqueCategories = [...new Set(state.originalModules.map(module => module.category).filter(Boolean))];

		// Map category names to display names
		const categoryDisplayNames = {
			'Membership': 'Membership',
			'Smart Fields': 'Smart Fields',
			'User Management': 'User Management',
			'E-Commerce': 'Payments',
			'Design': 'Design',
			'Integrations': 'Integrations',
			'Security': 'Security',
			'Email Marketing': 'Email Marketing',
			'Others': 'Others'
		};

		// Create category objects with both internal and display names
		const categories = [
			{ value: "All", label: "All", internalValue: "All" }
		];

		uniqueCategories.forEach(category => {
			categories.push({
				value: categoryDisplayNames[category] || category,
				label: categoryDisplayNames[category] || category,
				internalValue: category
			});
		});

		return categories;
	};

	const categories = useMemo(() => getDynamicCategories(), [state.originalModules]);

	// Options for dropdowns
	const statusOptions = [
		{ label: "All Status", value: "all" },
		{ label: "Active", value: "active" },
		{ label: "Inactive", value: "inactive" }
	];

	const planOptions = [
		{ label: "All Plans", value: "all" },
		{ label: "Free", value: "free" },
		{ label: "Personal", value: "personal" },
		{ label: "Plus", value: "plus" },
		{ label: "Professional", value: "professional" }
	];

	const sortOptions = [
		{ label: __("All", "user-registration"), value: "default" },
		{ label: __("Newest", "user-registration"), value: "newest" },
		{ label: __("Oldest", "user-registration"), value: "oldest" },
		{ label: __("Ascending", "user-registration"), value: "asc" },
		{ label: __("Descending", "user-registration"), value: "desc" }
	];

	// Memoized values for select components
	const selectedSortValue = useMemo(() =>
		sortOptions.find(option => option.value === state.selectedSort) || null,
		[state.selectedSort]
	);

	const selectedStatusValue = useMemo(() =>
		statusOptions.find(option => option.value === state.selectedStatus) || null,
		[state.selectedStatus]
	);

	const selectedPlanValue = useMemo(() =>
		planOptions.find(option => option.value === state.selectedPlan) || null,
		[state.selectedPlan]
	);

	// Deduplicate modules based on slug
	const deduplicateModules = (modules) => {
		const seen = new Set();
		return modules.filter((module) => {
			if (seen.has(module.slug)) {
				return false;
			}
			seen.add(module.slug);
			return true;
		});
	};

	const fetchModules = useCallback(
		(for_payments = false) => {
			setState((prev) => ({ ...prev, isLoading: true }));
			getAllModules()
				.then((data) => {
					if (data.success) {
						// Deduplicate modules to prevent duplicates from both JSON files
						const deduplicatedModules = deduplicateModules(data.modules_lists);

						dispatch({
							type: actionTypes.GET_ALL_MODULES,
							allModules: deduplicatedModules
						});
						setState((prev) => ({
							...prev,
							originalModules: deduplicatedModules,
							modulesLoaded: true,
							isLoading: false
						}));
						filterModules(deduplicatedModules, tabIndex, false); // No loading for initial load
						if (for_payments) {
							setIsStateUpdated(true);
						}
					}
				})
				.catch((error) =>
					setState((prev) => ({
						...prev,
						error: error.message,
						modulesLoaded: true,
						isLoading: false
					}))
				);
		},
		[dispatch]
	);

	useEffect(() => {
		// Set loading state when component mounts or when switching menus
		setState((prev) => ({ ...prev, isLoading: true }));
		fetchModules();
	}, [fetchModules]);

	useEffect(() => {
		if (isFirstRender.current) {
			isFirstRender.current = false;
			return;
		}
		fetchModules(true);
	}, [isMembershipActivated, isPaymentAddonActivated]);

	// Scroll to top functionality
	useEffect(() => {
		const handleScroll = () => {
			const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			setShowScrollTop(scrollTop > 300);
		};

		window.addEventListener('scroll', handleScroll);
		return () => window.removeEventListener('scroll', handleScroll);
	}, []);

	const scrollToTop = () => {
		window.scrollTo({
			top: 0,
			behavior: 'smooth'
		});
	};

	// Filter Modules by Categories
	const filterModules = (modules, category, showLoading = false, statusFilter = null, planFilter = null) => {
		// Only show loading for category changes, not search operations
		if (showLoading) {
			setState((prev) => ({ ...prev, isLoading: true }));
		}

		// Use setTimeout only when showing loading state
		const processFilter = () => {
		let filtered = modules;

			// Filter by category
			if (category && category !== "All") {
				filtered = filtered.filter((mod) => mod.category === category);
			}

			// Filter by status - use passed parameter or current state
			const currentStatus = statusFilter !== null ? statusFilter : state.selectedStatus;
			if (currentStatus && currentStatus !== "all") {
				filtered = filtered.filter((mod) => mod.status === currentStatus);
			}

			// Filter by plan - use passed parameter or current state
			const currentPlan = planFilter !== null ? planFilter : state.selectedPlan;
			if (currentPlan && currentPlan !== "all") {
				filtered = filtered.filter((mod) => {
					if (currentPlan === "free") {
						return mod.plan && mod.plan.includes("free");
					} else if (currentPlan === "personal") {
						return mod.plan && mod.plan.includes("personal");
					} else if (currentPlan === "plus") {
						return mod.plan && mod.plan.includes("plus");
					} else if (currentPlan === "professional") {
						return mod.plan && mod.plan.includes("professional");
					}
					return true;
				});
			}

			// Filter by search term
		const searchValue = searchItemRef.current.toLowerCase();
		filtered = filtered.filter((mod) =>
			mod.title.toLowerCase().includes(searchValue)
		);

		setState((prev) => ({
			...prev,
			modules: filtered,
				noItemFound: filtered.length === 0,
				isLoading: false
			}));
		};

		if (showLoading) {
			setTimeout(processFilter, 150); // Brief loading state for category changes
		} else {
			processFilter(); // Immediate for search operations
		}
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
			filterModules(state.originalModules, state.selectedCategory, false); // No loading for search
		}, 300) // Reduced debounce time for better responsiveness
	);

	const handleSearchInputChange = (e) => {
		const val = e.target.value;
		setState((prev) => ({ ...prev, searchItem: val }));
		searchItemRef.current = val;
		
		// Only search if at least 3 characters are typed
		if (val.length >= 3) {
			debounceSearch(val);
		} else if (val.length === 0) {
			// If search is cleared, show all modules
			filterModules(state.originalModules, state.selectedCategory, false, state.selectedStatus, state.selectedPlan);
		}
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
			case "default":
				// For "All" sort, just re-apply current filters without sorting
				filterModules(data, state.selectedCategory, false, state.selectedStatus, state.selectedPlan);
				break;
			default:
				setState((prev) => ({
					...prev,
					modulesLoaded: false
				}));
		}
	};

	const bulkOptions = [
		{ label: __("Activate", "user-registration"), value: "activate" },
		{ label: __("Deactivate", "user-registration"), value: "deactivate" }
	];

	// Reset all filters to default values
	const handleResetFilters = () => {
		setState(prev => ({
			...prev,
			selectedSort: "default",
			selectedStatus: "all",
			selectedPlan: "all",
			searchItem: ""
		}));
		// Clear search input
		searchItemRef.current = "";
		// Reset to show all modules with default sorting
		filterModules(state.originalModules, "All", false, "all", "all");
	};


	return (
		<Box top="var(--wp-admin--admin-bar--height, 0)" zIndex={1}  minH="100vh">
			<Container maxW="100%" p="20px">
				{/* Header Section */}
				<Box mb="6">
					<Filters
						sortOptions={sortOptions}
						statusOptions={statusOptions}
						planOptions={planOptions}
						selectedSortValue={selectedSortValue}
						selectedStatusValue={selectedStatusValue}
						selectedPlanValue={selectedPlanValue}
						onSortChange={(selectedOption) => {
							setState(prev => ({ ...prev, selectedSort: selectedOption?.value || "default" }));
							handleSorterChange(selectedOption?.value, state.originalModules);
						}}
						onStatusChange={(selectedOption) => {
							const newStatus = selectedOption?.value || "all";
							setState(prev => ({ ...prev, selectedStatus: newStatus }));
							// Trigger filtering with the new status value immediately
							filterModules(state.originalModules, state.selectedCategory, false, newStatus, null);
						}}
						onPlanChange={(selectedOption) => {
							const newPlan = selectedOption?.value || "all";
							setState(prev => ({ ...prev, selectedPlan: newPlan }));
							// Trigger filtering with the new plan value immediately
							filterModules(state.originalModules, state.selectedCategory, false, null, newPlan);
						}}
						searchValue={state.searchItem}
						onSearchChange={handleSearchInputChange}
						onReset={handleResetFilters}
					/>

					{/* Category Tabs */}
					<Categories
						categories={categories}
						selectedCategory={state.selectedCategory}
						onCategoryChange={(displayValue, internalValue) => {
							setState(prev => ({ ...prev, selectedCategory: displayValue }));
							filterModules(state.originalModules, internalValue, true); // Show loading for category changes
						}}
					/>
						</Box>

				{/* Content Section */}
				{state.isLoading || !state.modulesLoaded ? (
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
							{__("Please try another search", "user-registration")}
						</Text>
					</Box>
				) : (
					<CardsGrid
						modules={state.modules}
						selectedCategory={state.selectedCategory}
						showToast={showToast}
					/>
				)}
			</Container>

			{/* Scroll to Top Button */}
			{showScrollTop && (
				<IconButton
					position="fixed"
					bottom="6"
					right="6"
					zIndex="1000"
					aria-label="Scroll to top"
					icon={<FaArrowUp />}
					size="md"
					variant="outline"
					bg="white"
					borderColor="gray.300"
					color="gray.600"
					borderRadius="lg"
					boxShadow="sm"
					_hover={{
						bg: "gray.50",
						borderColor: "gray.400",
						transform: "translateY(-1px)",
						boxShadow: "md"
					}}
					_focus={{
						boxShadow: "outline"
					}}
					transition="all 0.2s"
					onClick={scrollToTop}
				/>
			)}
		</Box>
	);
};

export default Modules;
