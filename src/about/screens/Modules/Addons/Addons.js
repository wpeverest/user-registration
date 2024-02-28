import React, { useState, useEffect } from "react";
import AddonSkeleton from "../../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { Tabs, Container } from "@chakra-ui/react";
import AddonItem from "./components/AddonItem";
import { isArray, isEmpty } from "../../../../utils/utils";
import { Col, Row } from "react-grid-system";
import { useStateValue } from "../../../../context/StateProvider";
import { actionTypes } from "../../../../context/gettingStartedContext";

const Addons = ({
	isPerformingBulkAction,
	filteredAddons,
	selectedSlugs,
	setSelectedSlugs,
}) => {
	const handleCheckedChange = (slug, checked) => {
		if (checked) {
			setSelectedSlugs((prev) => [...prev, slug + "/" + slug + ".php"]);
		} else {
			setSelectedSlugs((prev) =>
				prev.filter((s) => s !== slug + "/" + slug + ".php")
			);
		}
	};

	// useEffect(() => {
	// 	console.log(filteredAddons);
	// }, [filteredAddons]);

	return (
		<>
			<Tabs>
				<Container maxW="container.xl">
					{isEmpty(filteredAddons) ? (
						<AddonSkeleton />
					) : (
						<Row>
							{isArray(filteredAddons) &&
								filteredAddons?.map((data) => (
									<Col
										style={{ marginBottom: 30 }}
										md={4}
										key={data.slug}
									>
										<AddonItem
											data={data}
											isChecked={Object.values(
												selectedSlugs
											)?.includes(
												data.slug +
													"/" +
													data.slug +
													".php"
											)}
											onCheckedChange={
												handleCheckedChange
											}
											isPerformingBulkAction={
												isPerformingBulkAction
											}
											selectedSlugs={selectedSlugs}
										/>
									</Col>
								))}
						</Row>
					)}
				</Container>
			</Tabs>
		</>
	);
};

export default Addons;
