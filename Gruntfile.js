module.exports = function(grunt) {

	// 1. All configuration goes here
	grunt.initConfig({
		pkg : grunt.file.readJSON('package.json'),
		phpunit: {
		    classes: {
		        dir: 'tests/php/'
		    },
		    options: {
		        bin: 'vendor/bin/phpunit',
		        bootstrap: 'tests/php/phpunit.php',
		        colors: true
		    }
		},
		watch : {
			javascript : {
				files : ['fileadmin/templates/js/src-*/*.js'],
				tasks : ['concat', 'uglify'],
				options : {
					spawn : false
				}
			},
			geshi : {
				files : ['typo3conf/ext/jkphl_blog/Resources/Public/Js/Geshi.js'],
				tasks : ['uglify'],
				options : {
					spawn : false
				}
			},
			images : {
				files : ['fileadmin/user_upload/**/*.{png,jpg,gif}', 'fileadmin/images/*.{png,jpg,gif}'],
				tasks : ['imagemin'],
				options : {
					spawn : false
				}
			}
		}

	});

	// 3. Where we tell Grunt we plan to use this plug-in.
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-phpunit');

	// 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
	grunt.registerTask('default', ['phpunit']);

}