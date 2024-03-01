import {
	Box,
	Button,
	Container,
	Drawer,
	DrawerBody,
	DrawerCloseButton,
	DrawerContent,
	DrawerHeader,
	DrawerOverlay,
	Image,
	Link,
	Stack,
	Tag,
	Text,
	useDisclosure,
	Divider,
	Center,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useRef } from "react";
import { NavLink } from "react-router-dom";
import { ROUTES } from "../../Constants";
import announcement from "../../images/announcement.gif";
import { Logo } from "../Icon/Icon";
import IntersectObserver from "../IntersectionObserver/IntersectionObserver";
import Changelog from "../Changelog/Changelog";

const Header = () => {
	const { isOpen, onOpen, onClose } = useDisclosure();
	const ref = useRef;

	/* global _UR_ */
	const { version, isPro, upgradeURL } = typeof _UR_ !== "undefined" && _UR_;

	React.useEffect(() => {
		if (isOpen) {
			document.body.classList.add("ur-modal-open");
		} else {
			document.body.classList.remove("ur-modal-open");
		}
		return () => {
			document.body.classList.remove("ur-modal-open");
		};
	}, [isOpen]);
	return (
		<>
			<Box
				position={{
					sm: "sticky",
				}}
				top="var(--wp-admin--admin-bar--height, 0)"
				bg={"white"}
				zIndex={1}
				borderBottom="1px solid #E9E9E9"
			>
				<Container maxW="container.xl">
					<Stack
						direction="row"
						minH="70px"
						justify="space-between"
						px="6"
					>
						<Stack direction="row" align="center" gap="7">
							<Link as={NavLink} to="/dashboard">
								<Logo h="10" w="10" />
							</Link>
							<IntersectObserver routes={ROUTES}>
								{ROUTES.map(({ route, label }) => (
									<Link
										data-target={route}
										key={route}
										as={NavLink}
										to={route}
										fontSize="sm"
										fontWeight="semibold"
										lineHeight="150%"
										color="#383838"
										_hover={{
											color: "primary.500",
										}}
										_focus={{
											boxShadow: "none",
										}}
										_activeLink={{
											color: "primary.500",
											borderBottom: "3px solid",
											borderColor: "primary.500",
											marginBottom: "-2px",
										}}
										display="inline-flex"
										alignItems="center"
										px="2"
										h="full"
									>
										{label}
									</Link>
								))}
							</IntersectObserver>
						</Stack>
						<Stack direction="row" align="center" spacing="12px">
							<Tag
								variant="outline"
								colorScheme="primary"
								borderRadius="xl"
								bgColor="#F8FAFF"
								fontSize="xs"
							>
								{version}
							</Tag>
							<Center height="18px">
								<Divider orientation="vertical" />
							</Center>
							{!isPro && (
								<Link
									color="#2563EB"
									fontSize="12px"
									height="18px"
									w="85px"
									href={upgradeURL}
									isExternal
								>
									{__("Upgrade To Pro", "user-registration")}
								</Link>
							)}
							<Button
								onClick={onOpen}
								variant="unstyled"
								borderRadius="full"
								border="2px"
								borderColor="gray.200"
								w="40px"
								h="40px"
								position="relative"
							>
								<Image
									src={announcement}
									alt="announcement"
									h="35px"
									w="35px"
									position="absolute"
									top="50%"
									left="50%"
									transform="translate(-40%, -50%)"
								/>
							</Button>
						</Stack>
					</Stack>
				</Container>
			</Box>
			<Drawer
				isOpen={isOpen}
				placement="right"
				onClose={onClose}
				finalFocusRef={ref}
			>
				<DrawerOverlay bgColor="rgba(0,0,0,0.05)" />
				<DrawerContent
					className="user-registration-announcement"
					top="var(--wp-admin--admin-bar--height, 0) !important"
				>
					<DrawerCloseButton />
					<DrawerHeader>
						{__("Latest Updates", "user-registration")}
					</DrawerHeader>
					<DrawerBody>
						<Changelog />{" "}
					</DrawerBody>
				</DrawerContent>
			</Drawer>
		</>
	);
};

export default Header;
