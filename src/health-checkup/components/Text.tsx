import { Text as ChakraText, TextProps } from "@chakra-ui/react";

// This plugin's own global admin CSS sets `.user-registration-page p { color:
// #383838; font-size:14px; line-height:24px; }`, which — being a class+tag
// selector — outranks Chakra's single-class utility styles for anything
// rendered as a plain `<p>` (Chakra Text's default tag). That silently wins
// over any color/fontSize/lineHeight we set here. Default to `<div>` instead
// to sidestep the collision entirely; call sites that intentionally need an
// inline tag (`as="b"`, `as="code"`, `as="span"`, heading levels, ...) still
// get exactly that tag, since an explicit `as` in props overrides this default.
const Text = (props: TextProps) => <ChakraText as="div" {...props} />;

export default Text;
