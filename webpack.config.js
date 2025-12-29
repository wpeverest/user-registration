const path = require("path");
const webpack = require("webpack");
const CopyPlugin = require("copy-webpack-plugin");
const isProd = process.env.NODE_ENV === "production";
const WebpackBar = !isProd ? require("webpackbar") : null;
const ReactRefreshWebpackPlugin = !isProd ? require("@pmmmwh/react-refresh-webpack-plugin") : null;

module.exports = (env, argv) => {
	const isDevServer = process.env.WEBPACK_SERVE === "true";
	const isDev = !isProd;

	return {
		mode: isDev ? "development" : "production",
		entry: {
			welcome: "./src/welcome/index.tsx",
			dashboard: "./src/dashboard/index.js",
			blocks: "./src/blocks/index.js",
			formblock: "./assets/js/admin/gutenberg/form-block.js",
			form_templates: "./src/form-templates/index.js",
			"divi-builder": "./src/widgets/divi-builder/index.js",
			"content-access-rules": "./src/content-restriction/index.js"
		},
		output: {
			path: path.resolve(__dirname, "chunks"),
			publicPath: isDevServer ? "http://localhost:3000/" : "/wp-content/plugins/user-registration/chunks/",
			filename: "[name].js"
		},
		devtool: isProd ? false : "source-map",
		ignoreWarnings: [
			/Module not found/,
			/export 'default'.*was not found/,
			/was not found in/,
		],
		devServer: {
			static: {
				directory: path.join(__dirname, "chunks")
			},
			hot: true,
			liveReload: true,
			port: 3000,
			host: "localhost",
			allowedHosts: "all",
			headers: {
				"Access-Control-Allow-Origin": "*",
				"Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
				"Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
			},
			devMiddleware: {
				writeToDisk: true
			},
			client: {
				overlay: true,
				progress: true,
				webSocketURL: {
					hostname: "localhost",
					pathname: "/ws",
					port: 3000
				}
			},
			proxy: [
				{
					context: ["/wp-admin", "/wp-content", "/wp-includes", "/wp-json"],
					target: "http://localhost:8000",
					changeOrigin: true
				}
			]
		},
		resolve: {
			extensions: [".js", ".jsx", ".ts", ".tsx", ".json"]
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
									["@babel/preset-react", { runtime: "automatic" }]
								],
								plugins: [
									isDev && isDevServer && require.resolve("react-refresh/babel")
								].filter(Boolean)
							}
						}
					]
				},
				{
					test: /\.(ts|tsx)$/,
					exclude: /node_modules/,
					use: [
						{
							loader: "babel-loader",
							options: {
								presets: [
									"@babel/preset-env",
									["@babel/preset-react", { runtime: "automatic" }],
									"@babel/preset-typescript"
								],
								plugins: [
									isDev && isDevServer && require.resolve("react-refresh/babel")
								].filter(Boolean)
							}
						}
					]
				},
				{
					test: /\.s[ac]ss$/i,
					use: ["style-loader", "css-loader", "sass-loader"]
				},
				{
					test: /\.(gif|webp|svg)$/i,
					type: "asset/inline"
				},
				{
					test: /\.(png|jpg|jpeg)$/i,
					type: "asset/resource"
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
			...(isDev && WebpackBar ? [new WebpackBar()] : []),
			...(isDev && isDevServer && ReactRefreshWebpackPlugin ? [
				new ReactRefreshWebpackPlugin({
					overlay: false
				})
			] : [])
		],
		externals: {
			"@wordpress/blocks": ["wp", "blocks"],
			"@wordpress/components": ["wp", "components"],
			"@wordpress/block-editor": ["wp", "blockEditor"],
			"@wordpress/server-side-render": ["wp", "serverSideRender"],
			react: "React",
			"react-dom": "ReactDOM",
			jquery: "jQuery",
			$: "jQuery"
		}
	};
};
