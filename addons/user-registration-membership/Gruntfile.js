/* jshint node:true */
module.exports = function (grunt) {
	"use strict";

	grunt.initConfig({
		// Store project settings.
		pkg: grunt.file.readJSON("package.json"),

		// Setting folder templates.
		dirs: {
			js: "assets/js",
			css: "assets/css",
		},

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				jshintrc: ".jshintrc",
			},
			all: ["Gruntfile.js"],
		},

		// Sass linting with Stylelint.
		stylelint: {
			options: {
				stylelintrc: ".stylelintrc",
			},
			all: ["<%= dirs.css %>/*.scss"],
		},

		// Minify all .js files.
		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false,
				},
				output: {
					comments: /@license|@preserve|^!/,
				},
			},
			admin: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/admin/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/admin/",
						ext: ".min.js",
					},
				],
			},
			frontend: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.js %>/frontend/",
						src: ["*.js", "!*.min.js"],
						dest: "<%= dirs.js %>/frontend/",
						ext: ".min.js",
					},
				],
			},
		},

		// Compile all .scss files.
		sass: {
			options: {
				sourcemap: "none",
				implementation: require("sass"),
			},
			compile: {
				files: [
					{
						expand: true,
						cwd: "<%= dirs.css %>/",
						src: ["*.scss"],
						dest: "<%= dirs.css %>/",
						ext: ".css",
					},
				],
			},
		},

		// Generate all RTL .css files
		rtlcss: {
			generate: {
				expand: true,
				cwd: "<%= dirs.css %>",
				src: ["*.css", "!*-rtl.css"],
				dest: "<%= dirs.css %>/",
				ext: "-rtl.css",
			},
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: "<%= dirs.css %>/",
				src: ["*.css"],
				dest: "<%= dirs.css %>/",
				ext: ".css",
			},
		},

		// Watch changes for assets.
		watch: {
			css: {
				files: ["<%= dirs.css %>/*.scss"],
				tasks: ["sass", "rtlcss", "cssmin"],
			},
			js: {
				files: [
					"<%= dirs.js %>/admin/*js",
					"<%= dirs.js %>/frontend/*js",
					"<%= dirs.js %>/ur-snackbar/*js",
					"<%= dirs.js %>/pro/*js",
					"!<%= dirs.js %>/admin/*.min.js",
					"!<%= dirs.js %>/frontend/*.min.js",
					"!<%= dirs.js %>/ur-snackbar/*.min.js",
					"<%= dirs.js %>/pro/*.min.js",
				],
				tasks: ["jshint", "uglify"],
			},
		},
		// PHP Code Sniffer.
		phpcs: {
			options: {
				bin: "vendor/bin/phpcs",
			},
			dist: {
				src: [
					"**/*.php", // Include all files
					"!includes/libraries/**", // Exclude libraries/
					"!node_modules/**", // Exclude node_modules/
					"!vendor/**", // Exclude vendor/
				],
			},
		},

		// Autoprefixer.
		postcss: {
			options: {
				processors: [
					require("autoprefixer")({
						browsers: ["> 0.1%", "ie 8", "ie 9"],
					}),
				],
			},
			dist: {
				src: ["<%= dirs.css %>/*.css"],
			},
		},

		// Compress files and folders.
		compress: {
			options: {
				archive: "<%= pkg.name %>.zip",
			},
			files: {
				src: [
					"**",
					"!.*",
					"!*.md",
					"!*.zip",
					"!.*/**",
					"!phpcs.xml",
					"!Gruntfile.js",
					"!package.json",
					"!composer.json",
					"!composer.lock",
					"!node_modules/**",
					"!package-lock.json",
				],
				dest: "<%= pkg.name %>",
				expand: true,
			},
		},
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
	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks("grunt-contrib-compress");

	// Register tasks
	grunt.registerTask("default", ["js"]);

	grunt.registerTask("js", ["uglify:admin", "uglify:frontend"]);

	grunt.registerTask("css", ["sass", "rtlcss", "postcss", "cssmin"]);

	// Only an alias to 'default' task.
	grunt.registerTask("dev", ["default"]);

	grunt.registerTask("zip", ["dev", "compress"]);
};
