/**
 *  External Dependencies
 */
import { Box, Heading, HStack, Tag, Text } from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { sprintf, __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";

/**
 *  Internal Dependencies
 */
import ChangelogSkeleton from "../../skeleton/ChangelogSkeleton/ChangelogSkeleton";
import { CHANGELOG_TAG_COLORS } from "../../Constants";

const Changelog = () => {
	/* global _UR_DASHBOARD_ */
	const { urRestApiNonce } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	const [changelogParsed, setChangelogParsed] = useState(false);
	const [changelogs, setChangelogs] = useState({});

	/**
	 *  Fetrch changelogs on component load.
	 */
	useEffect(() => {
		if (!changelogParsed) {
			const data = apiFetch({
				path: `user-registration/v1/changelog`,
				method: "GET",
				headers: {
					"X-WP-Nonce": urRestApiNonce
				}
			}).then((res) => {
				if (res.success) {
					setChangelogs(res.changelog);
					setChangelogParsed(true);
				}
			});
		}
	}, []);

	if (!changelogParsed) {
		return <ChangelogSkeleton />;
	}

	return (
		<>
			{changelogs?.map((changelog) => (
				<Box key={changelog.version} mb="7">
					<HStack justify="space-between">
						<Heading as="h4" fontSize="sm" fontWeight="semibold">
							{sprintf(__("Version %s"), changelog.version)}
						</Heading>
						<Text>{changelog.date}</Text>
					</HStack>
					<Box>
						{Object.entries(changelog.changes).map(
							([tag, changes], i) => (
								<Box
									key={`${changelog.version}${tag}${i}`}
									position="relative"
									_after={{
										bgColor:
											CHANGELOG_TAG_COLORS?.[
												tag.trim().toLowerCase()
											]?.bgColor ?? "gray",
										bottom: 0,
										content: '""',
										height: "full",
										left: "12px",
										position: "absolute",
										top: 0,
										width: "2px"
									}}
									mb="10"
									mt="8"
								>
									<Tag
										colorScheme={
											CHANGELOG_TAG_COLORS?.[
												tag.trim().toLowerCase()
											]?.scheme
										}
										position="sticky"
										zIndex={2}
										top="0"
										fontWeight="normal"
									>
										{tag}
									</Tag>
									<Box pt="10px">
										{changes.map((change, j) => (
											<Text
												key={`${changelog.version}${tag}${i}${j}`}
												pl="10"
												position="relative"
												mb="4"
												_after={{
													bgColor:
														CHANGELOG_TAG_COLORS?.[
															tag
																.trim()
																.toLowerCase()
														]?.bgColor,
													bgPosition: "50%",
													borderRadius: "50%",
													content: '""',
													height: "20px",
													width: "20px",
													position: "absolute",
													top: "50%",
													transform:
														"translateY(-50%)",
													left: "2px"
												}}
												_before={{
													color: CHANGELOG_TAG_COLORS?.[
														tag.trim().toLowerCase()
													]?.color,
													content: '"\\2713"',
													position: "absolute",
													left: "9px",
													top: "50%",
													transform:
														"translateY(-50%)",
													fontSize: "10px",
													fontWeight: "bold",
													zIndex: 1
												}}
											>
												{change}
											</Text>
										))}
									</Box>
								</Box>
							)
						)}
					</Box>
				</Box>
			))}
		</>
	);
};

export default Changelog;
