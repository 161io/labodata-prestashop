/**
 * Copyright (c) 161 SARL, https://161.io
 */

module.exports = function(grunt) {
  'use strict';

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    cssmin: {
      labodata: {
        files: [
          {
            expand: true,
            cwd: 'css/',
            src: ['**/*.css', '!**/*.min.css'],
            dest: 'css/',
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
            cwd: 'js/',
            src: ['**/*.js', '!**/*.min.js'],
            dest: 'js/',
            ext: '.min.js'
          }
        ]
      }
    },
    watch: {
      cssmin: {
        files: ['css/**/*.css', '!**/*.min.css'],
        tasks: 'cssmin'
      },
      uglify: {
        files: ['js/**/*.js', '!**/*.min.js'],
        tasks: 'uglify'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['cssmin', 'uglify']);

};
