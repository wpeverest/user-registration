/**
 *  External Dependencies
 */
import { Box, Skeleton, SkeletonText, VStack, HStack, SimpleGrid } from "@chakra-ui/react";
import React from "react";

const AddonCardSkeleton = () => {
	return (
		<Box
			bg="white"
			borderRadius="xl"
			border="1px solid"
			borderColor="gray.200"
			p="6"
			boxShadow="sm"
			position="relative"
			height="100%"
			display="flex"
			flexDirection="column"
		>
			{/* Main Content Layout */}
			<HStack align="start" spacing="4" flex="1" mb="6">
				{/* Left Side - Icon Skeleton */}
				<Box
					w="12"
					h="12"
					bg="white"
					borderRadius="lg"
					display="flex"
					alignItems="center"
					justifyContent="center"
					boxShadow="sm"
					flexShrink={0}
				>
					<Skeleton width="24px" height="24px" borderRadius="md" />
				</Box>

				{/* Right Side - Title, Description, and Plan Badge */}
				<VStack align="start" spacing="3" flex="1">
					{/* Title and Plan Badge */}
					<HStack justify="space-between" w="full" align="start">
						<Skeleton height="20px" width="60%" />
						<Skeleton height="16px" width="60px" borderRadius="sm" />
					</HStack>

					{/* Description Skeleton */}
					<SkeletonText
						width="100%"
						noOfLines={3}
						spacing="2"
						skeletonHeight="12px"
						flex="1"
					/>
				</VStack>
			</HStack>

			{/* Footer Section Skeleton */}
			<HStack justify="space-between" align="center">
				<HStack spacing="3">
					{/* Docs Link Skeleton */}
					<Skeleton height="16px" width="40px" />
					<Box color="gray.300">|</Box>
					{/* Settings Icon Skeleton */}
					<Skeleton height="16px" width="16px" borderRadius="md" />
					<Box color="gray.300">|</Box>
					{/* Video Icon Skeleton */}
					<Skeleton height="16px" width="16px" borderRadius="md" />
				</HStack>
				<HStack spacing="2">
					{/* Switch or Upgrade Button Skeleton */}
					<Skeleton height="24px" width="80px" borderRadius="md" />
				</HStack>
			</HStack>
		</Box>
	);
};

const index = () => {
	return (
		<Box>
			<Box mb="8" background={"white"} p={10} borderRadius={12}>
				{/* Header Skeleton */}
				<HStack justify="space-between" mb="4">
					<Skeleton height="24px" width="120px" />
					<Skeleton height="16px" width="60px" />
				</HStack>
				{/* Divider */}
				<Box w="full" h="1px" bg="gray.200" mb="6" />

				{/* Cards Grid Skeleton */}
				<SimpleGrid columns={{ base: 1, md: 2, lg: 3 }} spacing="6">
					{Array(6)
						.fill(1)
						.map((_, i) => (
							<AddonCardSkeleton key={i} />
						))}
				</SimpleGrid>
			</Box>
		</Box>
	);
};

export default index;
