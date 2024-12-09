import React, { useState, useCallback } from "react";
import {
  Box,
  VStack,
  HStack,
  Text,
  Spacer,
  Input,
  InputLeftElement,
  InputGroup,
  Badge,
  CardHeader,
  CardFooter,
  Button,
  Card,
  Heading,
} from "@chakra-ui/react";
import { IoSearchOutline } from "react-icons/io5";
import debounce from "lodash.debounce";
import { __ } from "@wordpress/i18n";

const Sidebar = React.memo(({ categories, selectedCategory, onCategorySelect, onSearchChange }) => {
  const [searchTerm, setSearchTerm] = useState("");

  const debouncedSearchChange = useCallback(
    debounce((value) => {
      onSearchChange(value);
    }, 300),
    [onSearchChange]
  );

  const handleSearchChange = (e) => {
    const value = e.target.value;
    setSearchTerm(value);
    debouncedSearchChange(value);
  };

  const favorites = categories.find((cat) => cat.name === "Favorites");

  const orderedCategories =
    favorites && favorites.count > 0
      ? [favorites, ...categories.filter((cat) => cat.name !== "Favorites")]
      : categories;

  return (
    <Box>
      <InputGroup mb="26px">
        <InputLeftElement
          pointerEvents="none"
          padding="0px 0px 0px 8px"
          borderRadius="8px"
          borderColor="#B0B0B0"
        >
          <IoSearchOutline style={{ width: "18px", height: "18px" }} color="#737373" />
        </InputLeftElement>
        <Input
          placeholder={__("Search Templates", "user-registration")}
          value={searchTerm}
          onChange={handleSearchChange}
          _focus={{
            borderColor: "#7545BB",
            outline: "none",
            boxShadow: "none",
          }}
        />
      </InputGroup>

      <VStack align="stretch" gap="2px">
        {orderedCategories.map((category) => (
          <HStack
            key={category.name}
            p="12px"
            _hover={{
              bg: "#F7F4FB",
              "& > .badge": {
                bg: selectedCategory === category.name ? "#FFFFFF" : "#FFFFFF",
              },
            }}
            borderRadius="md"
            cursor="pointer"
            bg={selectedCategory === category.name ? "#F7F4FB" : "transparent"}
            onClick={() => onCategorySelect(category.name)}
          >
            <Text
              color={selectedCategory === category.name ? "#7545BB" : ""}
              fontWeight="semibold"
              margin="0px"
            >
              {category.name}
            </Text>
            <Spacer />
            <Badge
              className="badge"
              display="flex"
              alignItems="center"
              justifyContent="center"
              width="32px"
              height="32px"
              padding="0px"
              borderRadius="8px"
              color={selectedCategory === category.name ? "#7545BB" : ""}
              bg={selectedCategory === category.name ? "white" : "#F2F2F2"}
            >
              {category.count}
            </Badge>
          </HStack>
        ))}
        <Card
          align="center"
          bg="linear-gradient(90.62deg, rgba(76, 21, 155, 0.7) 0.2%, rgba(76, 21, 155, 0.7) 0.21%, rgba(140, 100, 198, 0.7) 99.25%)"
          padding="40px 24px"
          marginTop="26px"
        >
          <CardHeader padding="0px">
            <Heading
              fontSize="18px"
              color="white"
              lineHeight="28px"
              padding="0px"
              margin="0px 0px 20px"
              textAlign="center"
            >
              {__("Can't Find The Form Template You Need?", "user-registration")}
            </Heading>
          </CardHeader>
          <CardFooter padding="0" width="100%">
            <a
              href="https://everestforms.net/request-template"
              target="_blank"
              rel="noopener noreferrer"
              style={{ width: "inherit" }}
            >
              <Button
                backgroundColor="#FFFFFF"
                color="#7545BB"
                padding="12px 10px"
                borderRadius="4px"
                width="inherit"
              >
                {__("Request Template", "user-registration")}
              </Button>
            </a>
          </CardFooter>
        </Card>
      </VStack>
    </Box>
  );
});

export default Sidebar;
