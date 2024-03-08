/**
 *  External Dependencies
 */
import React, { useEffect, useRef, useState } from "react";
import {
	HStack,
	IconButton,
	Menu,
	MenuButton,
	MenuItem,
	MenuList,
} from "@chakra-ui/react";
import { NavLink, useLocation } from "react-router-dom";
import PropTypes from "prop-types";

/**
 *  Internal Dependencies
 */
import { DotsHorizontal } from "../Icon/Icon";

const IntersectionStyles = {
	visible: {
		order: 0,
		visibility: "visible",
		opacity: 1,
	},
	inVisible: {
		order: 100,
		visibility: "hidden",
		pointerEvents: "none",
	},
	toolbarWrapper: {
		overflow: "hidden",
		display: "flex",
		border: "1px solid black",
		alignItem: "center",
	},
	overflowStyle: {
		order: 99,
		position: "sticky",
		right: "0",
		backgroundColor: "white",
	},
};

const IntersectObserver = ({ children, routes }) => {
	const ref = useRef(null);
	const [visibleMap, setVisibleMap] = useState({});
	const location = useLocation();
	const hiddenRoutes = routes.filter((route) => !visibleMap[route.route]);

	const selectedHiddenRoute = hiddenRoutes.find(
		(h) => h.route === location.pathname
	);

	useEffect(() => {
		if (!ref.current) return;
		const observer = new IntersectionObserver(
			(entries) => {
				const updatedEntries = {};
				entries.forEach((entry) => {
					const target = entry.target.dataset?.["target"];
					if (entry.isIntersecting && target) {
						updatedEntries[target] = true;
					}
					if (!entry.isIntersecting && target) {
						updatedEntries[target] = false;
					}
				});

				setVisibleMap((prev) => ({
					...prev,
					...updatedEntries,
				}));
			},
			{
				root: ref.current,
				threshold: 0.98,
			}
		);

		Array.from(ref.current.children).forEach((item) => {
			if (item.getAttribute("data-target")) {
				observer.observe(item);
			}
		});

		return () => observer.disconnect();
	}, []);

	const shouldShowMenu = Object.values(visibleMap).some((v) => v === false);

	return (
		<HStack
			ref={ref}
			width={{
				base: "50px",
				sm: "240px",
				md: "520px",
				lg: "570px",
				xl: "680px",
			}}
			overflow={"hidden"}
			h="full"
		>
			{React.Children.map(children, (child) => {
				const otherSX = visibleMap[child.props["data-target"]]
					? IntersectionStyles.visible
					: IntersectionStyles.inVisible;

				return React.cloneElement(child, {
					sx: { ...children?.props?.sx, ...otherSX },
				});
			})}

			{shouldShowMenu && (
				<Menu>
					<MenuButton
						as={IconButton}
						aria-label="Options"
						sx={IntersectionStyles.overflowStyle}
						style={{
							background: "#FFFFFF",
							boxShadow: "none",
							marginLeft: "0px",
						}}
						color={
							!!selectedHiddenRoute ? "primary.500" : "#383838"
						}
						visibility={"visible"}
						icon={<DotsHorizontal w="24px" h="24px" />}
					></MenuButton>
					<MenuList display="flex" flexDirection="column">
						{hiddenRoutes.map(({ label, route }) => {
							return (
								<MenuItem
									key={route}
									as={NavLink}
									to={route}
									marginBottom={"0px"}
									data-target={route}
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
									}}
									display="inline-flex"
									alignItems="center"
									px="2"
									py="10px"
								>
									{label}
								</MenuItem>
							);
						})}
					</MenuList>
				</Menu>
			)}
		</HStack>
	);
};

IntersectObserver.propTypes = {
	children: PropTypes.any,
	routes: PropTypes.arrayOf(
		PropTypes.shape({
			label: PropTypes.string.isRequired,
			route: PropTypes.string.isRequired,
		})
	).isRequired,
};

export default IntersectObserver;
