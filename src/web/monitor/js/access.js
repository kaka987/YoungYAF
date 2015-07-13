$(function () {
	// 访问报表参数初始化
	var ripTopTen;
	var rtcTopTen;
	var rgTopTen;
    var requestsMap;

    // 更数据请求时间
    var rgTopTenRequestTime        = 1000 * 60;
    var rtcTopTenRequestTime       = 1000 * 60;
    var ripTopTenRequestTime       = 1000 * 60;
    var requestsMapRequestTime     = 1000 * 60;

    var rgTopTenUrl        = '/reportapi/accessinfo/rgTopTen';
    var rtcTopTenUrl       = '/reportapi/accessinfo/rtcTopTen';
    var ripTopTenUrl       = '/reportapi/accessinfo/ripTopTen';
    var requestsMapUrl     = '/reportapi/accessinfo/requestsMap';

    // 添加监控历史参数
    var period = $('#history_period').val();
    var date = $('#history_date').val();
    if(period == 'day') {
        rgTopTenUrl += '?from='+ date;
        rtcTopTenUrl += '?from='+ date;
        ripTopTenUrl += '?from='+ date;
        requestsMapUrl += '?from='+ date;
    } else if(period == 'between') {
        date = date.split(',');
        var from_date = date[0];
        var to_date = date[1];
        rgTopTenUrl += '?from='+ from_date +'&to'+ to_date;
        rtcTopTenUrl += '?from='+ from_date +'&to'+ to_date;
        ripTopTenUrl += '?from='+ from_date +'&to'+ to_date;
        requestsMapUrl += '?from='+ from_date +'&to'+ to_date;
    }

    var globalRequestsMapOptions;

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

    // 全球访问Top10报表配置
    var rgTopTenOptions = {
        chart: {
            type: 'column',
            renderTo:'rgTopTen'
        },
        title: {
            text: null
        },
        xAxis: {
            title: {
                text: "地区"
            }
        },
        yAxis: {
            title: {
                text: "次数"
            }
        },
        tooltip: {
            formatter: function() {
                return '地区: ' + this.x +'<br/>'+
                    this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
            }
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true
                },
                pointWidth: 30
            }
        },
        legend: false,
        credits: {
            enabled: false
        },
        series: [{
            name: "次数",
            dataLabels: {
                enabled: true
            }
        }]
    }

	// 响应耗时Top10报表配置
	var rtcTopTenOptions = {
		chart: {                                                           
			type: 'column',
			renderTo:'rtcTopTen'
		},
		title: {                                                           
			text: null                
		},                                                              
		xAxis: {
			title: {                                                       
			  text: "地区"                                                 
			}
		},
		yAxis: {
			title: {
				text: "耗时秒"
			}
		},                                                                 
		tooltip: {                                                         
			formatter: function() {
			    return 'Ip: ' + String(this.point.ip) +'<br/>'+ '<br/>' + 'Host：' + this.point.host + '<br/>' + 'Path：' + this.point.path + '<br/>' +
			    '地区：' + this.x +'<br/>'+
			    this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '秒';
			}
		},                                                                 
		plotOptions: {
            column: {
				dataLabels: {                                              
				  enabled: true                                          
				},
                pointWidth: 30
			}                                                              
		},
		legend: false,                                                                 
		credits: {
			enabled: false                                                 
		},
		series: [{
			name: "耗时"
		}]
	}

    // 访问IPTop10报表配置
    var ripTopTenOptions = {
        chart: {
            type: 'column',
            renderTo:'ripTopTen'
        },
        title: {
            text: null
        },
        xAxis: {
            title: {
                text: "地区"
            }
        },
        yAxis: {
            title: {
                text: "次数"
            }
        },
        tooltip: {
            formatter: function() {
                return 'Ip: ' + this.point.ip +'<br/>地区：' + this.x + '<br/>' + 'Host：' + this.point.host + '<br/>' + 'Path：' + this.point.path + '<br/>' +
                    this.series.name + ': ' +  Highcharts.numberFormat(this.y, 0) + '次';
            }
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true
                },
                pointWidth: 30
            }
        },
        legend: false,
        credits: {
            enabled: false
        },
        series: [{
            name: "次数"
        }]
    }

    // 请求次数统计地图配置
    var requestsMapOptions = {
        chart : {
            borderWidth : 1,
            renderTo:'requestsMap'
        },

        colors: ['rgba(19,64,117,0.05)', 'rgba(19,64,117,0.2)', 'rgba(19,64,117,0.4)',
            'rgba(19,64,117,0.5)', 'rgba(19,64,117,0.6)', 'rgba(19,64,117,0.8)', 'rgba(19,64,117,1)'],

        title : {
            text : ''
        },

        mapNavigation: {
            enabled: true
        },

        legend: {
            title: {
                text: '请求次数',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                }
            },
            align: 'left',
            verticalAlign: 'bottom',
            floating: true,
            layout: 'vertical',
            valueDecimals: 0,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || 'rgba(255, 255, 255, 0.85)',
            symbolRadius: 0,
            symbolHeight: 14
        },
        colorAxis: {
            dataClasses: [{
                to: 10
            }, {
                from: 10,
                to: 100
            }, {
                from: 100,
                to: 1000
            }, {
                from: 1000,
                to: 10000
            }, {
                from: 10000,
                to: 100000
            }, {
                from: 100000,
                to: 1000000
            }, {
                from: 1000000
            }]
        },
        tooltip: {
            formatter: function() {
                return '<span style="fill:#7cb5ec">●</span> ' + this.key + '<br/>' +
                    '请求次数:' + this.point.value + '次';
            }
        },
        series : [{
            mapData: Highcharts.maps.world,
            joinBy: 'code',
            animation: true,
            name: '请求次数',
            states: {
                hover: {
                    color: '#BADA55'
                }
            }
        }]
    }

    // 响应时间地图配置
    var responseTimeMapOptions = {
        chart : {
            borderWidth : 1,
            renderTo:'requestsMap'
        },

        colors: ['rgba(19,64,117,0.05)', 'rgba(19,64,117,0.2)', 'rgba(19,64,117,0.4)',
            'rgba(19,64,117,0.5)', 'rgba(19,64,117,0.6)', 'rgba(19,64,117,0.8)', 'rgba(19,64,117,1)'],

        title : {
            text : ''
        },

        mapNavigation: {
            enabled: true
        },

        legend: {
            title: {
                text: '最大请求耗时',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                }
            },
            align: 'left',
            verticalAlign: 'bottom',
            floating: true,
            layout: 'vertical',
            valueDecimals: 0,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || 'rgba(255, 255, 255, 0.85)',
            symbolRadius: 0,
            symbolHeight: 14
        },
        colorAxis: {
            dataClasses: [{
                to: 0.1
            }, {
                from: 0.1,
                to: 1
            }, {
                from: 1,
                to: 5
            }, {
                from: 5,
                to: 10
            }, {
                from: 10,
                to: 50
            }, {
                from: 50,
                to: 100
            }, {
                from: 100
            }]
        },
        tooltip : {
            formatter: function() {
                return '<span style="fill:#7cb5ec">●</span> ' + this.key + "<br/>" +
                    "最大耗时:" + this.point.max_request_time + "，" +
                    "平均耗时:" + this.point.avg_request_time;
            }
        },
        series : [{
            mapData: Highcharts.maps.world,
            joinBy: 'code',
            animation: true,
            name: '请求耗时',
            states: {
                hover: {
                    color: '#BADA55'
                }
            }
        }]
    }


    // 全球访问Top10报表运行
    $.ajax({
        url: rgTopTenUrl,
        type:'get',
        dataType:"json",
        success: function(result) {
            var rgTopTenData = [];
            var rgTopTenCategories = [];
           //console.log(JSON.stringify(result.data.series));
            $.each(result.data.series, function(i,n){
                rgTopTenCategories.push(n.name);
                rgTopTenData.push(parseInt(n.num));
            });

            // 运行报表
            rgTopTenOptions.xAxis.categories = rgTopTenCategories;
            rgTopTenOptions.series[0].data = rgTopTenData;
            rgTopTen = new Highcharts.Chart(rgTopTenOptions);
        }
    });

	// 响应耗时Top10报表运行
	$.ajax({
	    url: rtcTopTenUrl,
	    type: 'get',
	    dataType:"json",
	    success: function(result) {
	      var rtcTopTenData = [];
	      var rtcTopTenCategories = [];

	      $.each(result.data.series, function(i,n){
	        // rtcTopTenCategories.push(n.ip);
	        rtcTopTenCategories.push(n.name);
	        rtcTopTenData.push({
	        	y: Number(n.second),
	        	ip: n.ip,
                path: n.path,
                host: n.host
	        });
	      });

	      // 运行报表
	      rtcTopTenOptions.xAxis.categories = rtcTopTenCategories;
	      rtcTopTenOptions.series[0].data = rtcTopTenData;
	      rtcTopTen = new Highcharts.Chart(rtcTopTenOptions);
	    }
	});

	// 访问IPTop10报表运行
	$.ajax({
	    url: ripTopTenUrl,
	    type: 'get',
	    dataType: "json",
	    success: function(result) {
	      var ripTopTenData = [];
	      var ripTopTenCategories = [];
//          console.log(JSON.stringify(result.data));
	      $.each(result.data.series, function(i,n){
	        ripTopTenCategories.push(n.name);
	        ripTopTenData.push({
                y: parseInt(n.num),
                ip: n.ip,
                path: n.path,
                host: n.host
            });
	      });

	      // 运行报表
	      ripTopTenOptions.xAxis.categories = ripTopTenCategories;
	      ripTopTenOptions.series[0].data = ripTopTenData;
	      ripTopTen = new Highcharts.Chart(ripTopTenOptions);
	    }
	});

	$.ajax({
		url: requestsMapUrl,
	    type:'get',
	    dataType:"json",
	    success: function(result) {
            var requestsMapData = [];
//            console.log(JSON.stringify(result.data.series));
            $.each(result.data.series, function (i, n) {
                requestsMapData.push({
                    code: n.code.toUpperCase(),
                    value: parseFloat(n.value),
                    name: n.name
                })
            });
            // 运行地图
            requestsMapOptions.series[0].data = requestsMapData;
            requestsMap = new Highcharts.Map(requestsMapOptions);
	    }
	});

    // 全球访问Top10报表请求新数据
    setInterval(function() {
        $.ajax({
            url: rgTopTenUrl,
            type: 'get',
            dataType: "json",
            success: function(result) {
                var rgTopTenData = [];
                var rgTopTenCategories = [];
                $.each(result.data.series, function(i,n){
                    rgTopTenCategories.push(n.name);
                    rgTopTenData.push(parseInt(n.num));
                });

                // 运行报表
                rgTopTen.xAxis[0].categories = rgTopTenCategories;
                rgTopTen.series[0].update(
                    {
                        data: rgTopTenData
                    },
                    true,
                    true
                );
            }
        });
    }, rgTopTenRequestTime);

    // 响应耗时Top10报表请求新数据
    setInterval(function() {
        $.ajax({
            url: rtcTopTenUrl,
            type: 'get',
            dataType:"json",
            success: function(result) {
                var rtcTopTenData = [];
                var rtcTopTenCategories = [];

                $.each(result.data.series, function(i,n){
                    // rtcTopTenCategories.push(n.ip);
                    rtcTopTenCategories.push(n.name);
                    rtcTopTenData.push({
                        y: Number(n.second),
                        ip: n.ip,
                        path: n.path
                    });
                });

                // 运行报表
                rtcTopTen.xAxis[0].categories = rtcTopTenCategories;
                rtcTopTen.series[0].update(
                    {
                        data: rtcTopTenData
                    },
                    true,
                    true
                );
            }
        });
    }, rtcTopTenRequestTime);

    // 访问IPTop10报表请求新数据
    setInterval(function() {
        $.ajax({
            url: ripTopTenUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var ripTopTenData = [];
                var ripTopTenCategories = [];

                $.each(result.data.series, function(i,n){
                    ripTopTenCategories.push(n.name);
                    ripTopTenData.push({
                        y: Number(n.num),
                        ip: n.ip,
                        path: n.path
                    });
                });

                // 运行报表
                ripTopTen.xAxis[0].categories = ripTopTenCategories;
                ripTopTen.series[0].update(
                    {
                        data: ripTopTenData
                    },
                    true,
                    true
                );
            }
        });
    }, ripTopTenRequestTime);

    // 全球访问趋势报表请求新数据
    setInterval(function() {
        $.ajax({
            url: requestsMapUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var requestsMapData = [];
                $.each(result.data.series, function (i, n) {
                    var value = 0;
                    if ($('.map-request .number').hasClass('active')) {
                        value = parseFloat(n.value);
                    } else if($('.map-request .time').hasClass('active')) {
                        value = parseFloat(n.max_request_time);
                    }
                    requestsMapData.push({
                        code: n.code.toUpperCase(),
                        value: value,
                        avg_request_time: parseFloat(n.avg_request_time),
                        max_request_time: parseFloat(n.max_request_time),
                        name: n.name
                    })
                });

                // 运行报表
                requestsMap.series[0].update(
                    {
                        data: requestsMapData
                    },
                    true,
                    true
                );
            }
        });
    }, requestsMapRequestTime);


    $('.map-request button').on('click', function() {
        if ($(this).hasClass('time')) {
            globalRequestsMapOptions = responseTimeMapOptions;
            //不太明白此处是什么意思？
            requestsMapUrl = '/reportapi/accessinfo/responseTimeMap';
            globalRequestsMapOptions.series = [{
                mapData: Highcharts.maps.world,
                joinBy: 'code',
                animation: true,
                name: '请求耗时',
                states: {
                    hover: {
                        color: '#BADA55'
                    }
                }
            }]
            $('.map-request .number').removeClass('active');
        } else if ($(this).hasClass('number')) {
            globalRequestsMapOptions = requestsMapOptions;
            requestsMapUrl = '/reportapi/accessinfo/requestsMap';
            globalRequestsMapOptions.series = [{
                mapData: Highcharts.maps.world,
                joinBy: 'code',
                animation: true,
                name: '请求次数',
                states: {
                    hover: {
                        color: '#BADA55'
                    }
                }
            }]
            $('.map-request .time').removeClass('active');
        }
        $(this).addClass('active');

        requestsMap = new Highcharts.Chart(globalRequestsMapOptions);

        $.ajax({
            url: requestsMapUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var requestsMapData = [];
                $.each(result.data.series, function (i, n) {
                    var value = 0;
                    if ($('.map-request .number').hasClass('active')) {
                        value = parseFloat(n.value);
                    } else if($('.map-request .time').hasClass('active')) {
                        value = parseFloat(n.max_request_time);
                    }
                    requestsMapData.push({
                        code: n.code.toUpperCase(),
                        value: value,
                        avg_request_time: parseFloat(n.avg_request_time),
                        max_request_time: parseFloat(n.max_request_time),
                        name: n.name
                    })
                });
                // 运行地图
                globalRequestsMapOptions.series[0].data = requestsMapData;
                requestsMap = new Highcharts.Map(globalRequestsMapOptions);
            }
        });
    })

});