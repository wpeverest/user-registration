const fs = require("fs");

/** @param {import('grunt')} grunt */
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
				"!changelog.txt",
				"!release/**",
				...distIgnorePatterns
			],
			dest: "user-registration"
		}
	];

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		dirs: {
			js: "assets/js",
			css: "assets/css"
		},
		terser: {
			options: {
				output: {
					comments: /@license|@preserve|^!/
				},
				compress: {
					drop_console: false
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
					"<%= dirs.js %>/jquery-blockui/jquery.blockUI.min.js": [
						"<%= dirs.js %>/jquery-blockui/jquery.blockUI.js"
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
					]
				}
			}
		},
		sass: {
			options: {
				implementation: require("sass"),
				sourceMap: false
			},
			compile: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.css %>/",
						src: [
							"*.scss",
							"modules/**/*.scss",
							"ur-snackbar/*.scss"
						],
						dest: "<%= dirs.css %>/",
						ext: ".css"
					}
				]
			}
		},
		rtlcss: {
			generate: {
				expand: true,
				cwd: "<%= dirs.css %>",
				src: ["*.css", "!select2.css", "!*-rtl.css"],
				dest: "<%= dirs.css %>/",
				ext: "-rtl.css"
			}
		},
		cssmin: {
			minify: {
				expand: true,
				cwd: "<%= dirs.css %>/",
				src: ["*.css"],
				dest: "<%= dirs.css %>/",
				ext: ".css"
			}
		},
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
				tasks: ["terser"]
			}
		},
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
		compress: {
			withVersion: {
				options: {
					archive: "release/<%= pkg.name %>-<%= pkg.version %>.zip"
				},
				files: filesToCompress
			}
		},
		shell: {
			composerProd: {
				command: "composer install --no-dev --optimize-autoloader"
			}
		}
	});

	grunt.loadNpmTasks("grunt-sass");
	grunt.loadNpmTasks("grunt-rtlcss");
	grunt.loadNpmTasks("@lodder/grunt-postcss");
	grunt.loadNpmTasks("grunt-terser");
	grunt.loadNpmTasks("grunt-contrib-cssmin");
	grunt.loadNpmTasks("grunt-contrib-concat");
	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks("grunt-contrib-compress");
	grunt.loadNpmTasks("grunt-shell");

	grunt.registerTask("default", ["terser"]);

	grunt.registerTask("js", [
		"terser:admin",
		"terser:frontend",
		"terser:urComponents",
		"terser:modules",
		"terser:urSnackbar"
	]);

	grunt.registerTask("css", [
		"sass",
		"rtlcss",
		"postcss",
		"cssmin",
		"concat"
	]);

	grunt.registerTask("release", [
		"shell:composerProd",
		"sass",
		"rtlcss",
		"cssmin",
		"concat",
		"terser",
		"compress:withVersion"
	]);

	grunt.registerTask("dev", ["watch"]);
};
