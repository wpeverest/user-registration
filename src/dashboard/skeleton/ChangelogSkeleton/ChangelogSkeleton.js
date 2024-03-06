/**
 *  External Dependencies
 */
import { Box, Skeleton, SkeletonText, Stack } from "@chakra-ui/react";
import React from "react";

const ChangelogSkeleton = () => {
	return (
		<>
			{Array.from({ length: 5 }).map((_, i) => (
				<Box key={i} mt={i > 0 ? "20px" : undefined}>
					<Stack
						direction="row"
						align="center"
						justify="space-between"
					>
						<Skeleton height="17px" width="80px" />
						<Skeleton height="10px" width="57px" />
					</Stack>
					<Skeleton height="26px" width="60px" mt="20px" />
					<SkeletonText mt="20px" noOfLines={5} spacing="1" />
				</Box>
			))}
		</>
	);
};

export default ChangelogSkeleton;
