import React, { useEffect, useState } from "react";
import {
	Box,
	Text,
	Heading,
	VStack,
	HStack,
	Input,
	InputGroup,
	InputLeftElement,
	Button,
	Select,
	Card,
	CardBody,
	useColorModeValue,
	ButtonGroup,
	Flex,
	Menu,
	MenuButton,
	MenuList,
	MenuItem,
	Tag,
	TagLabel,
	TagCloseButton,
	IconButton,
	Wrap,
	WrapItem,
	Popover,
	PopoverTrigger,
	PopoverContent,
	PopoverBody,
	Checkbox,
	useDisclosure
} from "@chakra-ui/react";
import { AddIcon, CloseIcon, ChevronDownIcon } from "@chakra-ui/icons";
import { useStateValue } from "../../context/StateProvider";
import {
	MembershipPlan,
	MembershipPlanType,
	BillingPeriod,
	ContentAccess
} from "../../context/Gettingstartedcontext";
import { apiGet } from "../../api/gettingStartedApi";
import { __ } from "@wordpress/i18n";

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
						borderColor={isOpen ? "#475BD8" : borderColor}
						borderRadius="md"
						textAlign="left"
						cursor="pointer"
						_hover={{ borderColor: "gray.300" }}
						_focus={{
							borderColor: "#475BD8",
							boxShadow: "0 0 0 1px #475BD8",
							outline: "none"
						}}
					>
						<Flex align="center" justify="space-between">
							<Wrap spacing={1} flex={1}>
								{selectedOptions.length > 0 ? (
									selectedOptions.map((opt) => (
										<WrapItem key={opt.value}>
											<Tag
												size="sm"
												borderRadius="sm"
												variant="outline"
												colorScheme="gray"
												bg="gray.100"
											>
												<TagLabel fontSize="sm">
													{opt.label}
												</TagLabel>
												<TagCloseButton
													onClick={(e) =>
														handleRemove(
															opt.value,
															e
														)
													}
												/>
											</Tag>
										</WrapItem>
									))
								) : (
									<Text color="gray.400" fontSize="sm">
										{placeholder}
									</Text>
								)}
							</Wrap>
							<ChevronDownIcon color="gray.500" ml={2} />
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
					borderRadius="md"
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
										onChange={() => handleToggle(opt.value)}
										mr={2}
										colorScheme="blue"
									/>
									<Text fontSize="sm">{opt.label}</Text>
								</Flex>
							))
						) : (
							<Text px={3} py={2} color="gray.500" fontSize="sm">
								No options available
							</Text>
						)}
					</PopoverBody>
				</PopoverContent>
			</Popover>
		</Box>
	);
};

interface MembershipCardProps {
	plan: MembershipPlan;
	pages: ContentOption[];
	posts: ContentOption[];
}

const MembershipCard: React.FC<MembershipCardProps> = ({
	plan,
	pages,
	posts
}) => {
	const { dispatch } = useStateValue();

	const cardBg = useColorModeValue("white", "gray.800");
	const borderColor = useColorModeValue("gray.200", "gray.600");
	const labelColor = useColorModeValue("gray.700", "gray.300");
	const inputBg = useColorModeValue("white", "gray.700");
	const accessBg = useColorModeValue("green.50", "green.900");
	const accessBorderColor = useColorModeValue("green.200", "green.700");

	const handleCancelPlan = () => {
		dispatch({
			type: "REMOVE_MEMBERSHIP_PLAN",
			payload: plan.id
		});
	};

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

	const handleCurrencyChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
		dispatch({
			type: "UPDATE_MEMBERSHIP_PLAN",
			payload: { id: plan.id, updates: { currency: e.target.value } }
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

	const handleAddContentAccess = (type: "pages" | "posts") => {
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

	const getOptionsForAccess = (access: ContentAccess): ContentOption[] => {
		if (access.type === "pages") return pages;
		if (access.type === "posts") return posts;
		return [];
	};

	const labelForAccess = (access: ContentAccess) => {
		if (access.type === "pages") return "Pages:";
		if (access.type === "posts") return "Posts:";
		return access.type + ":";
	};

	return (
		<Card
			bg={cardBg}
			borderWidth="1px"
			borderColor={borderColor}
			borderRadius="lg"
			mb={4}
			boxShadow="none"
		>
			<CardBody p={6}>
				<VStack spacing={5} align="stretch">
					{/* Name row with cancel button inline */}
					<Flex align="center">
						<Text minW="100px" fontWeight="500" color={labelColor} flexShrink={0}>
							Name :
						</Text>
						<Input
							flex={1}
							value={plan.name}
							onChange={handleNameChange}
							placeholder="Enter plan name"
							bg={inputBg}
							borderColor={borderColor}
							_hover={{ borderColor: "gray.300" }}
							_focus={{
								borderColor: "#475BD8",
								boxShadow: "0 0 0 1px #475BD8"
							}}
						/>
						{/* Cancel button - inline with Name row */}
						{plan.isNew && (
							<IconButton
								aria-label="Cancel new plan"
								icon={<CloseIcon boxSize={2.5} />}
								size="sm"
								variant="ghost"
								colorScheme="red"
								ml={3}
								onClick={handleCancelPlan}
							/>
						)}
					</Flex>

					<Flex align="center">
						<Text minW="100px" fontWeight="500" color={labelColor} flexShrink={0}>
							Type :
						</Text>
						<ButtonGroup size="sm" isAttached variant="outline">
							<Button
								bg={plan.type === "free" ? "#475BD8" : "white"}
								color={
									plan.type === "free" ? "white" : "gray.700"
								}
								borderColor={
									plan.type === "free"
										? "#475BD8"
										: "gray.200"
								}
								_hover={{
									bg:
										plan.type === "free"
											? "#3a4bc2"
											: "gray.50"
								}}
								onClick={() => handleTypeChange("free")}
								px={6}
							>
								Free
							</Button>
							<Button
								bg={plan.type === "paid" ? "#475BD8" : "white"}
								color={
									plan.type === "paid" ? "white" : "gray.700"
								}
								borderColor={
									plan.type === "paid"
										? "#475BD8"
										: "gray.200"
								}
								_hover={{
									bg:
										plan.type === "paid"
											? "#3a4bc2"
											: "gray.50"
								}}
								onClick={() => handleTypeChange("paid")}
								px={6}
							>
								Paid
							</Button>
						</ButtonGroup>
					</Flex>

					{plan.type === "paid" && (
						<Flex align="center">
							<Text minW="100px" fontWeight="500" color={labelColor} flexShrink={0}>
								Price
							</Text>
							<HStack spacing={3} flex={1}>
								<InputGroup maxW="150px">
									<InputLeftElement
										pointerEvents="none"
										color="gray.500"
									>
										$
									</InputLeftElement>
									<Input
										value={plan.price}
										onChange={handlePriceChange}
										placeholder="0.00"
										bg={inputBg}
										borderColor={borderColor}
									/>
								</InputGroup>
								<Select
									value={plan.currency}
									onChange={handleCurrencyChange}
									maxW="100px"
									bg={inputBg}
									borderColor={borderColor}
								>
									<option value="USD">USD</option>
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="INR">INR</option>
								</Select>
								<Select
									value={plan.billingPeriod}
									onChange={handleBillingPeriodChange}
									maxW="130px"
									bg={inputBg}
									borderColor={borderColor}
								>
									<option value="weekly">Weekly</option>
									<option value="monthly">Monthly</option>
									<option value="yearly">Yearly</option>
									<option value="one-time">One-Time</option>
								</Select>
							</HStack>
						</Flex>
					)}

					{/* Access Section */}
					{plan.contentAccess.length > 0 && (
						<Box
							bg={accessBg}
							borderRadius="md"
							p={4}
							borderWidth="1px"
							borderColor={accessBorderColor}
						>
							<Text
								color="green.600"
								fontWeight="500"
								mb={3}
								fontSize="sm"
							>
								Access ▾
							</Text>
							<VStack spacing={3} align="stretch">
								{plan.contentAccess.map((access) => {
									const options = getOptionsForAccess(access);
									return (
										<Flex
											key={access.id}
											align="center"
											bg="white"
											borderRadius="md"
											borderWidth="1px"
											borderColor="gray.200"
											p={3}
										>
											<Text
												minW="70px"
												fontWeight="500"
												color={labelColor}
												fontSize="sm"
												flexShrink={0}
											>
												{labelForAccess(access)}
											</Text>
											<Box flex="1" mx={2}>
												<Select2MultiSelect
													options={options}
													value={access.value}
													onChange={(ids) =>
														handleAccessValueChange(
															access,
															ids
														)
													}
													placeholder={
														access.type === "pages"
															? "Select pages"
															: "Select posts"
													}
												/>
											</Box>
											<IconButton
												aria-label="Remove access"
												icon={<CloseIcon boxSize={2} />}
												size="xs"
												variant="ghost"
												colorScheme="red"
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

					<Flex>
						<Menu>
							<MenuButton
								as={Button}
								leftIcon={<AddIcon boxSize={3} />}
								variant="outline"
								colorScheme="blue"
								borderColor="#475BD8"
								color="#475BD8"
								_hover={{ bg: "blue.50" }}
							>
								Content
							</MenuButton>
							<MenuList>
								<MenuItem
									onClick={() =>
										handleAddContentAccess("pages")
									}
									isDisabled={hasPages}
									opacity={hasPages ? 0.5 : 1}
									cursor={
										hasPages ? "not-allowed" : "pointer"
									}
								>
									Pages
								</MenuItem>
								<MenuItem
									onClick={() =>
										handleAddContentAccess("posts")
									}
									isDisabled={hasPosts}
									opacity={hasPosts ? 0.5 : 1}
									cursor={
										hasPosts ? "not-allowed" : "pointer"
									}
								>
									Posts
								</MenuItem>
							</MenuList>
						</Menu>
					</Flex>
				</VStack>
			</CardBody>
		</Card>
	);
};

const MembershipStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { membershipPlans } = state;

	const textColor = useColorModeValue("gray.800", "white");

	const [pages, setPages] = useState<ContentOption[]>([]);
	const [posts, setPosts] = useState<ContentOption[]>([]);

	useEffect(() => {
		const loadContent = async () => {
			try {
				const res: any = await apiGet("/content");
				const content = res.content || res;
				setPages(content.pages || []);
				setPosts(content.posts || []);
			} catch (e) {
				console.error(e);
			}
		};
		loadContent();
	}, []);

	const handleAddPlan = () => {
		dispatch({ type: "ADD_MEMBERSHIP_PLAN" });
	};

	return (
		<>
			<Heading
				size="lg"
				fontFamily="Inter"
				fontWeight={600}
				fontSize="21px"
				lineHeight="34px"
				letterSpacing="-0.01em"
				color={textColor}
				mb={8}
			>
				{__("Create Membership", "user-registration")}
			</Heading>

			<VStack spacing={4} align="stretch" mb={6}>
				{membershipPlans.map((plan) => (
					<MembershipCard
						key={plan.id}
						plan={plan}
						pages={pages}
						posts={posts}
					/>
				))}
			</VStack>

			<Flex justify="center">
				<Button
					leftIcon={<AddIcon boxSize={3} />}
					variant="outline"
					colorScheme="blue"
					borderColor="#475BD8"
					color="#475BD8"
					_hover={{ bg: "blue.50" }}
					onClick={handleAddPlan}
				>
					Add More
				</Button>
			</Flex>
		</>
	);
};

export default MembershipStep;
