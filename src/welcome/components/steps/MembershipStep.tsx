import { AddIcon, ChevronDownIcon, CloseIcon } from "@chakra-ui/icons";
import {
	Box,
	Button,
	Card,
	CardBody,
	Checkbox,
	Flex,
	Heading,
	HStack,
	IconButton,
	Input,
	InputGroup,
	Menu,
	MenuButton,
	MenuItem,
	MenuList,
	Popover,
	PopoverBody,
	PopoverContent,
	PopoverTrigger,
	Select,
	Spinner,
	Tag,
	TagCloseButton,
	TagLabel,
	Text,
	useColorModeValue,
	useDisclosure,
	VStack,
	Wrap,
	WrapItem
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useEffect, useState } from "react";
import { apiGet } from "../../api/gettingStartedApi";
import {
	BillingPeriod,
	ContentAccess,
	MembershipPlan,
	MembershipPlanType
} from "../../context/Gettingstartedcontext";
import { useStateValue } from "../../context/StateProvider";
import { DeleteIcon } from "../Icon/Icon";

interface ContentOption {
	value: number;
	label: string;
}

interface Select2MultiSelectProps {
	options: ContentOption[];
	value: number[];
	onChange: (ids: number[]) => void;
	placeholder?: string;
}

const Select2MultiSelect: React.FC<Select2MultiSelectProps> = ({
	options,
	value,
	onChange,
	placeholder = "Select..."
}) => {
	const { isOpen, onOpen, onClose } = useDisclosure();
	const borderColor = useColorModeValue("gray.200", "gray.600");
	const inputBg = useColorModeValue("white", "gray.700");
	const hoverBg = useColorModeValue("gray.100", "gray.600");

	const selectedOptions = options.filter((opt) => value.includes(opt.value));

	const handleToggle = (optionValue: number) => {
		if (value.includes(optionValue)) {
			onChange(value.filter((v) => v !== optionValue));
		} else {
			onChange([...value, optionValue]);
		}
	};

	const handleRemove = (optionValue: number, e: React.MouseEvent) => {
		e.stopPropagation();
		onChange(value.filter((v) => v !== optionValue));
	};

	return (
		<Box position="relative" w="100%">
			<Popover
				isOpen={isOpen}
				onOpen={onOpen}
				onClose={onClose}
				placement="bottom-start"
				matchWidth
				autoFocus={false}
			>
				<PopoverTrigger>
					<Box
						as="button"
						type="button"
						w="100%"
						minH="40px"
						px={3}
						py={2}
						bg={inputBg}
						border="1px solid"
						borderColor={isOpen ? "#475BB2" : borderColor}
						borderRadius="4px"
						textAlign="left"
						cursor="pointer"
						_hover={{ borderColor: "gray.300" }}
						_focus={{
							borderColor: "#475BB2",
							boxShadow: "none",
							outline: "none"
						}}
					>
						<Flex align="center" justify="space-between">
							<Wrap spacing={2} flex={1}>
								{selectedOptions.length > 0 ? (
									selectedOptions.map((opt) => (
										<WrapItem key={opt.value}>
											<Tag
												size="sm"
												borderRadius="4px"
												variant="solid"
												bg="#F3F4F6"
												color="#4B5563"
												px={2}
												py={1}
												h="26px"
											>
												<TagLabel
													fontSize="12px"
													fontWeight="400"
												>
													{opt.label}
												</TagLabel>
												<TagCloseButton
													onClick={(e) =>
														handleRemove(
															opt.value,
															e
														)
													}
													color="#9CA3AF"
													fontSize="10px"
												/>
											</Tag>
										</WrapItem>
									))
								) : (
									<Text color="gray.400" fontSize="13px">
										{placeholder}
									</Text>
								)}
							</Wrap>
							<ChevronDownIcon color="gray.400" ml={2} />
						</Flex>
					</Box>
				</PopoverTrigger>
				<PopoverContent
					w="100%"
					maxH="200px"
					overflowY="auto"
					boxShadow="lg"
					border="1px solid"
					borderColor="gray.200"
					borderRadius="4px"
					zIndex={10}
					mt={1}
				>
					<PopoverBody p={0}>
						{options.length > 0 ? (
							options.map((opt) => (
								<Flex
									key={opt.value}
									px={3}
									py={2}
									align="center"
									cursor="pointer"
									_hover={{ bg: hoverBg }}
									onClick={() => handleToggle(opt.value)}
								>
									<Checkbox
										isChecked={value.includes(opt.value)}
										mr={2}
										colorScheme="blue"
										pointerEvents="none"
										size="sm"
									/>
									<Text fontSize="13px" color="#4B5563">
										{opt.label}
									</Text>
								</Flex>
							))
						) : (
							<Text
								px={3}
								py={2}
								color="gray.500"
								fontSize="13px"
							>
								No options available
							</Text>
						)}
					</PopoverBody>
				</PopoverContent>
			</Popover>
		</Box>
	);
};

interface TypeToggleProps {
	value: MembershipPlanType;
	onChange: (type: MembershipPlanType) => void;
}

const TypeToggle: React.FC<TypeToggleProps> = ({ value, onChange }) => {
	return (
		<Box bg="#F6F6F9" borderRadius="6px" p="4px" display="inline-flex">
			<Button
				bg={value === "free" ? "#475BB2" : "transparent"}
				color={value === "free" ? "white" : "#6B7280"}
				borderRadius="4px"
				_hover={{
					bg: value === "free" ? "#3A4B9C" : "transparent"
				}}
				_active={{
					bg: value === "free" ? "#3A4B9C" : "transparent"
				}}
				onClick={() => onChange("free")}
				px={6}
				fontSize="14px"
				fontWeight="500"
				h="32px"
				minW="70px"
			>
				{__("Free", "user-registration")}
			</Button>
			<Button
				bg={value === "paid" ? "#475BB2" : "transparent"}
				color={value === "paid" ? "white" : "#6B7280"}
				borderRadius="4px"
				_hover={{
					bg: value === "paid" ? "#3A4B9C" : "transparent"
				}}
				_active={{
					bg: value === "paid" ? "#3A4B9C" : "transparent"
				}}
				onClick={() => onChange("paid")}
				px={6}
				fontSize="14px"
				fontWeight="500"
				h="32px"
				minW="70px"
			>
				{__("Paid", "user-registration")}
			</Button>
		</Box>
	);
};

interface MembershipCardProps {
	plan: MembershipPlan;
	pages: ContentOption[];
	posts: ContentOption[];
	isPro: boolean;
	onDelete: (id: string) => void;
	showDelete: boolean;
}

const MembershipCard: React.FC<MembershipCardProps> = ({
	plan,
	pages,
	posts,
	isPro,
	onDelete,
	showDelete
}) => {
	const { dispatch } = useStateValue();

	const cardBg = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.600");
	const labelColor = useColorModeValue("#383838", "gray.300");
	const inputBg = useColorModeValue("white", "gray.700");

	useEffect(() => {
		if (!isPro && plan.billingPeriod !== "one-time") {
			dispatch({
				type: "UPDATE_MEMBERSHIP_PLAN",
				payload: {
					id: plan.id,
					updates: { billingPeriod: "one-time" }
				}
			});
		}
	}, [isPro, plan.id, plan.billingPeriod, dispatch]);

	const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: { id: plan.id, updates: { name: e.target.value } }
		});
	};

	const handleTypeChange = (type: MembershipPlanType) => {
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: { id: plan.id, updates: { type } }
		});
	};

	const handlePriceChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: { id: plan.id, updates: { price: e.target.value } }
		});
	};

	const handleBillingPeriodChange = (
		e: React.ChangeEvent<HTMLSelectElement>
	) => {
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: {
				id: plan.id,
				updates: { billingPeriod: e.target.value as BillingPeriod }
			}
		});
	};

	const handleAddContentAccess = (type: "pages" | "posts" | "wholesite") => {
		if (plan.contentAccess.some((a) => a.type === type)) {
			return;
		}
		const newAccess: ContentAccess = {
			id: Math.random().toString(36).substring(2, 9),
			type,
			value: []
		};
		dispatch({
			type: "ADD_CONTENT_ACCESS",
			payload: {
				planId: plan.id,
				access: newAccess
			}
		});
	};

	const handleRemoveContentAccess = (accessId: string) => {
		const updatedAccessList = plan.contentAccess.filter(
			(a) => a.id !== accessId
		);
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: {
				id: plan.id,
				updates: { contentAccess: updatedAccessList }
			}
		});
	};

	const handleAccessValueChange = (access: ContentAccess, ids: number[]) => {
		const updatedAccessList = plan.contentAccess.map((a) =>
			a.id === access.id ? { ...a, value: ids } : a
		);
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: {
				id: plan.id,
				updates: { contentAccess: updatedAccessList }
			}
		});
	};

	const hasPages = plan.contentAccess.some((a) => a.type === "pages");
	const hasPosts = plan.contentAccess.some((a) => a.type === "posts");
	const hasWholeSite = plan.contentAccess.some((a) => a.type === "wholesite");

	const sortedContentAccess = [...plan.contentAccess].sort((a, b) => {
		const order = { wholesite: 0, pages: 1, posts: 2 };
		return (order[a.type] ?? 3) - (order[b.type] ?? 3);
	});

	const getOptionsForAccess = (access: ContentAccess): ContentOption[] => {
		if (access.type === "pages") return pages;
		if (access.type === "posts") return posts;
		return [];
	};

	const labelForAccess = (access: ContentAccess) => {
		if (access.type === "pages") return "Pages:";
		if (access.type === "posts") return "Posts:";
		if (access.type === "wholesite") return "Whole Site:";
		return access.type + ":";
	};

	const placeholderForAccess = (access: ContentAccess) => {
		if (access.type === "pages") return "Select pages";
		if (access.type === "posts") return "Select posts";
		return "Select...";
	};

	const inputStyles = {
		fontSize: "14px",
		bg: inputBg,
		borderColor: borderColor,
		borderRadius: "4px",
		_hover: { borderColor: "gray.300" },
		_focus: {
			borderColor: "#475BB2",
			boxShadow: "none",
			borderRadius: "4px"
		},
		_placeholder: { fontSize: "14px", color: "gray.400" }
	};

	const labelStyles = {
		minW: "100px",
		fontWeight: "600",
		color: labelColor,
		fontSize: "14px",
		flexShrink: 0
	};

	return (
		<Card
			bg={cardBg}
			borderWidth="1px"
			borderColor={borderColor}
			borderRadius="8px"
			mb={4}
			boxShadow="none"
		>
			<CardBody p={6}>
				<VStack spacing={5} align="stretch">
					<Flex align="center">
						<Text {...labelStyles}>
							{__("Plan Name :", "user-registration")}
						</Text>
						<Input
							flex={1}
							value={plan.name}
							onChange={handleNameChange}
							placeholder="Enter plan name"
							{...inputStyles}
						/>
					</Flex>

					<Flex align="center">
						<Text {...labelStyles}>
							{__("Type :", "user-registration")}
						</Text>
						<TypeToggle
							value={plan.type}
							onChange={handleTypeChange}
						/>
					</Flex>

					{plan.type === "paid" && (
						<Flex align="center">
							<Text {...labelStyles}>
								{__("Price :", "user-registration")}
							</Text>
							<HStack spacing={3} flex={1}>
								<InputGroup maxW="120px">
									<Input
										value={plan.price}
										onChange={handlePriceChange}
										{...inputStyles}
									/>
								</InputGroup>
								{isPro && (
									<Select
										value={plan.billingPeriod}
										onChange={handleBillingPeriodChange}
										maxW="120px"
										{...inputStyles}
									>
										<option value="weekly">Weekly</option>
										<option value="monthly">Monthly</option>
										<option value="yearly">Annually</option>
										<option value="one-time">
											One-Time
										</option>
									</Select>
								)}
							</HStack>
						</Flex>
					)}

					<Flex align="flex-start">
						<Text {...labelStyles} pt={2}>
							{__("Access :", "user-registration")}
						</Text>
						<VStack spacing={3} align="stretch" flex={1}>
							{/* Single container box for all access items */}
							{sortedContentAccess.length > 0 && (
								<Box>
									<VStack spacing={4} align="stretch">
										{sortedContentAccess.map((access) => {
											const isWholeSite =
												access.type === "wholesite";
											const options =
												getOptionsForAccess(access);

											return (
												<Flex
													key={access.id}
													align="center"
													role="group"
												>
													<Text
														minW="80px"
														fontWeight="500"
														color={labelColor}
														fontSize="14px"
														flexShrink={0}
													>
														{labelForAccess(access)}
													</Text>
													{isWholeSite ? (
														<Text
															flex="1"
															mx={2}
															fontSize="14px"
															color={labelColor}
														>
															{__(
																"Whole Site",
																"user-registration"
															)}
														</Text>
													) : (
														<Box flex="1" mx={2}>
															<Select2MultiSelect
																options={
																	options
																}
																value={
																	access.value
																}
																onChange={(
																	ids
																) =>
																	handleAccessValueChange(
																		access,
																		ids
																	)
																}
																placeholder={placeholderForAccess(
																	access
																)}
															/>
														</Box>
													)}
													<IconButton
														aria-label="Remove access"
														icon={
															<CloseIcon
																boxSize={2}
															/>
														}
														size="sm"
														opacity={0}
														variant="ghost"
														color="red.500"
														_hover={{
															opacity: "1"
														}}
														onClick={() =>
															handleRemoveContentAccess(
																access.id
															)
														}
													/>
												</Flex>
											);
										})}
									</VStack>
								</Box>
							)}

							<Box>
								<Menu>
									<MenuButton
										as={Button}
										leftIcon={<AddIcon boxSize={2.5} />}
										variant="solid"
										bg="#EDEFF7"
										color="#475BB2"
										fontSize="14px"
										fontWeight="500"
										h="32px"
										px={3}
										borderRadius="4px"
										border="1px solid"
										borderColor="#F8F8FA"
										_hover={{ bg: "#E2E6F3" }}
										_active={{ bg: "#D8DCF0" }}
									>
										{__("Content", "user-registration")}
									</MenuButton>
									<MenuList borderRadius="4px" boxShadow="md">
										<MenuItem
											onClick={() =>
												handleAddContentAccess(
													"wholesite"
												)
											}
											isDisabled={hasWholeSite}
											opacity={hasWholeSite ? 0.5 : 1}
											cursor={
												hasWholeSite
													? "not-allowed"
													: "pointer"
											}
											fontSize="14px"
										>
											{__(
												"Whole Site",
												"user-registration"
											)}
										</MenuItem>
										<MenuItem
											onClick={() =>
												handleAddContentAccess("pages")
											}
											isDisabled={hasPages}
											opacity={hasPages ? 0.5 : 1}
											cursor={
												hasPages
													? "not-allowed"
													: "pointer"
											}
											fontSize="14px"
										>
											{__("Pages", "user-registration")}
										</MenuItem>
										<MenuItem
											onClick={() =>
												handleAddContentAccess("posts")
											}
											isDisabled={hasPosts}
											opacity={hasPosts ? 0.5 : 1}
											cursor={
												hasPosts
													? "not-allowed"
													: "pointer"
											}
											fontSize="14px"
										>
											{__("Posts", "user-registration")}
										</MenuItem>
									</MenuList>
								</Menu>
							</Box>
						</VStack>
					</Flex>

					{showDelete && (
						<Flex justify="flex-end">
							<IconButton
								aria-label="Delete membership"
								icon={<DeleteIcon />}
								size="sm"
								variant="ghost"
								color="gray.400"
								_hover={{
									color: "red.500",
									bg: "transparent"
								}}
								onClick={() => onDelete(plan.id)}
							/>
						</Flex>
					)}
				</VStack>
			</CardBody>
		</Card>
	);
};

const MembershipStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { membershipPlans } = state;

	const textColor = useColorModeValue("#383838", "white");
	const subtextColor = useColorModeValue("gray.600", "gray.300");

	const [pages, setPages] = useState<ContentOption[]>([]);
	const [posts, setPosts] = useState<ContentOption[]>([]);
	const [isLoadingData, setIsLoadingData] = useState(true);

	const isPro = (window as any).urmSetupWizard?.isPro || false;

	useEffect(() => {
		const loadMembershipsData = async () => {
			try {
				setIsLoadingData(true);
				const res: any = await apiGet("/memberships");

				const content = res.content || {};
				setPages(content.pages || []);
				setPosts(content.posts || []);

				const currentPlans = state.membershipPlans;
				const hasModifiedPlans = currentPlans.some(
					(plan) =>
						plan.name !== "" ||
						plan.price !== "" ||
						plan.contentAccess.length > 0
				);

				if (
					res.memberships &&
					Array.isArray(res.memberships) &&
					res.memberships.length > 0 &&
					!hasModifiedPlans
				) {
					const hydratedPlans: MembershipPlan[] = res.memberships.map(
						(m: any) => ({
							id: String(m.id),
							name: m.name || "",
							type: m.type || "free",
							price: m.price || "",
							billingPeriod: m.billingPeriod || "monthly",
							contentAccess: m.contentAccess || [],
							isNew: false
						})
					);

					dispatch({
						type: "HYDRATE_FROM_API",
						payload: {
							membershipPlans: hydratedPlans
						}
					});
				}
			} catch (e) {
				console.error("Failed to load memberships data:", e);
			} finally {
				setIsLoadingData(false);
			}
		};

		loadMembershipsData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, []);

	const handleAddPlan = () => {
		dispatch({ type: "ADD_MEMBERSHIP_PLAN" });
	};

	const handleDeletePlan = (id: string) => {
		dispatch({
			type: "REMOVE_MEMBERSHIP_PLAN",
			payload: id
		});
	};

	if (isLoadingData) {
		return (
			<Flex justify="center" align="center" minH="200px">
				<Spinner size="lg" color="#475BB2" />
			</Flex>
		);
	}

	return (
		<>
			<Heading
				fontFamily="Inter"
				fontWeight={600}
				fontSize="24px"
				lineHeight="34px"
				letterSpacing="-0.01em"
				color={textColor}
				mb={3}
			>
				{__("Create Membership", "user-registration")}
			</Heading>

			<Text fontSize="14px" color={subtextColor} mb={8}>
				{__(
					"Create your first membership plan. Choose what content to protect. You can edit this anytime.",
					"user-registration"
				)}
			</Text>

			<VStack spacing={4} align="stretch" mb={6}>
				{membershipPlans.map((plan) => (
					<MembershipCard
						key={plan.id}
						plan={plan}
						pages={pages}
						posts={posts}
						isPro={isPro}
						onDelete={handleDeletePlan}
						showDelete={membershipPlans.length > 1}
					/>
				))}
			</VStack>

			<Flex justify="center">
				<Button
					leftIcon={<AddIcon boxSize={2.5} />}
					variant="outline"
					borderRadius="4px"
					borderColor="#F8F8FA"
					bg="#EDEFF7"
					color="#475BB2"
					fontSize="14px"
					fontWeight="500"
					px={6}
					py={4}
					h="auto"
					_hover={{ bg: "#E2E6F3" }}
					_active={{ bg: "#D8DCF0" }}
					onClick={handleAddPlan}
				>
					{__("Add Another Plan", "user-registration")}
				</Button>
			</Flex>
		</>
	);
};

export default MembershipStep;
