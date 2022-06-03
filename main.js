jQuery(document).ready(function ($) {
    $('button[name=ssl_btn]').on('click', function (e) {
        e.preventDefault();
        var action = $(this).val();
        console.log("checkk sssl = " + action);
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '/wp-admin/admin-ajax.php',
            data: {
                action: action
            },
            success: function (data) {
                console.dir(data.result);
                $.each(JSON.parse(data.result), function (k, v) {
                    var dif = Date.parse(v)/1000 - Math.floor(Date.now()/1000);
                    console.info("parse v = " + dif);
                    if (dif < 6184000)
                        $('.ssl-result').append('<div style="color: red;">' + k + '</div><div style="color: red;">' + v + '</div>');
                    else
                        $('.ssl-result').append('<div>' + k + '</div><div>' + v + '</div>');
                });
            },
            error: function(xhr) {
                alert ("Oopsie: " + xhr.statusText);
            }
        });
        $(this).prop('disabled', true);
    });
   $('form[name=siteinfo]').on('submit', function (e) {
       e.preventDefault();
       console.log("before ajax");
       var site_name = $('select[name=site_name]').val();
       var route = $('input[type=radio]:checked').val();
       $.ajax( {
           type: 'post',
           dataType: 'json',
           url : '/wp-admin/admin-ajax.php',
           data : {
               'action' : 'version_control',
               'route' : route, // поисковой запрос
               'url' : site_name,
           },
           success : function( data ) {
               console.dir( data );
               $('.response_wrapper').append(data.foo);
           },
           error: function(xhr) {
               alert ("Oopsie: " + xhr.statusText);
           }
       } );

   }) ;
});