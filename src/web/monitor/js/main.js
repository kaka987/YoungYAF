/**
 * 对Date的扩展，将 Date 转化为指定格式的String
 * 月(M)、日(d)、12小时(h)、24小时(H)、分(m)、秒(s)、周(E)、季度(q) 可以用 1-2 个占位符
 * 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
 * eg:
 * (new Date()).pattern("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
 * (new Date()).pattern("yyyy-MM-dd E HH:mm:ss") ==> 2009-03-10 二 20:09:04
 * (new Date()).pattern("yyyy-MM-dd EE hh:mm:ss") ==> 2009-03-10 周二 08:09:04
 * (new Date()).pattern("yyyy-MM-dd EEE hh:mm:ss") ==> 2009-03-10 星期二 08:09:04
 * (new Date()).pattern("yyyy-M-d h:m:s.S") ==> 2006-7-2 8:9:4.18
 */
Date.prototype.pattern=function(fmt) {
    var o = {
        "M+" : this.getMonth()+1, //月份
        "d+" : this.getDate(), //日
        "h+" : this.getHours()%12 == 0 ? 12 : this.getHours()%12, //小时
        "H+" : this.getHours(), //小时
        "m+" : this.getMinutes(), //分
        "s+" : this.getSeconds(), //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S" : this.getMilliseconds() //毫秒
    };
    var week = {
        "0" : "/u65e5",
        "1" : "/u4e00",
        "2" : "/u4e8c",
        "3" : "/u4e09",
        "4" : "/u56db",
        "5" : "/u4e94",
        "6" : "/u516d"
    };
    if(/(y+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    }
    if(/(E+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, ((RegExp.$1.length>1) ? (RegExp.$1.length>2 ? "/u661f/u671f" : "/u5468") : "")+week[this.getDay()+""]);
    }
    for(var k in o){
        if(new RegExp("("+ k +")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    return fmt;
}
var math = Math,
    mathAbs = math.abs;
function numberFormat(number, decimals, decPoint, thousandsSep) {
        n = +number || 0,
        c = decimals === -1 ?
            (n.toString().split('.')[1] || '').length : // preserve decimals
            (isNaN(decimals = mathAbs(decimals)) ? 2 : decimals),
        d = decPoint === undefined ? '.' : decPoint,
        t = thousandsSep === undefined ? ',' : thousandsSep,
        s = n < 0 ? "-" : "",
        i = String(parseInt(n = mathAbs(n).toFixed(c))),
        j = i.length > 3 ? i.length % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
        (c ? d + mathAbs(n - i).toFixed(c).slice(2) : "");
}

var getCookie = function($name) {
    var returns = new Array();

    var items   = document.cookie.split(";");

    if (items.length > 0) {
        for(var i in items) {
            var item  = items[i].split("=");
            if (item[0]) var key   = item[0].trim();
            if (item[1]) var value = item[1].trim();

            returns[key] = value;
        }
        return returns[$name];
    }
}


$(function () {
    var scrollSwitch = $('#scrollSwitch').val();
    if(scrollSwitch == 'ON') {
        (function () {
            $.scrollUp({
                animation: 'fade',
                activeOverlay: '#00FFFF',
                scrollImg: { active: true, type: 'background', src: '/static/img/top.png' }
            });
        })();
    }


    // 动态时间显示
    var localDiffTime = $('#nowTime').val() * 1000 - parseInt(new Date().getTime());
    (function() {
        var nowDate = new Date(new Date().getTime() + localDiffTime);
        var nowDateStr = nowDate.pattern('yyyy-MM-dd HH:mm:ss');
        $('#showDate').html(nowDateStr);
        setTimeout(arguments.callee, 1000);
    })();

    var from_date;
    var to_date;

    if(typeof WdatePicker != 'undefined') {
        WdatePicker({
            eCont:     'first_date',
            dateFmt:   'yyyy-MM-dd',
            skin:      'twoer',
            minDate:   '%y-%M-01',
            maxDate:   '%y-%M-%ld',
            onpicked:  function(dp){
                location.href = location.pathname +'?period=day&date='+ dp.cal.getDateStr();
            }
        })

        WdatePicker({
            eCont:    'from_date',
            dateFmt:  'yyyy-MM-dd',
            skin:     'twoer',
            minDate:'%y-%M-01',
            maxDate:'%y-%M-%ld',
            onpicked: function(dp){
                from_date = dp.cal.getDateStr();
            }
        })

        WdatePicker({
            eCont:    'to_date',
            dateFmt:  'yyyy-MM-dd',
            skin:     'twoer',
            minDate:'%y-%M-01',
            maxDate:'%y-%M-%ld',
            onpicked: function(dp){
                to_date = dp.cal.getDateStr();
            }
        })
    }

    var hideHistory = function() {
        var history_status = $('#history_status').val();
        if(history_status == 'show') {
            $('#monitor_history').hide();
            $('#history_status').val('hide');
            $('#modal').remove();
        }
    }

    var showHistory = function() {
        $('#monitor_history').show();
        $('#history_status').val('show');
        $('body').append('<div id="modal"></div>');
    }

    $('#applyBetween').on('click', function(){
        var fdate = typeof(from_date) == 'undefined' ? new Date().pattern('yyyy-MM-dd') : from_date;
        var tdate = typeof(to_date) == 'undefined' ? new Date().pattern('yyyy-MM-dd') : to_date;
        var ftime = new Date(fdate).getTime();
        var ttime = new Date(tdate).getTime();
        if(ftime >= ttime) {
            var message = noty({
                text        : '开始时间不能大于等于结束时间！',
                type        : 'warning',
                dismissQueue: true,
                layout      : 'center'
            });
            setTimeout(function() { message.close() }, 2000);
            return false;
        }
        location.href = location.pathname +'?period=between&date='+ fdate +','+ tdate;
    })

    $('.monitor-time').on('click', function() {
        showHistory();
    })

    $('body').delegate('#modal', 'click', function(){
        hideHistory();
    })

    $('input[name="type"]').on('click', function() {
        var type = $(this).val();

        $('.first_section').hide();
        $('.from_section').hide();
        $('.to_section').hide();
        $('#applyBetween').hide();

        if(type == 'day') {
            $('.first_section').show();
        } else if(type == 'between') {
            $('.from_section').show();
            $('.to_section').show();
            $('#applyBetween').show();
        }
    })

    var region = function() {
        var host = location.host;

        $.ajax({
            url: '/api/system/region?domain='+ host,
            type: 'get',
            dataType: 'json',
            success: function(result) {
                var data = result.data;
                if (data.self) {
                    $('.show-region .text').html(data.self.name + '地区');
                }
                var listLen = data.list.length;
                var listStr = '';
                for (var i = 0; i < listLen; i++) {
                    var one = data.list[i];
                    listStr += '<li><a href="http://'+ one.domain +'">'+ one.name +'</a></li>';
                }
                $('.region-list').html(listStr);
            }
        });
    }
    region();



})

