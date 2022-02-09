const path = require("path");

module.exports = (env, argv) => {
	let production = argv.mode === "production";

	return {
		entry: ["/src/index.js", "./assets/js/admin/gutenberg/form-block.js"],
		output: {
			path: path.resolve(__dirname + "/build"),
			publicPath: "/",
			filename: "[name].js",
		},

		resolve: {
			extensions: [".js", ".jsx", ".json"],
		},
		module: {
			rules: [
				{
					test: /\.(js|jsx)$/,
					exclude: /node_modules/,
					use: ["babel-loader", "eslint-loader"],
				},
				{
					test: /\.s[ac]ss$/i,
					use: [
						// Creates `style` nodes from JS strings
						"style-loader",
						// Translates CSS into CommonJS
						"css-loader",
						// Compiles Sass to CSS
						"sass-loader",
					],
				},
			],
		},
	};
};
