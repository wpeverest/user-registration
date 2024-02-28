import React, { useState } from "react";
import AddonsSkeleton from "../../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { Row, Col } from "@chakra-ui/react";

const Features = () => {
	// return <AddonsSkeleton />;
	const [selectedSlugs, setSelectedSlugs] = useState([]);
	return (
		<Row>
			<Col style={{ marginBottom: 30 }} md={3} key={data.slug}>
				<AddonItem
					data={data}
					isChecked={selectedSlugs.includes(data.slug)}
					onCheckedChange={handleCheckedChange}
				/>
			</Col>
		</Row>
	);
};

export default Features;
