const path = require("path");
const CopyPlugin = require("copy-webpack-plugin");
const isProd = process.env.NODE_ENV === "production";
const WebpackBar = !isProd ? require("webpackbar") : null;

module.exports = (env, argv) => {
	return {
		entry: {
			welcome: "./src/welcome/index.js",
			dashboard: "./src/dashboard/index.js",
			formblock: "./assets/js/admin/gutenberg/form-block.js",
			blocks: "./src/blocks/index.js",
			form_templates: "./src/form-templates/index.js",
			"divi-builder": "./src/widgets/divi-builder/index.js"
		},
		output: {
			path: path.resolve(__dirname + "/chunks"),
			publicPath: "/",
			filename: "[name].js"
		},
		devtool: isProd ? false : "source-map",
		resolve: {
			extensions: [".js", ".jsx", ".json"]
		},
		module: {
			rules: [
				{
					test: /\.(js|jsx)$/,
					exclude: /node_modules/,
					use: [
						{
							loader: "babel-loader",
							options: {
								presets: [
									"@babel/preset-env",
									"@babel/preset-react"
								]
							}
						},
						{
							loader: "eslint-loader"
						}
					]
				},
				{
					test: /\.s[ac]ss$/i,
					use: [
						// Creates `style` nodes from JS strings
						"style-loader",
						// Translates CSS into CommonJS
						"css-loader",
						// Compiles Sass to CSS
						"sass-loader"
					]
				},
				{
					test: /\.(gif|webp|svg)$/i,
					use: "url-loader"
				},
				{
					test: /\.(png|jpg|jpeg)$/i,
					use: [
						{
							loader: "file-loader"
						}
					]
				}
			]
		},
		plugins: [
			new CopyPlugin({
				patterns: [
					{
						from: "src/blocks/**/block.json",
						to({ absoluteFilename }) {
							return path.resolve(
								__dirname,
								"chunks",
								path.basename(path.dirname(absoluteFilename)),
								"block.json"
							);
						}
					}
				]
			}),
			...(!isProd && WebpackBar ? [new WebpackBar()] : [])
		],
		externals: {
			"@wordpress/blocks": ["wp", "blocks"],
			"@wordpress/components": ["wp", "components"],
			"@wordpress/block-editor": ["wp", "blockEditor"],
			"@wordpress/server-side-render": ["wp", "serverSideRender"],
			react: ["React"]
		}
	};
};
