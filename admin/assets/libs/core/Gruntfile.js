module.exports = function(grunt) {
    
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'), //load configuration

        concat: {
            'default': {
                files: {
                    'hc-full.js': ['src/hc.js','src/*','src/ui/init.js','src/ui/*'],
                    'hc-core.js':['src/hc.js', 'src/hc.loc.js','src/hc.net.js','src/ui/init.js','src/ui/element-parser.js'],
                    'hc-ui.js':['src/hc.js', 'src/hc.loc.js','src/hc.net.js','src/ui/init.js','src/ui/*']
                }
            }
        },
 
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %>\nAuthor:<%= pkg.author %>\nDescription:<%= pkg.description %>\nCreated:<%= grunt.template.today("dd-mm-yyyy HH:MM:ss") %> */\n',

                mangle: {
                    except: ['jQuery', 'Backbone']
                }
            },
            full:{
                files: {
                    'hc-full.min.js': ['hc-full.js']
                }
            },
            core:{
                files: {
                    'hc-core.min.js':['hc-core.js']
                }
            },
            ui:{
                files: {
                    'hc-ui.min.js':['hc-ui.js']
                }
            }
        }
 
    });
    grunt.loadNpmTasks('grunt-contrib-concat'); // which NPM packages to load
    grunt.loadNpmTasks('grunt-contrib-uglify'); // which NPM packages to load

    grunt.registerTask('core'); 
    grunt.registerTask('ui'); 
    grunt.registerTask('full'); 

    grunt.registerTask('default', ['concat','uglify']); // which packages to run

};