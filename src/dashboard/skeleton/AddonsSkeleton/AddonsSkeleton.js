/**
 *  External Dependencies
 */
import { Box, Flex, Skeleton, SkeletonText, VStack } from "@chakra-ui/react";
import React from "react";
import { Col, Row } from "react-grid-system";

const index = () => {
	return (
		<Row>
			{Array(12)
				.fill(1)
				.map((_, i) => (
					<Col
						style={{ marginBottom: 30 }}
						md={3}
						key={Date.now() + Math.random()}
					>
						<Box
							borderColor="gray.100"
							rounded="sm"
							bg="white"
							mb="30px"
							shadow="sm"
							minH="xs"
						>
							<VStack direction="row" gap={2}>
								<Skeleton h="10rem" width={"100%"} />

								<Box px={5} py={6} width={"100%"}>
									<VStack direction={"row"} gap={5}>
										<Flex
											gap={2}
											justifyContent={"space-evenly"}
											alignItems={"center"}
											width={"100%"}
										>
											<Skeleton
												width={"1rem"}
												height={"1rem"}
											/>
											<SkeletonText
												width={"100%"}
												noOfLines={2}
											/>
											<Skeleton
												width={"2rem"}
												height={"1rem"}
											/>
										</Flex>
										<Skeleton h="2rem" width={"100%"} />
										<SkeletonText
											width={"100%"}
											noOfLines={3}
										/>
									</VStack>
								</Box>
							</VStack>
						</Box>
					</Col>
				))}
		</Row>
	);
};

export default index;
