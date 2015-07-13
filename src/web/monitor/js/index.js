$(function () {
    var init,
        accessTrendOption,
        accessDynamicOption,
        errorTopTenOption,
        numberOfNodeOption,
        errorTrendOption,
        accessTrendChart,
        accessDynamicChart,
        errorTopTenChart,
        numberOfNodeChart,
        errorTrendChart,
        accessTrendAnalytics,
        accessDynamicAnalytics,
        errorTopTenAnalytics,
        numberOfNodeAnalytics,
        errorTrendAnalytics,
        indexAnalytics,
        alarmMonitor,
        accessTrendAnalyticsUpdate,
        accessDynamicAnalyticsUpdate,
        errorTopTenAnalyticsUpdate,
        numberOfNodeAnalyticsUpdate,
        errorTrendAnalyticsUpdate,
        accessTrendUpdateTime    = 1000 * 60,
        errorTrendUpdateTime     = 1000 * 300,
        errorTopTenUpdateTime    = 1000 * 60,
        numberOfNodeUpdateTime   = 1000 * 60,
        accessDynamicUpdateTime  = 1000 * 30,
        accessNumeUpdateTime     = 1000 * 300,
        dynamicData              = [],
        dynamicNextTime          = 0,
        dynamicReuqestNum        = 1,
        accessTrendUrl           = '/api/analytics/accessTrend',
        errorTrendUrl            = '/api/analytics/errorTrend',
        errorTopTenUrl           = '/api/analytics/errorTopTen',
        numberOfNodeUrl          = '/api/monitor/datanode',
        accessDynamicUrl         = '/api/monitor/dynamic',
        indexAnalyticsUrl        = '/api/analytics/accessNumber?index=pv,error',
        nowMilliSecond,
        nowSecond

    /**
     * 初始化
     * @type {Function}
     */
    init = (function() {
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
            },
            yAxis: {
                gridLineDashStyle: 'Dash'
            },
            chart: {
                height: 220
            },
            plotOptions: {
                series: {
                    turboThreshold: 999999999
                }
            }

        });

        var getCookie = function($name) {

            var returns = new Array();
            var _cookie = document.cookie;
            if (_cookie.length < 1) return '';
            var items   = _cookie.split(";");
            for(var i in items) {
                var item  = items[i].split("=");
                var key   = item[0].trim();
                var value = item[1].trim();
                returns[key] = value;
            }

            return returns[$name];
        }

        var host = decodeURIComponent(getCookie('url_ids'));
        // 当前时间毫秒
        nowMilliSecond = (new Date()).getTime();
        // 当前时间秒
        nowSecond = parseInt(nowMilliSecond / 1000);

        // 添加监控历史参数
        var period = $('#history_period').val();
        var date = $('#history_date').val();

        var param = new Array();

        if(period == 'day') {
            param.push("from=" + date);
            indexAnalyticsUrl += '&from=' + date;
        } else if(period == 'between') {
            date = date.split(',');

            var from = date[0];
            var to   = date[1];

            param.push("from=" + from + "&to=" + to);
            indexAnalyticsUrl += "&from=" + from + "&to=" + to;
        }

        if(host.length > 0 && host != "undefined") {
            param.push("host=" + host);
            indexAnalyticsUrl += '&host=' + host;
        }

        param = param.join('&');

        accessTrendUrl += '?' + param;
        errorTrendUrl += '?' + param;
        numberOfNodeUrl += '?' + param;
        accessDynamicUrl += '?' + param;
    });

    // 访问趋势报表配置
    accessTrendOption = {
        chart: {
            zoomType: "x",
            renderTo:"accessTrend"
        },
        title: {
            text: null
        },
        subtitle: {
            text: ""
        },
        xAxis: {
            type: "datetime",
            labels: {
                formatter: function() {
                    return Highcharts.dateFormat("%H:%M", this.value);
                }
            }
        },
        yAxis: {
            title: null
        },
        plotOptions: {
            series: {
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1.2
                    }
                },
                marker: {
                    enabled: false
                },
                shadow: false,
                threshold: null

            }
        },
        tooltip: {
            crosshairs: true,
            formatter : function() {
                return Highcharts.dateFormat("%H:%M", this.x) +
                    "<br/>"+ "<span style=\"fill:"+ this.series.color +"\" x=\"8\" dy=\"16\">●</span> " +
                    this.series.name + ": <b>" + Highcharts.numberFormat(this.y, 0) + "<\/b> (req)";
            }
        }

    };

    // 访问动态报表配置
    accessDynamicOption = {
        chart: {
            renderTo: 'accessDynamic',
            events: {
                load: function() {
                    // set up the updating of the chart each second
                    var series = this.series[0];
                    setInterval(function() {
                        var obj = dynamicData.shift();
                        if (typeof(obj) == "object") {
                            series.addPoint([obj.x, obj.y], true, true);
                        }
                    }, 1000);
                }
            }
        }
        ,title: {
            text: null
        },
        xAxis: {
            type: 'datetime',
            title: null
        },
        yAxis: {
            title: null,
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }],
            maxPadding: 1.5
        },
        tooltip: {
            crosshairs: true,
            formatter : function() {
                return "<span style=\"fill:"+ this.series.color +"\" x=\"8\" dy=\"16\">●</span> " +
                    Highcharts.dateFormat("%H:%M:%S", this.x) + ": "+ "<b>" + Highcharts.numberFormat(this.y, 0) + "<\/b> (req)";
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
    };

    // 错误趋势报表配置
    errorTrendOption = {
        chart: {
            zoomType: 'x',
            renderTo:'errorTrend',
            //type:'spline'
        },
        title: {
            text: null
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            type: 'datetime',
            labels: {
                formatter: function() {
                    return Highcharts.dateFormat("%H:%M", this.value);
                }
            }
        },
        yAxis: {
            title: null
        },
        plotOptions: {
            line: {
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1.2
                    }
                },
                marker: {
                    enabled: false
                },
                shadow: false,
                threshold: null
            }
        },
        tooltip: {
            "crosshairs": true,
            formatter: function() {
                var str = '<b>' + Highcharts.dateFormat('%H点%M分', this.x) + '</b><br/>' +
                    '状态: ' + this.point.code + ' 请求: ' +  Highcharts.numberFormat(this.y, 0) + '次<br/>';

                if (this.point.top.length > 0) {
                    for ( var i = 0; i < this.point.top.length; i++ ) {
                        str += 'Top' + (i + 1) + ' ' + this.point.top[i][0] + ' ' + this.point.top[i][1] + ' <b>' + this.point.top[i][2] + '</b>' + '次<br/>';
                    }
                }
                return str;
            }
        }
    };

    // 错误次数TOP10报表配置
    errorTopTenOption = {
        chart: {
            type: 'column',
            renderTo:'errorTopTen'
        },
        title: {
            text: null
        },
        xAxis: {
            title: null
        },
        yAxis: {
            title: null
        },
        tooltip: {
            formatter: function() {
                return this.point.host + this.point.path +'<br/>'+
                    '<span style="fill:'+ this.series.color +'" x="8" dy="16">●</span> ' +
                    this.point.status +': <b>'+Highcharts.numberFormat(this.y, 0) + '</b> (num)';
            }
        },
        plotOptions: {
            column: {
                pointWidth: 30
            }
        },
        legend: false,
        series: [{
            name: "次数",
            color: '#910000'
        }]
    };

    // 节点次数报表配置
    numberOfNodeOption = {
        chart: {
            type: 'column',
            renderTo:'numberOfNode'
        },
        title: {
            text: null
        },
        xAxis: {
            title: null
        },
        yAxis: {
            title: null
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
        tooltip: {
            formatter: function() {
                return '<span style="fill:'+ this.series.color +'" x="8" dy="16">●</span> ' +
                    this.x + ': <b>' + Highcharts.numberFormat(this.y, 0) + '</b> (req)';
            }
        }

    };

    /**
     * 访问趋势报表运行
     * @type {Function}
     */
    accessTrendAnalytics = (function() {
        $.ajax({
            url: accessTrendUrl,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                var series = [],
                    data = result.data.series;
                if(data.length > 0) {
                    $.each(data, function(i,n){
                        series.push({
                            "name" : n.name,
                            "data" : n.data
                        })
                    });
                }

                accessTrendOption.series = series;
                accessTrendChart = new Highcharts.Chart(accessTrendOption);

            }
        });
    });

    /**
     * 访问动态报表运行
     * 获取访问动态前60条，延迟30秒
     * @type {Function}
     */
    accessDynamicAnalytics = (function() {
        t = accessDynamicUrl+'&current_time=' + (nowSecond - 90) + '&q='+ dynamicReuqestNum +'&limit=90';
        $.ajax({
            url: t,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                var count = 0;
                var accessDynamicData = [];
                for (var i in result.data.series) {
                    var n = result.data.series[i];
                    if(count > 59) {
                        dynamicData.push({
                            x: parseInt(n.time + '000'),
                            y: parseInt(n.num)
                        });
                    }
                    if(count < 60) {
                        accessDynamicData.push({
                            x: parseInt(n.time + '000'),
                            y: parseInt(n.num)
                        });
                    }

                    count++;
                };
                dynamicNextTime = result.data.next_time;
                accessDynamicOption.series[0].data = accessDynamicData;
                accessDynamicChart = new Highcharts.Chart(accessDynamicOption);
            }
        });
    });

    /**
     * 错误趋势报表运行
     * @type {Function}
     */
    errorTrendAnalytics = (function() {
        $.ajax({
            url: errorTrendUrl,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                var errorTrendChartSeries = [];
                var i = 0;
                var data = result.data.series;

                for(var n in data) {
                    var node = data[n];
                    var errorData = [];

                    for(var f in node) {
                        errorData.push({
                            x: parseInt(node[f].time) * 1000,
                            y: parseInt(node[f].num),
                            top: node[f].top,
                            code: n
                        });
                    }

                    var visible = true;
                    if(n == '200') {
                        visible = false;
                    }
                    errorTrendChartSeries[i] = {
                        'name': n,
                        'data': errorData,
                        'visible': visible
                    }

                    i++;
                }
                // 运行报表
                errorTrendOption.series = errorTrendChartSeries;
                errorTrendChart = new Highcharts.Chart(errorTrendOption);
            }
        });
    });

    /**
     * 错误次数TOP10报表运行
     * @type {Function}
     */
    errorTopTenAnalytics = (function() {
        $.ajax({
            url: errorTopTenUrl,
            type: 'get',
            dataType: "json",
            success: function(result) {
                var errorTopTenChartData = [],
                    errorTopTenChartCategories = [],
                    data = result.data.series;
                $.each(data, function(i,n){
                    errorTopTenChartCategories.push(n.status);
                    errorTopTenChartData.push({
                        y: parseInt(n.num),
                        x: i,
                        host: n.host,
                        path: n.path,
                        status: n.status
                    });
                });
                // 运行报表
                errorTopTenOption.xAxis.categories = errorTopTenChartCategories;
                errorTopTenOption.series[0].data = errorTopTenChartData;
                errorTopTenChart = new Highcharts.Chart(errorTopTenOption);
            }
        });
    });

    /**
     * 节点次数报表运行
     * @type {Function}
     */
    numberOfNodeAnalytics = (function() {
        $.ajax({
            url: numberOfNodeUrl,
            type: 'get',
            dataType: "json",
            success: function(result) {
                var numberOfNodeChartData = [],
                    numberOfNodeChartCategories = [],
                    series = [],
                    data = result.data.series;
                $.each(data, function(i,n){
                    numberOfNodeChartCategories.push(i);
                    numberOfNodeChartData.push({
                        y: parseInt(n)
                    });
                });
                series.push({"data" : numberOfNodeChartData});
                // 运行报表
                numberOfNodeOption.xAxis.categories = numberOfNodeChartCategories;
                numberOfNodeOption.series = series;
                numberOfNodeChart = new Highcharts.Chart(numberOfNodeOption);
            }
        });
    });

    /**
     * 大盘统计，PV、UV、EV
     * @type {Function}
     */
    indexAnalytics = (function() {
        $.ajax({
            url: indexAnalyticsUrl,
            type:'get',
            dataType: "json",
            success: function(result) {
                var data = result.data.series;

                $('.number.pv').html(numberFormat(data.pv, ''));
                $('.number.uv').html(numberFormat(data.uv, ''));
                $('.number.ip').html(numberFormat(data.ip, ''));
                $('.number.error').html(numberFormat(data.error, ''));
            }
        });
        setTimeout(arguments.callee, accessNumeUpdateTime);
    });

    /**
     * 报警监控
     * @type {Function}
     */
    alarmMonitor = (function() {
        $.ajax({
            url: 'api/monitor/alarm?type=warning,critical,total',
            type:'get',
            dataType: "json",
            success: function(result) {
                var data = result.data.series;

                $('.number.warning').html("<span>" + data.warning + '</span>/' + data.total);
                $('.number.critical').html("<span>" + data.critical + '</span>/' + data.total);
            }
        });
        setTimeout(arguments.callee, 30000);
    });

    /**
     * 访问趋势更新
     * @type {Function}
     */
    accessTrendAnalyticsUpdate = (function() {
        $.ajax({
            url: accessTrendUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var data = result.data.series;
                if(data.length > 0) {
                    $.each(data, function(i,n){
                        accessTrendChart.series[i].update({data : n.data}, true, true);
                    });
                }
            }
        });
    });

    /**
     * 访问动态更新
     * @type {Function}
     */
    accessDynamicAnalyticsUpdate = (function() {
        // 获取后30条，延迟30秒
        dynamicReuqestNum++;
        dd = accessDynamicUrl+'&current_time=' + dynamicNextTime + '&q='+ dynamicReuqestNum +'&limit=30';
        $.ajax({
            url: dd,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                var data = result.data.series;
                $.each(data, function(i,n){
                    dynamicData.push({
                        x: parseInt(n.time) * 1000,
                        y: parseInt(n.num)
                    });
                });
                dynamicNextTime = result.data.next_time;
            }
        });
    });

    /**
     * 错误趋势更新
     * @type {Function}
     */
    errorTrendAnalyticsUpdate = (function() {
        $.ajax({
            url: errorTrendUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var i = 0,
                    data = result.data.series;
                for(var n in data) {
                    var node = data[n];
                    var errorData = [];
                    for(var f in node) {
                        errorData.push({
                            x: parseInt(node[f].time) * 1000,
                            y: parseInt(node[f].num),
                            top: node[f].top,
                            code: n
                        });
                    }
                    errorTrendChart.series[i].update(
                        {
                            data: errorData
                        },
                        true,
                        true
                    );
                    i++;
                }
            }
        });
    });

    /**
     * 错误TOP10更新
     * @type {Function}
     */
    errorTopTenAnalyticsUpdate = (function() {
        $.ajax({
            url: errorTopTenUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var errorTopTenChartData = [],
                    errorTopTenChartCategories = [],
                    data = result.data.series;
                $.each(data, function(i,n){
                    errorTopTenChartCategories.push(n.status);
                    errorTopTenChartData.push({
                        y: parseInt(n.num),
                        x: i,
                        host: n.host,
                        path: n.path,
                        status: n.status
                    });
                });
                // 运行报表
                errorTopTenChart.xAxis[0].categories = errorTopTenChartCategories;
                errorTopTenChart.series[0].update(
                    {
                        data: errorTopTenChartData
                    },
                    true,
                    true
                );
            }
        });

    });

    /**
     * 监控节点更新
     * @type {Function}
     */
    numberOfNodeAnalyticsUpdate = (function() {
        $.ajax({
            url: numberOfNodeUrl,
            type:'get',
            dataType:"json",
            success: function(result) {
                var numberOfNodeChartData = [],
                    numberOfNodeChartCategories = [],
                    data = result.data.series;
                $.each(data, function(i,n){
                    numberOfNodeChartCategories.push(i);
                    numberOfNodeChartData.push({
                        y: parseInt(n)
                    });
                });
                // 运行报表
                numberOfNodeChart.xAxis[0].categories = numberOfNodeChartCategories;
                numberOfNodeChart.series[0].update(
                    {
                        data: numberOfNodeChartData
                    },
                    true,
                    true
                );
            }
        });

    });

    init();

    indexAnalytics();

    //alarmMonitor();

    accessTrendAnalytics();
    setInterval(accessTrendAnalyticsUpdate, accessTrendUpdateTime);

    accessDynamicAnalytics();
    setInterval(accessDynamicAnalyticsUpdate, accessDynamicUpdateTime);

    errorTrendAnalytics();
    setInterval(errorTrendAnalyticsUpdate, errorTrendUpdateTime);

//    errorTopTenAnalytics();
//    setInterval(errorTopTenAnalyticsUpdate, errorTopTenUpdateTime);

    numberOfNodeAnalytics();
    setInterval(numberOfNodeAnalyticsUpdate, numberOfNodeUpdateTime);
});
