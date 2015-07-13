$(function(){
    $('#search_email').on('keydown', function(e){
        if(e.keyCode == 13) {
            location.href = '/online/userlist?kw='+ encodeURIComponent($(this).val());
        }
    })
})