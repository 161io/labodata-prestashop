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
          archive: 'labodata-1.0.0.zip'
        },
        files: [
          {
            expand: true,
            src: [
              '**',
              //'src/.htaccess',
              '!node_modules/**', '!test/**',
              '!*.js*', '!*.lock', '!config*.xml', '!labodata*.zip', '!phpunit.*'
            ],
            dest: 'labodata/'
          }
        ]
      }
    },
    replace: {
      labodata_build: {
        src: 'labodata.php',
        dest: './',
        replacements: [{
          from: /(\s+)\/\/(\$this->module_key\s*=)/,
          to: '$1$2'
        }]
      },
      labodata_restore: {
        src: 'labodata.php',
        dest: './',
        replacements: [{
          from: /(\s+)(\$this->module_key\s*=)/,
          to: '$1//$2'
        }]
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
  grunt.loadNpmTasks('grunt-text-replace');

  grunt.registerTask('default', ['cssmin', 'uglify']);
  grunt.registerTask('build_presta', ['default', 'replace:labodata_build', 'compress', 'replace:labodata_restore']);
  grunt.registerTask('build_github', ['default', 'replace:labodata_restore', 'compress']);

};
