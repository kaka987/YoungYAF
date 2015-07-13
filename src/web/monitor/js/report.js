$(function () {
    // 放大报表
    var serviceObj;
    var zoomDynamicInterval;
    var zoomService;
    var zoomServiceOptions;
    $('button.zoom').on('click', function() {
		var zoomService = $(this).attr('data-service');
        var zoomServiceOptions = eval('(' + zoomService + 'Options)');
        zoomServiceOptions.chart.width = '960';
        zoomServiceOptions.chart.height = '580';
        zoomServiceOptions.chart.renderTo = 'chartPopup';
        $("#chartPopup").attr('data-service', zoomService);

        if(zoomService == 'accessDynamic') {
          maxChartData = chartData;
          zoomServiceOptions.chart.events.load = function() {
                // set up the updating of the chart each second
                var series = this.series[0];
                zoomDynamicInterval = setInterval(function() {
                    var obj = maxChartData.shift();
                    var x = parseInt(obj.x),
                        y = obj.y;
                    series.addPoint([x, y], true, true);
                }, 1000);
            }
        }

        serviceObj = new Highcharts.Chart(zoomServiceOptions);

    	$.magnificPopup.open({
		  items: {
		    src: '#chartPopup'
		  },
		  type: 'inline',
		  callbacks: {
		  	close: function() {
				if(zoomService == 'accessDynamic') {
					clearInterval(zoomDynamicInterval);
					zoomServiceOptions.chart.events.load = null;
				}
				serviceObj = null;
		  	}
		  }
		});

    });


	// 日期插件
	// $('#startDate').datepicker({
	// format: 'yyyy-mm-dd',
	//       weekStart: 1,
	//       autoclose: true,
	//       todayBtn: 'linked',
	//       language: 'zh-CN'
	// });

	// 当前时间毫秒
	var time = (new Date()).getTime();
	// 当前时间秒
	var timeSecond = parseInt(time.toString().substring(0, 10));

	// 报表参数初始化
	var accessThrend = null;
	var accessDynamic = null;
	var errorTopTen = null;
	var ripTopTen = null;
	var rtcTopTen = null;
	var errorThrend = null;

	// 访问趋势配置
	var accessThrendSeries = [];
	// var accessThrendCategories = ["0点","1点","2点","3点","4点","5点","6点","7点","8点","9点","10点","11点","12点","13点","14点","15点","16点","17点","18点","19点","20点","21点","22点","23点"];
	var accessThrendCategories = [];
	// 5分钟请求一次
	var accessThrendRequestTime = 60000 * 5;

	// 错误TOP10配置
	var errorTopTenSeries = [];
  	var errorTopTenCategories = [];

	// 动态报表数据池
	var chartData = [];
	var maxChartData = [];

	// 报表全局设置
	Highcharts.setOptions({
	  global: {
	      useUTC: false
	  },
	  lang: {
	      numericSymbols: null
	  },
	  credits: {
	      enabled: false
	  },
	  series: {
	    showInLegend: false
	  }
	});

	// 访问趋势报表配置
	var accessThrendOptions = {
	  chart: {
	    renderTo:'accessThrend',
	  },
	  title: {
	      text: null
	  },
	  subtitle: {
	      text: '',
	  },
	  xAxis: {
	      labels:{
	        // step:2,
	        // rotation: 90
	      },
	      tickInterval: 1
	  },
	  yAxis: {
	      title: {
	          text: '次数'
	      }
	  },
	  tooltip: {
	      formatter: function() {
	            return '时间: ' + this.x +'点' + String(this.point.text) + '<br/>'+
	            this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
	        }
	  }
	}

	// 访问趋势报表运行
	$.ajax({
	    url:'/monitorapi/lbnginx/getData?service=accessThrend&todayOnly=0',
	    type:'get',
	    dataType:'json',
	    success: function(result) {
	      if(result.series.toDay.length) {
	        accessThrendSeries[0] = { 
	            'name': '今天',
	            'data': [],
	            'marker' : {
	                'enabled': false
	            },
	            'shadow': true,
	            'color': '#7cb5ec'
	          };
	        var toDayLen = result.series.toDay.length - 1;
	        var text = '';
	        $.each(result.series.toDay, function(i,n){
	          if(toDayLen == i) {
	          	var nextTime = n.time == 23 ? 0 : parseInt(n.time) + 1;
	          	text = '到' + nextTime + '点';
	          }
	          accessThrendSeries[0].data.push({
	          		x: parseInt(n.time),
	          		y: parseInt(parseInt(n.num)),
	          		text: text
	        	});
	        });

	        if(result.series.yestDay.length) {
	          accessThrendSeries[1] = { 
	            'name': '昨天',
	            'data': [],
	            'marker' : {
	                'enabled': false
	            },
	            'shadow': true,
	            'color': '#999999'
	          };
	          $.each(result.series.yestDay, function(i,n){
	            accessThrendSeries[1].data.push({
		        		x: parseInt(n.time),
		        		y: parseInt(parseInt(n.num)),
		        		text: ''
		        	});
	          	});
	        }
	      }

	      // 运行报表
	      accessThrendOptions.xAxis.categories = accessThrendCategories;
	      accessThrendOptions.series = accessThrendSeries;
	      accessThrend = new Highcharts.Chart(accessThrendOptions);
	    }
	});

    // 访问趋势请求新数据，每5秒一次
    setInterval(function() {
		$.ajax({
		    url:'/monitorapi/lbnginx/getData?service=accessThrend&todayOnly=0',
		    type:'get',
		    dataType:"json",
		    success: function(result) {
		    	var toDayData = [];
		    	var yestDayData = [];
		    	if(result.series.toDay.length) {
		    		var toDayLen = result.series.toDay.length - 1;
	        		var text = '';
			        $.each(result.series.toDay, function(i,n){
			          if(toDayLen == i) {
			          	var nextTime = n.time == 23 ? 0 : parseInt(n.time) + 1;
			          	text = '到' + nextTime + '点';
			          }
			          toDayData.push({
			        		x: parseInt(n.time),
			        		y: parseInt(parseInt(n.num)),
			        		text: text
			        	});
			        });

			        accessThrendSeries[0].data = toDayData;

			        accessThrend.series[0].update({
		        		data: accessThrendSeries[0].data
		    	  	});

			        if(result.series.yestDay.length) {
						$.each(result.series.yestDay, function(i,n){
							yestDayData.push({
				        		x: parseInt(n.time),
				        		y: parseInt(parseInt(n.num)),
				        		text: ''
				        	});
						});
						accessThrendSeries[1].data = yestDayData;
						accessThrend.series[1].update({
			        		data: accessThrendSeries[1].data
			    	  	});
			      	}
		      
		    	}
		    }
		});

	    

	  }, accessThrendRequestTime);


	// 访问动态报表配置
	var accessDynamicOptions = {
	  chart: {
	      renderTo: 'accessDynamic',
	      events: {
	          load: function() {
	              // set up the updating of the chart each second
	              var series = this.series[0];
	              setInterval(function() {
	                  var obj = chartData.shift();
	                  var x = parseInt(obj.x),
	                      y = obj.y;
	                  series.addPoint([x, y], true, true);
	              }, 1000);
	          }
	      }
	  }
	  ,title: {
	      text: null
	  },
	  xAxis: {
	      type: 'datetime',
	  },
	  yAxis: {
	      title: {
	          text: '次数'
	      },
	      plotLines: [{
	          value: 0,
	          width: 1,
	          color: '#808080'
	      }],
	      maxPadding: 1.5
	  },
	  tooltip: {
	      formatter: function() {
	              return '时间: ' + Highcharts.dateFormat('%H:%M:%S', this.x) +'<br/>'+
	              this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
	      }
	  },
	  legend: {
	      enabled: false
	  },
	  plotOptions: {
	      area: {
	          fillColor: {
	              linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
	              stops: [
	                  [0, Highcharts.getOptions().colors[0]],
	                  [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
	              ]
	          },
	          lineWidth: 1,
	          marker: {
	              enabled: false
	          },
	          shadow: false,
	          states: {
	              hover: {
	                  lineWidth: 1
	              }
	          },
	          threshold: null
	      }
	  },
	  series: [{
	      type: 'area',
	      name: '次数'
	    }]
	}

	// 第一次填充数据池
	var tempTime = (new Date()).getTime();
	var tempSecond = parseInt(tempTime.toString().substring(0, 10));
	// 获取后90条，延迟30秒，90条=90秒
	tempSecond = tempSecond - 120;
	$.ajax({
	  url:'http://10.2.255.19/?current_time=' + tempSecond + '&service=access_dynamic&limit=90',
	  type:'get',
	  dataType:"json",
	  success: function(result) {
	      var count = 0;
	      var accessDynamicData = [];
	      for (var i in result.data) {
	        var n = result.data[i];
	        if(count > 59) {
	            chartData.push({
	                x: n.time + '000',
	                y: n.num
	            });
	            maxChartData.push({
	                x: n.time + '000',
	                y: n.num
	            });
	          }
	          if(count < 60) {
	            accessDynamicData.push({
	                x: n.time + '000',
	                y: n.num
	            });
	          }
	          
	          count++;
	      };
	      if(count == 90) {
	        accessDynamicOptions.series[0].data = accessDynamicData;
	        accessDynamic = new Highcharts.Chart(accessDynamicOptions);
	      }
	  }
	});


	// 30秒补充一次数据池
	setInterval(function() {
	var tempTime = (new Date()).getTime();
	var tempSecond = parseInt(tempTime.toString().substring(0, 10));
	// 获取后30条，延迟30秒，30条=30秒
	tempSecond = tempSecond - 60;
	      $.ajax({
	        url:'http://10.2.255.19/?current_time=' + tempSecond + '&service=access_dynamic&limit=30',
	        type:'get',
	        dataType:"json",
	        success: function(result) {
	            $.each(result.data, function(i,n){
	                chartData.push({
	                    x: n.time + '000',
	                    y: n.num
	                });
	                maxChartData.push({
	                    x: n.time + '000',
	                    y: n.num
	                });
	            });
	        }
	    });
	  }, 30000);


	// 错误次数TOP10报表配置
	var errorTopTenOptions = {
	  chart: {
	      type: 'column',
	      renderTo:'errorTopTen',
	  },
	  title: {
	      text: null
	  },
	  xAxis: {
	  },
	  yAxis: {
	      min: 0,
	      title: {
	          text: '次数'
	      }
	  },
	  tooltip: {
	      formatter: function() {
	              return '主机: ' + this.point.host +'<br/>'+ 
	              '路径: ' + this.point.path +'<br/>'+ 
	              '代码: ' + this.point.code +'<br/>'+ 
	              this.series.name + ': ' + Highcharts.numberFormat(this.y, 0) + '次';
	      }
	  },
	  plotOptions: {
	      column: {
	          pointPadding: 0.2,
	          borderWidth: 0
	      }
	  },
	  legend: false,
	  series: [{
	      name: "次数",
	      shadow: true,
	      color: '#910000'
	  }]
	}

	// 错误次数TOP10报表运行
	$.ajax({
	    url:'/monitorapi/lbnginx/getData?service=errorTopTen',
	    type:'get',
	    dataType:"json",
	    success: function(result) {
	    	var errorTopTenData = [];
			$.each(result.data, function(i,n){
				errorTopTenCategories.push(n.code);
				errorTopTenData.push({
					y: parseInt(n.num),
					x: i,
					path: n.path,
					host: n.host,
					code: n.code
				});
			});
			// 运行报表
			errorTopTenOptions.xAxis.categories = errorTopTenCategories;
			errorTopTenOptions.series[0].data = errorTopTenData;
			errorTopTen = new Highcharts.Chart(errorTopTenOptions);
		}
	});

	// 错误趋势报表配置
	var errorThrendOptions = {
	  chart: {
	    renderTo:'errorThrend',
	  },
	  title: {
	      text: null
	  },
	  subtitle: {
	      text: '',
	  },
	  xAxis: {
	      // categories: ["0点","1点","2点","3点","4点","5点","6点","7点","8点","9点","10点","11点","12点","13点","14点","15点","16点","17点","18点","19点","20点","21点","22点","23点"],
	      labels:{ 
	          // step:2,
	          // rotation: 90
	      },
	      tickInterval: 1
	  },
	  yAxis: {
	      title: {
	          text: '次数'
	      }
	  },
	  tooltip: {
	      formatter: function() {
	              return '时间: ' + this.x +'点<br/>'+
	              this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
	      }
	  }
	}

	// 错误趋势报表运行
	$.ajax({
	    url:'/monitorapi/lbnginx/getData?service=errorThrend',
	    type:'get',
	    dataType:"json",
	    success: function(result) {
	      var errorThrendSeries = [];
	      var i = 0;
	      for(var n in result.series) {
	      	var node = result.series[n];
	      	var data = [];
	      	for(var f in node) {
	      		data.push({
	      			x: parseInt(node[f].time),
	      			y: parseInt(node[f].num)
	      		});
	      	}
	      	errorThrendSeries[i] = {
	      		'name': n,
	      		'data': data,
	      		'marker': { 'enabled': false },
	      		'shadow': true
	      	}
	      	i++;
	      }

	      // 运行报表
	      errorThrendOptions.series = errorThrendSeries;
			  errorThrend = new Highcharts.Chart(errorThrendOptions);
	    }
	});

	// 响应耗时TOP10报表配置
	var rtcTopTenOptions = {
	  chart: {                                                           
	      type: 'bar',
	      renderTo:'rtcTopTen',
	  },
	  title: {                                                           
	      text: null                
	  },                                                              
	  xAxis: {
	      title: {                                                       
	          text: "IP"                                                 
	      }                                                              
	  },                                                                 
	  yAxis: {
	      title: {
	        text: "秒"
	      }                                        
	  },                                                                 
	  tooltip: {                                                         
	      formatter: function() {
	              return 'IP: ' + this.x +'<br/>'+
	              this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '秒';
	      }
	  },                                                                 
	  plotOptions: {                                                     
	      bar: {                                                         
	          dataLabels: {                                              
	              enabled: true                                          
	          }                                                          
	      }                                                              
	  },                                                                 
	  legend: false,                                                                 
	  credits: {                                                         
	      enabled: false                                                 
	  },                                                                 
	  series: [{
	      name: "耗时",
	      shadow: true,
	  }]
	}

	// 响应耗时TOP10报表运行
	$.ajax({
	    url:'/monitorapi/lbnginx/getData?service=rtcTopTen',
	    type:'get',
	    dataType:"json",
	    success: function(result) {
	      var rtcTopTenData = [];
	      var rtcTopTenCategories = [];

	      $.each(result.data, function(i,n){
	        rtcTopTenCategories.push(n.ip);
	        rtcTopTenData.push(Number(n.second));
	      });

	      // 运行报表
	      rtcTopTenOptions.xAxis.categories = rtcTopTenCategories;
	      rtcTopTenOptions.series[0].data = rtcTopTenData;
	      rtcTopTen = new Highcharts.Chart(rtcTopTenOptions);
	    }
	});

	// 访问IPTOP10报表配置
	var ripTopTenOptions = {
	    chart: {                                                           
	      type: 'bar',
	      renderTo:'ripTopTen',
	    },
	    title: {                                                           
	        text: null                
	    },                                                              
	    xAxis: {
	        title: {                                                       
	            text: "IP"                                                 
	        }
	    },                                                                 
	    yAxis: {
	        title: {
	          text: "次数"
	        }                                
	    },                                                                 
	    tooltip: {                                                         
	        formatter: function() {
	              return 'IP: ' + this.x +'<br/>'+
	              this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
	      }
	    },                                                     
	    plotOptions: {
	        bar: {
	            dataLabels: {
	                enabled: true                                          
	            }
	        }
	    },                                                                 
	    legend: false,                                                                 
	    credits: {                                                         
	        enabled: false                                                 
	    },                                                                 
	    series: [{
	        name: "次数",
	        shadow: true,
	    }]
	}

	// 访问IPTOP10报表运行
	$.ajax({
	    url:'/monitorapi/lbnginx/getData?service=ripTopTen',
	    type:'get',
	    dataType:"json",
	    success: function(result) {
	      var ripTopTenData = [];
	      var ripTopTenCategories = [];

	      $.each(result.data, function(i,n){
	        ripTopTenCategories.push(n.ip);
	        ripTopTenData.push(parseInt(n.num));
	      });

	      // 运行报表
	      ripTopTenOptions.xAxis.categories = ripTopTenCategories;
	      ripTopTenOptions.series[0].data = ripTopTenData;
	      ripTopTen = new Highcharts.Chart(ripTopTenOptions);
	    }
	});

});