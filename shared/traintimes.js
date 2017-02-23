var hideUrlBar = function(){
    if (window.pageYOffset <= 0){window.scrollTo(0,1);}
};

$(window).load(function(){
    window.setTimeout(hideUrlBar,0);
});

$(function(){
    if ('standalone' in navigator && navigator.standalone) {
        $('a').click(function(){
            document.location = $(this).attr('href');
            return false;
        });
    }

    $('input[placeholder]').each(function(){
        var ph = $(this).attr("placeholder");
        var currvalue = $(this).val();
        if (ph == currvalue) {
            $(this).addClass('placeholder');
        }
        if (currvalue == "") {
            $(this).addClass('placeholder');
            $(this).val(ph);
        }
        $(this).focus(function(){
            $(this).removeClass('placeholder');
            if ($(this).val() == ph) {
                $(this).val('');
            }
        }).blur(function(){
            if ($(this).val() == '') {
                $(this).addClass('placeholder');
                $(this).val(ph);
            }
        });
    });

    hideTime('#sltArrRet', '#timeRet');
    hideTime('#sltArr', '#timeOut');
    $('.changestext').hide();
    $('.statustext').hide();
    $('.callingpoints').hide();
    $('.fares').hide();
    $('.change_link').click(function(e){
        var t = $(e.target),
            li = t.closest('li'),
            href = li.find('.calling_link_inner').attr('href').replace(';stage=0', ';ajax=2');
        li.find('.changestext').toggle('slow');
        if (li.find('.ajax').length) {
            return false;
        }
        $.getJSON(href, function(data) {
            li.find('.calling_link_inner').parents('tr').each(function(i, tr){
                tr = $(tr);
                tr.after('<tr class="ajax"><td colspan=6><div style="display:none" class="ajax">' + data['tables'][i] + '</div></td></tr>');
                if (data['platform'][i][0]) {
                    tr.find('.origin').append('<br>Platform ' + data['platform'][i][0]);
                }
                if (data['platform'][i][1]) {
                    tr.find('.destination').append('<br>Platform ' + data['platform'][i][1]);
                }
            });
        });
        return false;
    });
    $('.status').click(function(e){
        $(e.target).closest('li').find('.statustext').toggle('slow');
        return false;
    });
    $('.calling_link').click(function(e){
        var div = $(e.target).closest('li').find('.callingpoints');
        if (div.html()) {
            div.toggle('slow');
            return false;
        }
        var href = e.target.href + ';ajax=1';
        div.load(href, '', function(e){
            div.show('slow');
        });
        return false;
    });
    $('.calling_link_inner').click(function(e){
        var tr = $(e.target).closest('tr');
        if (tr.next().hasClass('ajax')) {
            tr.next().find('div.ajax').toggle('slow');
            return false;
        }
        var href = e.target.href + ';ajax=1';
        $.get(href, function(out){
            tr.after('<tr class="ajax"><td colspan=6><div style="display:none" class="ajax">' + out + '</div></td></tr>');
            tr.next().find('div.ajax').show('slow');
        });
        return false;
    });

    /*$('.calling_link_inner').each(function(i, e){
        var tr = $(e).closest('tr');
        var href = e.href + ';ajax=1';
        $.get(href, function(out){
            tr.after('<tr class="ajax"><td colspan=6><div style="display:none" class="ajax">' + out + '</div></td></tr>');
        });
    }); */

    if ($('.change_link').length) {
        $('.expand_all').html('<a href="#" onclick="$(\'.changestext\').show(\'slow\'); return false;">Show all changes</a>');
    }

    /* Fares links */
    $('.fares_link').click(function(e){
        var div = $(e.target).closest('li').find('.fares');
        if (div.html()) {
            div.toggle('fast');
            return false;
        }
        var href = e.target.href + ';ajax=1';
        var link = $(this);
        div.load(href, '', function(e){
            link.html(link.html().substring(0,link.html().length-1));
            div.show('slow');
        });
        link.html( link.html() + '&hellip;');
        return false;
    });

    /* Show/hide */
    $('#operatorMode').change(function(){
        var val = $(this).val();
        if (val == 'SHOW') {
            $('#operator_all').text('All train operators');
        } else {
            $('#operator_all').text('-');
        }
    });

    $('#liveboard tr').click(function(){
        if (self==top) {
            window.location = $(this).find('a').attr('href');
        } else {
            window.open($(this).find('a').attr('href'), '_top');
        }
    }).css('cursor', 'pointer').hover(function(){
        $(this).toggleClass("active");
        //$(this).find('td.d').show();
    }, function(){
        $(this).toggleClass("active");
        //$(this).find('td.d').hide();
    }); 
    //$('#liveboard td.d').append('<img src="/images/fatcow/32x32/arrow_right.png" alt="Details">').hide().find('a').hide();

    if (navigator.geolocation) {
        $('#txtFrom').after(' <span>(<a href="javascript:traintimes.geolocate.lookup(traintimes.front_geo_success(\'txtFrom\'))">nearest</a>)</span>');
        $('#txtTo').after(' <span>(<a href="javascript:traintimes.geolocate.lookup(traintimes.front_geo_success(\'txtTo\'))">nearest</a>)</span>');
    }

});

function hideTime(select, row) {
    $(select).change(function(){
        var val = $(select).val();
        if (val == 'FIRST' || val == 'LAST') {
            $(row).hide('fast');
        } else {
            $(row).show('fast');
        }
    });
    $(select).change();
}

var traintimes = {
    'geolocate': (function(){
        var watchID, datestart, g = {};
        g.lookup = function(callback_success, callback_error) {
            datestart = Date.now();
            if (navigator.geolocation) {
                watchID = navigator.geolocation.watchPosition(
                    function(position) {
                        var geots = position.timestamp;
                        var safari_fix = Date.parse(new Date(2001,0,1,0,0,0,0));
                        if (geots < safari_fix) {
                            // Safari returns timestamp since 2001, not 1970.
                            geots = geots + safari_fix;
                        };
                        if (geots > datestart && position.coords.accuracy <= 1000) {
                            navigator.geolocation.clearWatch(watchID);
                            callback_success(position);
                        }
                    },
                    function(msg) {
                        navigator.geolocation.clearWatch(watchID);
                        if (callback_error) {
                            callback_error(msg);
                        }
                    },
                    { enableHighAccuracy: true, maximumAge:0, timeout:10000 }
                );
            }
        };
        return g;
    })(),
    'front_geo_success': function(id) {
        return function(position) {
            $.getJSON('http://traintimes.org.uk/nearest.php?ajax=1;n=5;lat=' + position.coords.latitude + ';lon=' + position.coords.longitude , function(data) {
                var out = '<select name="' + $('#'+id).attr('name') + '" id="' + id + '">';
                for (var i = 0; i < data.length; i++) {
                    var station = data[i]['station'];
                    out += '<option value="' + data[i]['code'] + '">' + station + '</option>';
                }
                out += '</select>';
                $('#'+id).replaceWith(out);
                $('#'+id).next().remove();
            });
        };
    }
};
