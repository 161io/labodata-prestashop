/**
 * @copyright (c) 161 SARL, https://161.io
 */

module.exports = function(grunt) {
  'use strict';

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    compress: {
      labodata: {
        options: {
          archive: 'labodata-0.3.3.zip',
        },
        files: [
          {
            expand: true,
            src: [
              '**',
              //'src/.htaccess',
              '!bower_components/**', '!node_modules/**',
              '!*.js*', '!*.lock', '!config*.xml', '!labodata*.zip'
            ],
            dest: 'labodata/'
          }
        ]
      }
    },
    cssmin: {
      labodata: {
        files: [
          {
            expand: true,
            cwd: 'views/css/',
            src: ['**/*.css', '!**/*.min.css'],
            dest: 'views/css/',
            ext: '.min.css'
          }
        ]
      }
    },
    uglify: {
      labodata: {
        options: {
          output: {
            comments: 'some'
          }
        },
        files: [
          {
            expand: true,
            cwd: 'views/js/',
            src: ['**/*.js', '!**/*.min.js'],
            dest: 'views/js/',
            ext: '.min.js'
          }
        ]
      }
    },
    watch: {
      cssmin: {
        files: ['views/css/**/*.css', '!**/*.min.css'],
        tasks: 'cssmin'
      },
      uglify: {
        files: ['views/js/**/*.js', '!**/*.min.js'],
        tasks: 'uglify'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['cssmin', 'uglify']);
  grunt.registerTask('build', ['default', 'compress'])

};
