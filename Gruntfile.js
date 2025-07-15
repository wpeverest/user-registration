/* jshint node:true */
const fs = require("fs");

module.exports = function (grunt) {
	const distIgnorePatterns = fs
		.readFileSync(".distignore", "utf-8")
		.split("\n")
		.filter((line) => line.trim() && !line.startsWith("#"))
		.map((line) => `!${line.trim()}`);

	// Define the files to be included in the zip archive.
	const filesToCompress = [
		{
			expand: true,
			cwd: "./",
			src: [
				"**",
				"!.*",
				"!*.md",
				"!*.zip",
				"!.*/**",
				"!sass/**",
				"!phpcs.xml",
				"!Gruntfile.js",
				"!package.json",
				"!renovate.json",
				"!composer.lock",
				"!node_modules/**",
				"!package-lock.json",
				"!webpack.config.js",
				"!tests/**",
				"!phpunit-watcher.yml.dist",
				"!phpunit.xml.dist",
				"!phpcs.xml.dist",
				"!changelog.txt",
				"!release/**",
				...distIgnorePatterns
			],
			dest: "user-registration" // Ensure this ends with a slash
		}
	];

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		// Setting folder templates.
		dirs: {
			js: "assets/js",
			css: "assets/css"
		},

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				jshintrc: ".jshintrc"
			},
			all: [
				"Gruntfile.js",
				"<%= dirs.js %>/admin/*.js",
				"!<%= dirs.js %>/admin/*.min.js",
				"<%= dirs.js %>/frontend/*.js",
				"!<%= dirs.js %>/frontend/*.min.js"
			]
		},

		// Sass linting with Stylelint.
		stylelint: {
			options: {
				stylelintrc: ".stylelintrc"
			},
			all: ["<%= dirs.css %>/*.scss", "!<%= dirs.css %>/select2.scss"]
		},

		// Minify all .js files.
		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false
				},
				output: {
					comments: /@license|@preserve|^!/
				}
			},
			admin: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/admin/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/admin/",
						ext: ".min.js"
					}
				]
			},
			frontend: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/frontend/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/frontend/",
						ext: ".min.js"
					}
				]
			},
			urComponents: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/ur-components/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/ur-components/",
						ext: ".min.js"
					}
				]
			},
			modules: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/modules/",
						src: ["**/*.js", "!**/*.min.js"],
						dest: "<%= dirs.js %>/modules/",
						ext: ".min.js"
					}
				]
			},
			urSnackbar: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/ur-snackbar/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/ur-snackbar/",
						ext: ".min.js"
					}
				]
			},
			vendor: {
				files: {
					"<%= dirs.js %>/inputmask/jquery.inputmask.bundle.min.js": [
						"<%= dirs.js %>/inputmask/jquery.inputmask.bundle.js"
					],
					"<%= dirs.js %>/jquery-blockui/jquery.jquery.blockUI.min.js":
						[
							"<%= dirs.js %>/jquery-blockui/jquery.jquery.blockUI.js"
						],
					"<%= dirs.js %>/tooltipster/tooltipster.bundle.min.js": [
						"<%= dirs.js %>/tooltipster/tooltipster.bundle.js"
					],
					"<%= dirs.js %>/perfect-scrollbar/perfect-scrollbar.min.js":
						[
							"<%= dirs.js %>/perfect-scrollbar/perfect-scrollbar.js"
						],
					"<%= dirs.js %>/selectWoo/selectWoo.min.js": [
						"<%= dirs.js %>/selectWoo/selectWoo.js"
					],
					"<%= dirs.js %>/sweetalert2/sweetalert2.min.js": [
						"<%= dirs.js %>/sweetalert2/sweetalert2.js"
					],
					"<%= dirs.js %>/sweetalert2/sweetalert2.min.js": [
						"<%= dirs.js %>/sweetalert2/sweetalert2.js"
					]
				}
			}
		},

		// Compile all .scss files.
		sass: {
			options: {
				sourceMap: false,
				implementation: require("sass")
			},
			compile: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.css %>/",
						src: ["*.scss", "modules/**/*.scss", "ur-snackbar/*.scss"], // Include the modules and ur-snackbar directories
						dest: "<%= dirs.css %>/",
						ext: ".css"
					}
				]
			}
		},

		// Generate all RTL .css files
		rtlcss: {
			generate: {
				expand: true,
				cwd: "<%= dirs.css %>",
				src: ["*.css", "!select2.css", "!*-rtl.css"],
				dest: "<%= dirs.css %>/",
				ext: "-rtl.css"
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: "<%= dirs.css %>/",
				src: ["*.css"],
				dest: "<%= dirs.css %>/",
				ext: ".css"
			}
		},

		// Concatenate select2.css onto the admin.css files.
		concat: {
			admin: {
				files: {
					"<%= dirs.css %>/admin.css": [
						"<%= dirs.css %>/select2.css",
						"<%= dirs.css %>/admin.css"
					],
					"<%= dirs.css %>/admin-rtl.css": [
						"<%= dirs.css %>/select2.css",
						"<%= dirs.css %>/admin-rtl.css"
					]
				}
			}
		},

		// Watch changes for assets.
		watch: {
			css: {
				files: [
					"<%= dirs.css %>/**/*.scss",
					"<%= dirs.css %>/modules/**/*.scss",
					"<%= dirs.css %>/ur-snackbar/*.scss"
				],
				tasks: ["sass", "rtlcss", "cssmin", "concat"]
			},
			js: {
				files: [
					"<%= dirs.js %>/admin/*js",
					"<%= dirs.js %>/modules/**/**/*js",
					"<%= dirs.js %>/frontend/*js",
					"<%= dirs.js %>/ur-snackbar/*js",
					"!<%= dirs.js %>/admin/*.min.js",
					"!<%= dirs.js %>/frontend/*.min.js",
					"!<%= dirs.js %>/ur-snackbar/*.min.js"
				],
				tasks: ["jshint", "uglify"]
			}
		},

		// PHP Code Sniffer.
		phpcs: {
			options: {
				bin: "vendor/bin/phpcs"
			},
			dist: {
				src: [
					"**/*.php", // Include all files
					"!includes/libraries/**", // Exclude libraries/
					"!node_modules/**", // Exclude node_modules/
					"!vendor/**" // Exclude vendor/
				]
			}
		},

		// Autoprefixer.
		postcss: {
			options: {
				processors: [
					require("autoprefixer")({
						overrideBrowserslist: ["> 0.1%", "ie 8", "ie 9"]
					})
				]
			},
			dist: {
				src: ["<%= dirs.css %>/*.css"]
			}
		},

		// Generate POT file for translations
		makepot: {
			target: {
				options: {
					domainPath: "/languages/",
					mainFile: "user-registration.php",
					potFilename: "user-registration.pot",
					potHeaders: {
						poedit: true,
						"x-poedit-keywordslist": true
					},
					type: "wp-plugin",
					exclude: ["node_modules/.*", "tests/.*", "vendor/.*"]
				}
			}
		},

		// Compress files and folders.
		compress: {
			withVersion: {
				options: {
					archive: "release/<%= pkg.name %>-<%= pkg.version %>.zip"
				},
				files: filesToCompress
			}
			// withoutVersion: {
			// 	options: {
			// 		archive: "release/<%= pkg.name %>.zip"
			// 	},
			// 	files: filesToCompress
			// }
		},
		shell: {
			composerProd: {
				command: "composer install --no-dev --optimize-autoloader"
			}
		}
	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks("grunt-sass");
	grunt.loadNpmTasks("grunt-phpcs");
	grunt.loadNpmTasks("grunt-rtlcss");
	grunt.loadNpmTasks("grunt-postcss");
	grunt.loadNpmTasks("grunt-stylelint");
	grunt.loadNpmTasks("grunt-contrib-jshint");
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-cssmin");
	grunt.loadNpmTasks("grunt-contrib-concat");
	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks("grunt-contrib-compress");
	grunt.loadNpmTasks("grunt-shell");

	// Register tasks.
	grunt.registerTask("default", ["uglify"]);

	grunt.registerTask("js", [
		// 'jshint',
		"uglify:admin",
		"uglify:frontend",
		"uglify:urComponents",
		"uglify:modules",
		"uglify:urSnackbar"
	]);
	grunt.registerTask("css", [
		"sass",
		"rtlcss",
		"postcss",
		"cssmin",
		"concat"
	]);

	// Register tasks
	grunt.registerTask("release", [
		"shell:composerProd",
		"sass",
		"rtlcss",
		"cssmin",
		"concat",
		"uglify",
		// "compress:withoutVersion",
		"compress:withVersion"
	]);
	grunt.registerTask("dev", ["watch"]);
};
