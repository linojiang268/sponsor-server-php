var gulp = require('gulp'),
    browserify = require('browserify'),
    reactify = require('reactify'),
    source = require('vinyl-source-stream'),
    lessMap = require('gulp-less-sourcemap'),//less编译(替代gulp-less)并产生sourcemap
    rename = require('gulp-rename'),
    del = require('del'),//文件删除
    bs = require('browser-sync').create(),//静态服务器
    notify = require('gulp-notify'),
    path = require('path');

var SCRIPT_BASEDIR = __dirname + '/resources/scripts';

var LESS = './assets/less/*.less',
    CSS = './assets/css/*.css';

var handleError = function(){
    var args = Array.prototype.slice.call(arguments);
    notify.onError({
        title: 'compile error',
        message: '<%= error.message %>'
    }).apply(this,args);
    this.emit('close');
    this.emit('end');
};

var compileScript = function (filePath) {
    filePath = path.normalize(filePath);
    var index = filePath.indexOf(SCRIPT_BASEDIR);
    if (index !== 0) {
        console.warn('Won\'t compile script outside the base directory.\n');
        return;
    }

    filePath = filePath.substring(SCRIPT_BASEDIR.length + 1); // 1 == the length of the last slash
    // welcome/index/login.jsx -> welcome/index.js
    // welcome/common/index/login.jsx -> welcome/common/index.js
    // common/util.jsx         -> common/util.js
    // util.jsx  -> util.js

    var parts = filePath.split('/'),
        srcFile = filePath,
        destFile, destDir = '';
    switch (parts.length) {
        case 1:
            destFile = filePath;
            break;
        case 2:
            srcFile = path.join(SCRIPT_BASEDIR + '/' + filePath, '..', 'index.jsx');
            destFile = parts[1];
            destDir = parts[0];
            break;
        case 3:
            srcFile = path.join(SCRIPT_BASEDIR + '/' + filePath, '..', 'index.jsx');
            destFile = parts[1];
            destDir = parts[0];
            break;
        default:
            srcFile = path.join(SCRIPT_BASEDIR + '/' + filePath, '..', 'index.jsx');
            destFile = parts[parts.length - 2];
            destDir = parts.slice(0, parts.length - 2).join('/');
    }

    browserify(srcFile)
        .transform(reactify)
        .bundle()
        .pipe(source(path.basename(destFile, '.jsx') + '.js'))
        .pipe(notify('index.js compiled'))
        .pipe(gulp.dest('./public/scripts/' + destDir));
};

//清除目录
gulp.task('clean',function(){
    del(['tem/']);
});

//编译less
gulp.task('compile-less',function(){
    return gulp.src(LESS)
           .pipe(lessMap({
               sourceMap: {
                   sourceMapRootpath: LESS
               }
           }))
           .on("error", handleError)
           .pipe(gulp.dest('./assets/css'))
});

//编译脚本
gulp.task('watch',function(){
    gulp.watch(SCRIPT_BASEDIR + '/**/*.jsx', function (event) {
        compileScript(event.path);
    });
    gulp.watch(LESS, ['compile-less']);

    gulp.watch(CSS).on('change',function(){
        console.log('css refresh');
        bs.reload(CSS);
    });
    gulp.watch('./tem/**/*.js').on('change', function() {
        bs.reload();
    });
});

gulp.task('compile-scripts', function() {


    var sources = ['common', 'welcome/index'];
    for (var i in sources) {
        var dest = !~sources[i].indexOf('/') ? sources[i] : sources[i].substring(0, sources[i].lastIndexOf('/'));
        browserify('./resources/scripts/' + sources[i] + '/index.jsx')
            .transform(reactify)
            .bundle()
            .pipe(source('index.js'))
            .pipe(notify('index.js compiled'))
            .pipe(gulp.dest('./public/scripts/' + dest));
    }
});

gulp.task('default',['compile-scripts','compile-less'],function(){
    bs.init({
        proxy:"www.jhla.com"    
    });
    gulp.start('watch');
});
