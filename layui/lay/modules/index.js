/**
  项目JS主入口
  以依赖layui的layer和form模块为例
**/    
layui.define(['layer', 'form'], function(exports){
  var layer = layui.layer
  ,form = layui.form;
  
  // layer.msg('Hello World x 号码错误');
  
  exports('index', {}); //注意，这里是模块输出的核心，模块名必须和use时的模块名一致
}); 

layui.config({
  dir: './layui/' //layui.js 所在路径（注意，如果是script单独引入layui.js，无需设定该参数。），一般情况下可以无视
  ,version: false //一般用于更新模块缓存，默认不开启。设为true即让浏览器不缓存。也可以设为一个固定的值，如：201610
  ,debug: false //用于开启调试模式，默认false，如果设为true，则JS模块的节点会保留在页面
  ,base: '' //设定扩展的Layui模块的所在目录，一般用于外部模块扩展
});

layui.define(function(exports){
  //do something
  
  exports('demo', function(){
    // alert('Hello World!');
  });
});